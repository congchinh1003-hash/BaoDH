<?php
declare(strict_types=1);
require_once __DIR__.'/../app/autoload.php';

use App\Models\RemoteSession;
use App\Services\RemoteSecurity;
use App\Controllers\RemoteSupportController;
use App\Core\{Auth,Request,Response,Session};

putenv('REMOTE_PUBLIC_URL=https://taskflow.example.test');
putenv('REMOTE_RELAY_URL=wss://relay.example.test');
putenv('REMOTE_RELAY_SECRET='.str_repeat('test-key-',8));
$pdo=new PDO('sqlite::memory:',null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY,full_name TEXT); INSERT INTO users VALUES (1,"User"),(2,"IT one"),(3,"IT two");
CREATE TABLE remote_agent_devices (id INTEGER PRIMARY KEY AUTOINCREMENT,client_id INTEGER,name TEXT,enrollment_hash TEXT UNIQUE,enrollment_expires TEXT,token_hash TEXT UNIQUE,last_seen TEXT,revoked_at TEXT,created_at TEXT);
CREATE TABLE remote_support_sessions (id INTEGER PRIMARY KEY AUTOINCREMENT,session_code TEXT UNIQUE,ticket_id INTEGER,staff_id INTEGER,client_id INTEGER,device_id INTEGER,operator_device TEXT,status TEXT,notes TEXT,created_at TEXT,expires_at TEXT,approved_at TEXT,started_at TEXT,ended_at TEXT,agent_seen_at TEXT,viewer_seen_at TEXT,transport TEXT,end_reason TEXT);
CREATE TABLE remote_access_grants (device_id INTEGER,staff_id INTEGER,operator_device TEXT,granted_at TEXT,revoked_at TEXT,PRIMARY KEY(device_id,staff_id,operator_device));
CREATE TABLE remote_support_signals (id INTEGER PRIMARY KEY AUTOINCREMENT,session_id INTEGER,sender_type TEXT,signal_type TEXT,payload TEXT,created_at TEXT);');
$model=new RemoteSession($pdo);$count=0;
function check(bool $ok,string $name): void {global $count;if(!$ok)throw new RuntimeException($name);$count++;}
function denied(callable $action,string $name): void {try{$action();}catch(DomainException $e){check(true,$name);return;}throw new RuntimeException('Expected denial: '.$name);}
$enrollment=$model->issueEnrollment(1);$device=$model->enroll($enrollment['token'],'PC one');
check($device['device_id']===$enrollment['id'],'Enrollment returns bound PC');
check($model->authenticate($device['token'])['client_id']===1,'Agent is bound to the owning account');
denied(fn()=>$model->enroll($enrollment['token'],'Replay'),'Enrollment is single use');
denied(fn()=>$model->authenticate(str_repeat('0',64)),'Anonymous agent is denied');
$expired=$model->issueEnrollment(1);$pdo->exec("UPDATE remote_agent_devices SET enrollment_expires='2000-01-01' WHERE id=".$expired['id']);
denied(fn()=>$model->enroll($expired['token'],'Expired'),'Expired enrollment is denied');
$operator=RemoteSecurity::hash('operator one');
$s=$model->createSession(2,1,9,'test',$operator,$device['device_id']);
check(strlen($s['session_code'])===20,'Session IDs contain 64 bits of randomness');
check(!$model->hasGrant($s),'First connection needs consent');
denied(fn()=>$model->connected($s,'p2p'),'Cannot connect before consent');
denied(fn()=>$model->signal($s,'STAFF','OFFER',['sdp'=>'fake']),'Cannot signal before consent');
denied(fn()=>$model->createSession(3,1,null,'competing',RemoteSecurity::hash('other'),$device['device_id']),'One operator per PC');
$model->decide($s,'deny');$s=$model->findByCode($s['session_code']);
check($s['status']==='REJECTED'&&!$model->hasGrant($s),'Deny persists no permission');
denied(fn()=>$model->decide($s,'accept'),'Deny cannot be replayed as accept');
$s=$model->createSession(2,1,9,'accepted',$operator,$device['device_id']);$model->decide($s,'accept');$s=$model->findByCode($s['session_code']);
check($model->hasGrant($s)&&$s['status']==='ACCEPTED'&&!$s['started_at'],'Consent does not start the duration clock');
$model->signal($s,'STAFF','OFFER',['type'=>'offer','sdp'=>'fixture']);
check(count($model->signals($s,'CLIENT',0))===1&&count($model->signals($s,'STAFF',0))===0,'Signals go only to the opposite peer');
denied(fn()=>$model->signal($s,'CLIENT','OFFER',[]),'Agent cannot impersonate viewer');
denied(fn()=>$model->signal($s,'CLIENT','FRAME',[]),'Database cannot be used for screen frames');
$model->connected($s,'relay');$s=$model->findByCode($s['session_code']);$start=$s['started_at'];
check($s['status']==='CONNECTED'&&$start!==null&&$s['transport']==='relay','Actual transport connection starts audit');
$model->connected($s,'p2p');check($model->findByCode($s['session_code'])['started_at']===$start,'Fallback does not reset duration');
$model->end($s);$ended=$model->findByCode($s['session_code']);$model->end($ended);
check($ended['ended_at']!==null&&$model->findByCode($s['session_code'])['ended_at']===$ended['ended_at'],'End is idempotent and retains history');
check(count($model->signals($ended,'CLIENT',0))===0,'Ended session has no signaling');
$s=$model->createSession(2,1,null,'again',$operator,$device['device_id']);
check($model->agentPoll($model->authenticate($device['token']))['trusted']===true,'Next session remembers this account/device');
$different=$s;$different['operator_device']=RemoteSecurity::hash('another device');check(!$model->hasGrant($different),'A different IT device requires consent');
$different=$s;$different['staff_id']=3;check(!$model->hasGrant($different),'A different IT account requires consent');
$ticket=RemoteSecurity::relayTicket($s,'viewer',1234);$parts=explode('.',$ticket);$claims=json_decode(base64_decode(strtr($parts[0],'-_','+/')),true);
check($claims['exp']===1279&&$claims['sid']===(int)$s['id']&&$claims['sub']===2,'Relay credentials are short-lived and identity bound');
$model->revoke($device['device_id'],1);check(!$model->hasGrant($s)&&$model->findByCode($s['session_code'])['status']==='ENDED','Revoke ends current requests and removes remembered access');
denied(fn()=>$model->decide($s,'accept'),'Late consent cannot undo revocation');
denied(fn()=>$model->revoke($device['device_id'],3),'Another account cannot revoke this PC');
$s=$model->createSession(2,1,null,'timeout',$operator,$device['device_id']);$pdo->exec("UPDATE remote_support_sessions SET expires_at='2000-01-01' WHERE id=".$s['id']);$model->expireStale();
check($model->findByCode($s['session_code'])['status']==='EXPIRED','Unanswered requests expire');
$s=$model->createSession(2,1,null,'drop',$operator,$device['device_id']);
$pdo->exec("UPDATE remote_support_sessions SET agent_seen_at='2000-01-01' WHERE id=".$s['id']);
$model->decide($s,'accept');$model->expireStale();
check($model->findByCode($s['session_code'])['status']==='ACCEPTED','A slow first consent dialog does not immediately expire the accepted session');
$pdo->exec("UPDATE remote_support_sessions SET agent_seen_at='2000-01-01 00:00:00' WHERE id=".$s['id']);$model->expireStale();
check($model->findByCode($s['session_code'])['end_reason']==='connection_lost','Dead agent ends the session');
check($model->findByCode($s['session_code'])['ended_at']==='2000-01-01 00:00:20','Delayed cleanup does not inflate abandoned session duration');

