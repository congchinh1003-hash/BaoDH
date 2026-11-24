// Real Chromium DTLS data channels and a real TLS WebSocket relay. No desktop is captured.
import {chromium} from 'playwright';
import {createRelay} from '../server.mjs';
import {createHmac} from 'node:crypto';
import {execFileSync} from 'node:child_process';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import https from 'node:https';
import assert from 'node:assert/strict';
import {fileURLToPath} from 'node:url';
const root=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'../../..');
const key='browser-test-secret-'.repeat(4),temporary=fs.mkdtempSync(path.join(os.tmpdir(),'TaskFlowRemoteTest-'));
const openssl=process.env.OPENSSL_EXE||(process.platform==='win32'?'C:\\xampp\\apache\\bin\\openssl.exe':'openssl');
let browser,relay,assets;
function ticket(role){const payload=Buffer.from(JSON.stringify({aud:'taskflow-relay',sid:1,sub:role==='agent'?11:22,role,exp:Math.floor(Date.now()/1000)+45})).toString('base64url');return payload+'.'+createHmac('sha256',key).update(payload).digest('base64url');}
try {
    execFileSync(openssl,['req','-x509','-newkey','rsa:2048','-nodes','-keyout',path.join(temporary,'key.pem'),'-out',path.join(temporary,'cert.pem'),'-days','1','-subj','/CN=localhost'],{stdio:'ignore',windowsHide:true,env:{...process.env,OPENSSL_CONF:process.platform==='win32'?'NUL':'/dev/null'}});
    const tls={key:fs.readFileSync(path.join(temporary,'key.pem')),cert:fs.readFileSync(path.join(temporary,'cert.pem'))};
    relay=createRelay({key,tls,port:0});await relay.listen();
    assets=https.createServer(tls,(req,res)=>{const filename=req.url==='/remote-transport.js'?path.join(root,'public/js/remote-transport.js'):path.join(root,'remote/agent/web/agent.html');res.writeHead(200,{'Content-Type':req.url.endsWith('.js')?'application/javascript':'text/html'});res.end(fs.readFileSync(filename));});
    await new Promise(resolve=>assets.listen(0,'127.0.0.1',resolve));
    browser=await chromium.launch(process.platform==='win32'?{channel:'msedge',headless:true}:{headless:true});
    const context=await browser.newContext({ignoreHTTPSErrors:true});
    const agent=await context.newPage(),viewer=await context.newPage();
    let mode=null,inputs=[];
    await agent.exposeFunction('nativeBridge',async message=>{
        if(message.kind==='signal')await viewer.evaluate(async ({type,payload})=>{await window.testPeer.acceptSignal(type,payload);},{type:message.type,payload:message.payload});
        if(message.kind==='ready')mode=message.transport;
        if(message.kind==='input')inputs.push(message);
        if(message.kind==='error')throw new Error(message.message);
    });
    await agent.addInitScript(()=>{window.chrome.webview={postMessage:message=>window.nativeBridge(message)};});
    await agent.goto('https://127.0.0.1:'+assets.address().port+'/agent.html');
    await viewer.addScriptTag({path:path.join(root,'public/js/remote-transport.js')});
    await viewer.exposeFunction('viewerSignal',async(type,payload)=>agent.evaluate(data=>agentPoll(data),{relay_ticket:ticket('agent'),signals:[{signal_type:type,payload}]}));
    async function start(offer) {
        mode=null;inputs=[];
        const config={iceServers:[],relayUrl:'wss://127.0.0.1:'+relay.server.address().port};
        await agent.evaluate(options=>agentStart(options),{config,sessionCode:'REM-TEST'});
        await agent.evaluate(data=>agentPoll(data),{relay_ticket:ticket('agent'),signals:[]});
        await viewer.evaluate(({config,token})=>{
            window.received=null;window.viewerMode=null;
            window.testPeer=new TaskFlowTransport({...config,role:'viewer',ticket:token,onSignal:(type,payload)=>window.viewerSignal(type,payload),
                onReady:transport=>window.viewerMode=transport,onFrame:async blob=>window.received=Array.from(new Uint8Array(await blob.arrayBuffer())),onError:message=>window.error=message});
        },{config,token:ticket('viewer')});
        if(offer)await viewer.evaluate(()=>window.testPeer.offer());
        await viewer.waitForFunction(()=>window.viewerMode===(window.expectedMode||'p2p'),null,{timeout:20000});
    }
    await start(true);
    assert.equal(mode,'p2p');
    const frame=Buffer.alloc(90000,77);
    await agent.evaluate(base64=>agentFrame(base64),frame.toString('base64'));
    await viewer.waitForFunction(()=>window.received?.length===90000);
    assert.deepEqual(await viewer.evaluate(()=>window.received),[...frame]);
    await viewer.evaluate(()=>window.testPeer.sendInput({action:'move',x:0.25,y:0.75}));
    await new Promise(resolve=>setTimeout(resolve,100));assert.equal(inputs[0].x,0.25);assert.equal(inputs[0].sessionCode,'REM-TEST');
    console.log('PASS: actual browser P2P/DTLS, chunked frames, control, and agent bridge');
    await agent.evaluate(()=>agentStop());await viewer.evaluate(()=>{window.testPeer.close();window.expectedMode='relay';});
    await start(false);assert.equal(mode,'relay');
    await agent.evaluate(base64=>agentFrame(base64),Buffer.from('tls-relay-frame').toString('base64'));
    await viewer.waitForFunction(()=>window.received?.length===15);
    assert.equal(Buffer.from(await viewer.evaluate(()=>window.received)).toString(),'tls-relay-frame');
    await viewer.evaluate(()=>window.testPeer.sendInput({action:'keyDown',key:65}));
    await new Promise(resolve=>setTimeout(resolve,100));assert.equal(inputs[0].key,65);
    console.log('PASS: automatic fallback after P2P timeout, real WSS/TLS frames and control');
    await agent.evaluate(()=>agentStop());await viewer.evaluate(()=>window.testPeer.close());
} finally {
    await browser?.close();relay?.close();assets?.close();
    // Only files in the fresh, explicitly checked temporary directory are removed.
    if(path.dirname(temporary)===os.tmpdir()&&path.basename(temporary).startsWith('TaskFlowRemoteTest-')) {
        for(const filename of ['key.pem','cert.pem'])if(fs.existsSync(path.join(temporary,filename)))fs.unlinkSync(path.join(temporary,filename));
        fs.rmdirSync(temporary);
    }
}
