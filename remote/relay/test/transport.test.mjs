import test from 'node:test';
import assert from 'node:assert/strict';
import {createRequire} from 'node:module';
const require=createRequire(import.meta.url),Transport=require('../../../public/js/remote-transport.js');
class PC {
    constructor(){this.remoteDescription=null;this.candidates=[];}
    createDataChannel(){return {readyState:'open',bufferedAmount:0,sent:[],send(data){this.sent.push(data);},close(){this.readyState='closed';}};}
    async createOffer(){return{type:'offer',sdp:'offer'};}
    async createAnswer(){return{type:'answer',sdp:'answer'};}
    async setLocalDescription(data){this.localDescription={...data,toJSON:()=>data};}
    async setRemoteDescription(data){this.remoteDescription=data;}
    async addIceCandidate(candidate){this.candidates.push(candidate);}
    close(){}
}
globalThis.RTCPeerConnection=PC;
class WS {static OPEN=1;constructor(url){this.url=url;this.readyState=1;this.bufferedAmount=0;this.sent=[];}send(data){this.sent.push(data);}close(){this.readyState=3;}}
globalThis.WebSocket=WS;
test('ICE candidates queue before SDP and flush after it',async()=>{
    const signals=[],t=new Transport({role:'agent',ticket:'token',relayUrl:'wss://relay',onSignal:(...args)=>signals.push(args)});
    try{await t.acceptSignal('CANDIDATE',{candidate:'early'});assert.equal(t.pc.candidates.length,0);await t.acceptSignal('OFFER',{type:'offer',sdp:'offer'});assert.equal(t.pc.candidates.length,1);assert.equal(signals[0][0],'ANSWER');}finally{t.close();}
});
test('full frames chunk below WebRTC limits, reassemble, and honor backpressure',async()=>{
    let received;const a=new Transport({role:'agent',ticket:'a',onSignal:()=>{}}),v=new Transport({role:'viewer',ticket:'v',onSignal:()=>{},onFrame:blob=>{received=blob;}});
    try{a.attach(a.pc.createDataChannel());const data=Buffer.alloc(95000,42);a.sendFrame(data.toString('base64'));assert.ok(a.channel.sent.length>1);for(const chunk of a.channel.sent){assert.ok(chunk.length<16384);v.receive(chunk);}assert.deepEqual(Buffer.from(await received.arrayBuffer()),data);
        const sent=a.channel.sent.length;a.channel.bufferedAmount=300000;a.sendFrame(data.toString('base64'));assert.equal(a.channel.sent.length,sent);
    }finally{a.close();v.close();}
});
test('failed direct connection falls back to relay and refreshes credentials',()=>{
    const ready=[],t=new Transport({role:'agent',ticket:'old',relayUrl:'wss://relay',onSignal:()=>{},onReady:mode=>ready.push(mode)});
    try{t.pc.connectionState='failed';t.pc.onconnectionstatechange();assert.equal(t.ws.url,'wss://relay');t.updateTicket('new');t.ws.onopen();assert.equal(JSON.parse(t.ws.sent[0]).token,'new');t.ws.onmessage({data:JSON.stringify({kind:'ready'})});assert.deepEqual(ready,['relay']);t.sendFrame(Buffer.from('jpeg').toString('base64'));assert.ok(t.ws.sent.at(-1) instanceof Uint8Array);t.close();assert.equal(t.ws.readyState,3);}finally{t.close();}
});
test('a failed ICE connection falls back even while the data channel still reports open',()=>{
    const modes=[],t=new Transport({role:'agent',ticket:'valid',relayUrl:'wss://relay',onSignal:()=>{},onReady:mode=>modes.push(mode)});
    try{t.attach(t.pc.createDataChannel());t.pc.connectionState='connected';t.channel.onopen();assert.deepEqual(modes,['p2p']);t.pc.connectionState='disconnected';t.pc.onconnectionstatechange();assert.ok(t.ws);t.ws.onmessage({data:JSON.stringify({kind:'ready'})});assert.deepEqual(modes,['p2p','relay']);t.sendFrame(Buffer.from('relay').toString('base64'));assert.ok(t.ws.sent.at(-1) instanceof Uint8Array);}
    finally{t.close();}
});
test('relay authentication error closes the peer immediately without waiting for a close handshake',()=>{
    let message;const t=new Transport({role:'agent',ticket:'bad',relayUrl:'wss://relay',onSignal:()=>{},onError:error=>message=error});
    t.relay();t.ws.onmessage({data:JSON.stringify({kind:'error',message:'Access denied'})});
    assert.equal(t.closed,true);assert.equal(message,'Access denied');
});