// Exercise controller authorization with real session state and response capture.
class CaptureResponse extends Response {public array $data=[];public int $status=0;public function json(array $data,int $statusCode=200): void {$this->data=$data;$this->status=$statusCode;}}
class BodyRequest extends Request {public function __construct(private array $body){}public function getBody(): array {return $this->body;}}
Session::start();Session::set('user_id',3);Session::set('user_roles',['IT_STAFF']);$_COOKIE['taskflow_remote_operator']=str_repeat('b',64);
$controller=new RemoteSupportController($model);$response=new CaptureResponse();
$controller->viewerPoll(new BodyRequest([]),$response,$s['session_code']);check($response->status===403,'Other authenticated staff cannot read a session');
$_SERVER['HTTP_AUTHORIZATION']='Bearer '.str_repeat('a',64);$controller->agentPoll(new BodyRequest([]),$response);check($response->status===403,'Forged agent bearer rejected at controller');
$secondEnrollment=$model->issueEnrollment(3);$second=$model->enroll($secondEnrollment['token'],'Other PC');$_SERVER['HTTP_AUTHORIZATION']='Bearer '.$second['token'];
$controller->agentAction(new BodyRequest(['action'=>'end','session_code'=>$s['session_code']]),$response);check($response->status===403,'Authenticated wrong PC cannot end a session');
Session::set('user_id',1);Session::set('user_roles',['USER']);$controller->viewerPoll(new BodyRequest([]),$response,$s['session_code']);check($response->status===403,'Enduser cannot obtain viewer control ticket');
putenv('REMOTE_RELAY_SECRET=short');try{RemoteSecurity::config();throw new LogicException('Weak secret was accepted');}catch(RuntimeException $e){check(true,'Fail closed for missing TLS configuration');}

