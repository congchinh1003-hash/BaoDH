// Exercise the real Apache WebSocket proxy configuration on Windows/XAMPP.
// All listeners are loopback-only; traffic is generated test data.
import {createRelay} from '../server.mjs';
import {WebSocket} from 'ws';
import {once} from 'node:events';
import {createHmac} from 'node:crypto';
import {execFileSync,spawn} from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import https from 'node:https';
import net from 'node:net';
import assert from 'node:assert/strict';
import {fileURLToPath} from 'node:url';

if(process.platform!=='win32')throw new Error('This optional proxy test uses installed XAMPP on Windows.');
const root=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'../../..');
const directory=path.join(root,'remote/test-artifacts',Date.now().toString());fs.mkdirSync(directory,{recursive:true});
fs.copyFileSync(path.join(root,'.htaccess'),path.join(directory,'.htaccess'));
fs.mkdirSync(path.join(directory,'public'));
fs.copyFileSync(path.join(root,'public/.htaccess'),path.join(directory,'public/.htaccess'));
fs.writeFileSync(path.join(directory,'public/index.php'),'Application routing fixture');
const apacheRoot=process.env.APACHE_ROOT||'C:/xampp/apache';
const key='apache-relay-test-'.repeat(4);
let relay,apache;
async function freePort(){const server=net.createServer();await new Promise(resolve=>server.listen(0,'127.0.0.1',resolve));const port=server.address().port;await new Promise(resolve=>server.close(resolve));return port;}
function ticket(role){const payload=Buffer.from(JSON.stringify({aud:'taskflow-relay',sid:7,sub:role==='agent'?11:22,role,exp:Math.floor(Date.now()/1000)+45})).toString('base64url');return payload+'.'+createHmac('sha256',key).update(payload).digest('base64url');}
async function connect(url,role){const ws=new WebSocket(url,{rejectUnauthorized:false});await once(ws,'open');const auth=once(ws,'message');ws.send(JSON.stringify({kind:'auth',token:ticket(role)}));await auth;return ws;}
const posix=p=>p.replaceAll('\\','/');
try {
    const port=await freePort();relay=createRelay({key,behindProxy:true,port:0});await relay.listen();
    execFileSync(path.join(apacheRoot,'bin/openssl.exe'),['req','-x509','-newkey','rsa:2048','-nodes','-keyout',path.join(directory,'key.pem'),'-out',path.join(directory,'cert.pem'),'-days','1','-subj','/CN=localhost'],{stdio:'ignore',windowsHide:true,env:{...process.env,OPENSSL_CONF:'NUL'}});
    const modules=['authz_core','authz_host','mime','dir','rewrite','proxy','proxy_http','proxy_wstunnel','socache_shmcb','ssl'];
    const proxy=fs.readFileSync(path.join(root,'remote/apache-relay.conf'),'utf8').replaceAll('8081',String(relay.server.address().port));
    const config=`ServerRoot "${posix(apacheRoot)}"\nServerName localhost\nListen 127.0.0.1:${port}\nPidFile "${posix(path.join(directory,'apache.pid'))}"\nErrorLog "${posix(path.join(directory,'apache.log'))}"\nLogLevel warn\n`+
        modules.map(name=>`LoadModule ${name}_module modules/mod_${name}.so`).join('\n')+`\nTypesConfig "${posix(path.join(apacheRoot,'conf/mime.types'))}"\nDocumentRoot "${posix(directory)}"\n<Directory "${posix(directory)}">\nRequire all granted\nAllowOverride All\n</Directory>\n`+
        `SSLEngine on\nSSLCertificateFile "${posix(path.join(directory,'cert.pem'))}"\nSSLCertificateKeyFile "${posix(path.join(directory,'key.pem'))}"\n`+proxy;
    const filename=path.join(directory,'httpd.conf');fs.writeFileSync(filename,config);
    execFileSync(path.join(apacheRoot,'bin/httpd.exe'),['-t','-f',filename],{windowsHide:true,stdio:'pipe'});
    apache=spawn(path.join(apacheRoot,'bin/httpd.exe'),['-X','-f',filename],{windowsHide:true,stdio:'ignore'});
    const get=suffix=>new Promise((resolve,reject)=>https.get(`https://127.0.0.1:${port}${suffix}`,{rejectUnauthorized:false},response=>{let body='';response.on('data',data=>body+=data);response.on('end',()=>resolve({status:response.statusCode,body}));}).on('error',reject));
    let health;for(let attempt=0;attempt<30;attempt++){try{health=await get('/public/remote-support/relay/health');break;}catch{await new Promise(resolve=>setTimeout(resolve,100));}}
    assert.deepEqual(health,{status:200,body:'ok'});assert.deepEqual(await get('/remote-support/relay/health'),{status:200,body:'ok'});
    const url=`wss://127.0.0.1:${port}/public/remote-support/relay`;
    const anonymous=new WebSocket(url,{rejectUnauthorized:false});await once(anonymous,'open');const rejected=once(anonymous,'close');
    anonymous.send(JSON.stringify({kind:'auth',token:'invalid-test-ticket'}));assert.equal((await rejected)[0],4003);
    const agent=await connect(url,'agent'),viewer=await connect(url,'viewer');
    try {
        await new Promise(resolve=>setTimeout(resolve,30));
        const received=once(viewer,'message');agent.send(Buffer.from('public-proxy-screen'));assert.equal((await received)[0].toString(),'public-proxy-screen');
        const input=once(agent,'message');viewer.send(JSON.stringify({kind:'input',action:'move',x:0.5,y:0.5}));assert.equal(JSON.parse((await input)[0]).action,'move');
        console.log('PASS: actual Apache /public proxy, HTTPS health, WSS upgrades, authenticated frames/control');
    } finally {agent.terminate();viewer.terminate();}
} catch(error) {
    if(fs.existsSync(path.join(directory,'apache.log')))console.error(fs.readFileSync(path.join(directory,'apache.log'),'utf8'));
    throw error;
} finally {
    apache?.kill();relay?.close();
    if(apache)await once(apache,'exit').catch(()=>{});
    for(const name of ['key.pem','cert.pem'])if(fs.existsSync(path.join(directory,name)))fs.unlinkSync(path.join(directory,name));
}
