import test from 'node:test';
import assert from 'node:assert/strict';
import {createHmac} from 'node:crypto';
import {once} from 'node:events';
import {WebSocket} from 'ws';
import {createRelay,verifyTicket} from '../server.mjs';
const key='test-secret-'.repeat(5);
function ticket(sid,role,extra={}){const payload=Buffer.from(JSON.stringify({aud:'taskflow-relay',sid,sub:role==='agent'?11:22,role,exp:Math.floor(Date.now()/1000)+45,...extra})).toString('base64url');return payload+'.'+createHmac('sha256',key).update(payload).digest('base64url');}
async function connect(url,token){const ws=new WebSocket(url);await once(ws,'open');const authenticated=once(ws,'message');ws.send(JSON.stringify({kind:'auth',token}));await authenticated;return ws;}
test('tickets require signature, audience, identity, role, and expiry',()=>{
    assert.equal(verifyTicket(ticket(1,'agent'),key).role,'agent');
    for(const value of [ticket(1,'agent')+'x',ticket(1,'agent',{aud:'other'}),ticket(1,'bad'),ticket(0,'agent'),ticket(1,'viewer',{exp:1}),ticket(1,'viewer',{sub:0})])assert.throws(()=>verifyTicket(value,key));
    assert.throws(()=>createRelay({key,port:0}));
});
test('relay pairs only authenticated peers in the same session and enforces direction',async()=>{
    const relay=createRelay({key,behindProxy:true,port:0});await relay.listen();
    const url='ws://127.0.0.1:'+relay.server.address().port;let sockets=[];
    try{
        const anonymous=new WebSocket(url);sockets.push(anonymous);await once(anonymous,'open');const denied=once(anonymous,'close');anonymous.send(Buffer.from('frame'));assert.equal((await denied)[0],4003);
        const agent=await connect(url,ticket(1,'agent'));sockets.push(agent);
        const viewer=await connect(url,ticket(1,'viewer'));sockets.push(viewer);
        const other=await connect(url,ticket(2,'viewer'));sockets.push(other);let leaked=false;other.on('message',()=>{leaked=true;});
        // Drain the ready event before the binary frame check.
        await new Promise(resolve=>setTimeout(resolve,20));
        const frame=once(viewer,'message');agent.send(Buffer.from([255,216,1,2,255,217]));const [bytes,binary]=await frame;assert.equal(binary,true);assert.deepEqual([...bytes],[255,216,1,2,255,217]);
        const input=once(agent,'message');viewer.send(JSON.stringify({kind:'input',action:'move',x:0.5,y:0.5}));assert.equal(JSON.parse((await input)[0]).action,'move');assert.equal(leaked,false);
        const invalid=once(viewer,'close');viewer.send(Buffer.from('fake screen'));assert.equal((await invalid)[0],4003);
        const duplicate=new WebSocket(url);sockets.push(duplicate);await once(duplicate,'open');const dupDenied=once(duplicate,'close');duplicate.send(JSON.stringify({kind:'auth',token:ticket(1,'agent')}));assert.equal((await dupDenied)[0],4003);
        const changed=once(agent,'close');agent.send(JSON.stringify({kind:'auth',token:ticket(2,'agent')}));assert.equal((await changed)[0],4003);
    } finally {for(const ws of sockets)ws.terminate();relay.close();}
});