// The public install route must work before a relay is configured or deployed.
putenv('REMOTE_PUBLIC_URL');putenv('REMOTE_RELAY_URL');putenv('REMOTE_RELAY_SECRET');putenv('RENDER_EXTERNAL_URL');
check(RemoteSecurity::installationConfig()['public_url']==='https://baodh-kfy4.onrender.com/public','Installer defaults to the owner supplied public URL');
$_SERVER['SCRIPT_NAME']='/public/index.php';
ob_start();$controller->downloadAgent(new BodyRequest([]),$response);$installer=ob_get_clean();
check(str_starts_with($installer,'$TaskFlowInstallOptions = '),'Authenticated enduser can download without relay settings');
preg_match("/TaskFlowInstallOptions = '([^']+)'/",$installer,$match);
$options=json_decode(base64_decode($match[1]),true);
check($options['url']==='https://baodh-kfy4.onrender.com/public','Personalized installer preserves /public in all API addresses');
check($options['package_sha256']===hash_file('sha256',__DIR__.'/../public/agent/TaskFlow-Agent.zip'),'Download pins the distributed Windows package');
$installed=$model->enroll($options['enrollment'],'Downloaded agent');check($model->authenticate($installed['token'])['client_id']===1,'Downloaded installer belongs to the requesting enduser');
putenv('REMOTE_RELAY_SECRET='.str_repeat('test-key-',8));
check(RemoteSecurity::config()['relay_url']==='wss://baodh-kfy4.onrender.com/public/remote-support/relay','Bundled relay uses the same public application URL');
putenv('RENDER_EXTERNAL_URL=https://another-instance.onrender.com');
check(RemoteSecurity::installationConfig()['public_url']==='https://another-instance.onrender.com/public','Render native URL also keeps the public prefix');
putenv('RENDER_EXTERNAL_URL');

// Render the actual hub: connection readiness must never disable installation.
require_once __DIR__.'/../app/Helpers.php';
$isStaff=false;$configurationError='Relay unavailable';$installationError=null;$devices=[];$sessions=[];$allUsers=[];
ob_start();require __DIR__.'/../app/Views/remote/index.php';$hub=ob_get_clean();
preg_match('/<form[^>]+remote-support\/install.*?<\/form>/s',$hub,$downloadForm);
check(isset($downloadForm[0])&&!str_contains($downloadForm[0],'disabled'),'Enduser download button stays enabled without a relay');
check(str_contains($downloadForm[0],'/public/remote-support/install'),'Download form posts to the correct public route');
$isStaff=true;ob_start();require __DIR__.'/../app/Views/remote/index.php';$staffHub=ob_get_clean();
check(!str_contains($staffHub,'/remote-support/install'),'IT hub does not suggest installing an agent on the IT machine');
echo "PASS: $count remote support assertions\n";
