import test from 'node:test';
import assert from 'node:assert/strict';
import http from 'node:http';
import {once} from 'node:events';
import {createHmac} from 'node:crypto';
import {WebSocket} from 'ws';
import {createRelay} from '../server.mjs';

const key='gateway-test-secret-'.repeat(4);
function ticket(role){const payload=Buffer.from(JSON.stringify({aud:'taskflow-relay',sid:12,sub:role==='agent'?11:22,role,exp:Math.floor(Date.now()/1000)+45})).toString('base64url');return payload+'.'+createHmac('sha256',key).update(payload).digest('base64url');}
async function connect(url,role){const ws=new WebSocket(url);await once(ws,'open');const auth=once(ws,'message');ws.send(JSON.stringify({kind:'auth',token:ticket(role)}));await auth;return ws;}
const call=(port,path,method='GET',body='',headers={})=>new Promise((resolve,reject)=>{
    const request=http.request({host:'127.0.0.1',port,path,method,headers},response=>{
        const chunks=[];response.on('data',chunk=>chunks.push(chunk));response.on('end',()=>resolve({status:response.statusCode,headers:response.headers,body:Buffer.concat(chunks)}));
    });request.on('error',reject);request.end(body);
});
test('Render gateway preserves public paths, authorization, login cookies, UTF-8 bodies and installer downloads',async()=>{
    const zip=Buffer.from([80,75,3,4,1,2,3,4]);
    const app=http.createServer((req,res)=>{
        if(req.url==='/public/login'){res.writeHead(302,{'Location':'/public/remote-support','Set-Cookie':['session=abc; HttpOnly; Secure','operator=xyz; HttpOnly; Secure']});res.end();return;}
        if(req.url==='/public/remote-support/agent/package'){res.writeHead(200,{'Content-Type':'application/zip','Content-Disposition':'attachment; filename="agent.zip"'});res.end(zip);return;}
        let body='';req.on('data',chunk=>body+=chunk);req.on('end',()=>{res.writeHead(200,{'Content-Type':'application/json'});res.end(JSON.stringify({path:req.url,auth:req.headers.authorization,cookie:req.headers.cookie,body}));});
    });await new Promise(resolve=>app.listen(0,'127.0.0.1',resolve));
    const gateway=createRelay({key,behindProxy:true,port:0,upstream:{host:'127.0.0.1',port:app.address().port}});await gateway.listen();
    try {
        const port=gateway.server.address().port;
        const body=JSON.stringify({name:'Máy người dùng',enrollment:'example'});
        const response=await call(port,'/public/remote-support/agent/enroll','POST',body,{'Content-Type':'application/json','Authorization':'Bearer test-agent','Cookie':'session=abc'});
        assert.deepEqual(JSON.parse(response.body),{path:'/public/remote-support/agent/enroll',auth:'Bearer test-agent',cookie:'session=abc',body});
        const login=await call(port,'/public/login');assert.equal(login.status,302);assert.equal(login.headers.location,'/public/remote-support');assert.equal(login.headers['set-cookie'].length,2);
        const download=await call(port,'/public/remote-support/agent/package');assert.equal(download.headers['content-type'],'application/zip');assert.equal(download.headers['content-disposition'],'attachment; filename="agent.zip"');assert.deepEqual(download.body,zip);
        const malformed=await call(port,'//[');assert.equal(malformed.status,400);
        const health=await call(port,'/public/remote-support/relay/health');assert.equal(health.status,200);assert.equal(health.body.toString(),'ok');
        assert.throws(()=>createRelay({key,behindProxy:true,upstream:{host:'external.example',port:80}}));
    } finally {gateway.close();await new Promise(resolve=>app.close(resolve));}
});
test('Render gateway accepts relay WebSocket frames directly and rejects unrelated or anonymous upgrades',async()=>{
    const app=http.createServer((req,res)=>{res.end('PHP app');});await new Promise(resolve=>app.listen(0,'127.0.0.1',resolve));
    const gateway=createRelay({key,behindProxy:true,port:0,upstream:{host:'127.0.0.1',port:app.address().port}});await gateway.listen();
    let sockets=[];
    try {
        const base='ws://127.0.0.1:'+gateway.server.address().port;
        const invalid=new WebSocket(base+'/public/login');sockets.push(invalid);const error=once(invalid,'error');assert.match((await error)[0].message,/401/);
        const anonymous=new WebSocket(base+'/public/remote-support/relay');sockets.push(anonymous);await once(anonymous,'open');const closed=once(anonymous,'close'),rejection=once(anonymous,'message');anonymous.send(JSON.stringify({kind:'auth',token:'invalid-test-ticket'}));assert.equal(JSON.parse((await rejection)[0]).kind,'error');assert.equal((await closed)[0],4003);
        const agent=await connect(base+'/public/remote-support/relay','agent');sockets.push(agent);
        const viewer=await connect(base+'/public/remote-support/relay','viewer');sockets.push(viewer);
        await new Promise(resolve=>setTimeout(resolve,20));
        const frame=once(viewer,'message');agent.send(Buffer.from('screen-via-public-gateway'));assert.equal((await frame)[0].toString(),'screen-via-public-gateway');
        const input=once(agent,'message');viewer.send(JSON.stringify({kind:'input',action:'move',x:0.5,y:0.5}));assert.equal(JSON.parse((await input)[0]).action,'move');
    } finally {for(const ws of sockets)ws.terminate();gateway.close();await new Promise(resolve=>app.close(resolve));}
});
