import http from 'node:http';
import https from 'node:https';
import fs from 'node:fs';
import {createHmac,timingSafeEqual} from 'node:crypto';
import {pathToFileURL} from 'node:url';
import {WebSocketServer,WebSocket} from 'ws';

export function verifyTicket(token,key,now=Math.floor(Date.now()/1000)) {
    if(typeof token!=='string'||token.length>1024)throw new Error('Invalid ticket');
    const parts=token.split('.');if(parts.length!==2)throw new Error('Invalid ticket');
    const signature=Buffer.from(parts[1],'base64url'),expected=createHmac('sha256',key).update(parts[0]).digest();
    if(signature.length!==expected.length||!timingSafeEqual(signature,expected))throw new Error('Invalid signature');
    const claims=JSON.parse(Buffer.from(parts[0],'base64url').toString());
    if(claims.aud!=='taskflow-relay'||!Number.isInteger(claims.sid)||claims.sid<=0||!Number.isInteger(claims.sub)||claims.sub<=0||
        !['agent','viewer'].includes(claims.role)||!Number.isInteger(claims.exp)||claims.exp<=now||claims.exp>now+60)throw new Error('Invalid claims');
    return claims;
}
export function createRelay({key,tls,behindProxy=false,host='127.0.0.1',port=8081,upstream}={}) {
    if(typeof key!=='string'||key.length<32)throw new Error('REMOTE_RELAY_SECRET must contain at least 32 characters');
    if(!tls&&!behindProxy)throw new Error('TLS certificate or an explicitly configured TLS reverse proxy is required');
    if(upstream && (upstream.host!=='127.0.0.1'||!Number.isInteger(upstream.port)||upstream.port<1||upstream.port>65535))throw new Error('HTTP upstream must be a fixed loopback listener');
    const revision=/^[a-f0-9]{40}$/i.test(process.env.RENDER_GIT_COMMIT||'')?process.env.RENDER_GIT_COMMIT:'local';
    const metrics={connections:0,messages:0,errors:0,lastError:'none'};
    const relayPaths=['/public/remote-support/relay','/remote-support/relay'];
    const handler=(req,res)=>{
        let pathname;
        try {pathname=new URL(req.url,'http://localhost').pathname;}
        catch {res.writeHead(400);res.end('Bad request');return;}
        const health=pathname==='/health'||(upstream&&relayPaths.some(prefix=>pathname===prefix+'/health'));
        if(health||!upstream){res.writeHead(health?200:404,{'Content-Type':'text/plain','X-TaskFlow-Revision':revision,'Cache-Control':'no-store',
            ...(health?{'X-TaskFlow-Connections':String(metrics.connections),'X-TaskFlow-Messages':String(metrics.messages),'X-TaskFlow-Errors':String(metrics.errors),'X-TaskFlow-Last-Error':metrics.lastError}:{})});res.end(health?'ok':'Not found');return;}
        // Stream PHP/static requests to Apache; WebSocket frames stay in Node.
        const forwarded=http.request({host:upstream.host,port:upstream.port,path:req.url,method:req.method,headers:req.headers},response=>{
            res.writeHead(response.statusCode,response.headers);response.pipe(res);
            response.on('error',()=>res.destroy());
        });
        forwarded.on('error',()=>{if(!res.headersSent){res.writeHead(502,{'Content-Type':'text/plain'});res.end('Application server unavailable');}else res.destroy();});
        req.on('aborted',()=>forwarded.destroy());
        res.on('close',()=>{if(!res.writableFinished)forwarded.destroy();});
        req.pipe(forwarded);
    };
    const server=tls?https.createServer(tls,handler):http.createServer(handler);
    const wss=new WebSocketServer({server,maxPayload:2*1024*1024,perMessageDeflate:false,
        ...(upstream?{verifyClient:info=>{try{return relayPaths.includes(new URL(info.req.url,'http://localhost').pathname.replace(/\/+$/,''));}catch{return false;}}}:{})});
    const rooms=new Map();
    wss.on('headers',headers=>headers.push('X-TaskFlow-Revision: '+revision));
    wss.on('connection',ws=>{
        metrics.connections++;
        ws.alive=true;ws.on('pong',()=>{ws.alive=true;});
        const authTimeout=setTimeout(()=>ws.close(4001,'Authentication required'),5000);authTimeout.unref();
        ws.on('message',(data,binary)=>{
            metrics.messages++;
            try {
                if(!binary&&data.length<=2048) {
                    const msg=JSON.parse(data.toString());
                    if(msg.kind==='auth') {
                        const claims=verifyTicket(msg.token,key);
                        if(ws.claims&&(claims.sid!==ws.claims.sid||claims.sub!==ws.claims.sub||claims.role!==ws.claims.role))throw new Error('Identity cannot change');
                        if(!ws.claims) {
                            let room=rooms.get(claims.sid);if(!room){room={};rooms.set(claims.sid,room);}
                            if(room[claims.role])throw new Error('Participant already connected');
                            room[claims.role]=ws;clearTimeout(authTimeout);
                        }
                        ws.claims=claims;ws.send(JSON.stringify({kind:'authenticated'}));
                        const room=rooms.get(claims.sid);
                        if(room.agent&&room.viewer)for(const peer of Object.values(room))if(peer.readyState===WebSocket.OPEN)peer.send(JSON.stringify({kind:'ready'}));
                        return;
                    }
                }
                if(!ws.claims||ws.claims.exp<=Math.floor(Date.now()/1000))throw new Error('Authentication expired');
                if(ws.claims.role==='agent'&&!binary)throw new Error('Agents may only send screen frames');
                if(ws.claims.role==='viewer') {
                    if(binary||data.length>2048)throw new Error('Invalid control message');
                    const msg=JSON.parse(data.toString());
                    if(msg.kind!=='input'||!['move','down','up','wheel','keyDown','keyUp'].includes(msg.action))throw new Error('Invalid input');
                }
                const room=rooms.get(ws.claims.sid),peer=room?.[ws.claims.role==='agent'?'viewer':'agent'];
                if(peer?.readyState===WebSocket.OPEN&&peer.claims.exp>Math.floor(Date.now()/1000)&&peer.bufferedAmount<262144)peer.send(data,{binary});
            } catch {ws.close(4003,'Authentication or protocol rejected');}
        });
        ws.on('close',()=>{
            clearTimeout(authTimeout);
            if(!ws.claims)return;
            const room=rooms.get(ws.claims.sid);if(!room)return;
            if(room[ws.claims.role]===ws)delete room[ws.claims.role];
            for(const peer of Object.values(room))if(peer.readyState===WebSocket.OPEN)peer.send(JSON.stringify({kind:'peer_left'}));
            if(!room.agent&&!room.viewer)rooms.delete(ws.claims.sid);
        });
        ws.on('error',error=>{metrics.errors++;metrics.lastError=String(error.code||'WS_ERROR').replace(/[^A-Z0-9_]/gi,'').slice(0,60);});
        ws._socket.resume();
    });
    const sweep=setInterval(()=>{
        for(const ws of wss.clients) {
            if(!ws.alive||(ws.claims&&ws.claims.exp<=Math.floor(Date.now()/1000))){ws.terminate();continue;}
            ws.alive=false;ws.ping();
        }
    },10000);sweep.unref();
    server.on('close',()=>clearInterval(sweep));
    return {server,wss,listen:()=>new Promise(resolve=>server.listen(port,host,resolve)),close:()=>{for(const ws of wss.clients)ws.terminate();wss.close();server.close();}};
}
if(process.argv[1]&&import.meta.url===pathToFileURL(process.argv[1]).href) {
    const tls=process.env.REMOTE_TLS_CERT&&process.env.REMOTE_TLS_KEY?{cert:fs.readFileSync(process.env.REMOTE_TLS_CERT),key:fs.readFileSync(process.env.REMOTE_TLS_KEY)}:undefined;
    const upstream=process.env.REMOTE_HTTP_UPSTREAM_PORT?{host:'127.0.0.1',port:Number(process.env.REMOTE_HTTP_UPSTREAM_PORT)}:undefined;
    const relay=createRelay({key:process.env.REMOTE_RELAY_SECRET,tls,behindProxy:process.env.REMOTE_BEHIND_TLS_PROXY==='true',
        host:process.env.REMOTE_RELAY_HOST||'127.0.0.1',port:Number(process.env.PORT||8081),upstream});
    await relay.listen();
    if(process.env.REMOTE_DROP_USER){process.setgid(process.env.REMOTE_DROP_USER);process.setuid(process.env.REMOTE_DROP_USER);}
    console.log('TaskFlow remote relay listening');
    for(const signal of ['SIGINT','SIGTERM'])process.on(signal,()=>relay.close());
}
