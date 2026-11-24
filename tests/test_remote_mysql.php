<?php
declare(strict_types=1);
require_once __DIR__.'/../app/autoload.php';
use App\Core\Database;
use App\Models\RemoteSession;
use App\Services\RemoteSecurity;

// Uses a fresh temporary database; never changes existing application tables.
$config=require __DIR__.'/../app/Config/database.php';
$db=new PDO("mysql:host={$config['host']};port={$config['port']};charset=utf8mb4",$config['username'],$config['password'],$config['options']);
$name='taskflow_remote_test_'.bin2hex(random_bytes(8));
$db->exec("CREATE DATABASE `$name` CHARACTER SET utf8mb4");
try {
    putenv('DB_DATABASE='.$name);
    $pdo=Database::getConnection();
    $pdo->exec("CREATE TABLE users(id INT PRIMARY KEY,full_name VARCHAR(100)); INSERT INTO users VALUES(1,'User'),(2,'IT')");
    // Reproduce the legacy schema and run the additive migration twice.
    $pdo->exec("CREATE TABLE remote_support_sessions(id INT AUTO_INCREMENT PRIMARY KEY,session_code VARCHAR(20) UNIQUE,ticket_id INT NULL,staff_id INT NOT NULL,client_id INT NOT NULL,status VARCHAR(30),notes TEXT,started_at DATETIME NULL,ended_at DATETIME NULL,created_at DATETIME DEFAULT CURRENT_TIMESTAMP)");
    RemoteSession::ensureTablesExist();RemoteSession::ensureTablesExist();
    exec(escapeshellarg(PHP_BINARY).' '.escapeshellarg(__DIR__.'/../database/migrate_remote_support.php'),$migrationOutput,$migrationExit);
    if($migrationExit!==0)throw new RuntimeException('A fresh process could not repeat the migration');
    $model=new RemoteSession();
    $e=$model->issueEnrollment(1);$d=$model->enroll($e['token'],'Windows PC');
    $s=$model->createSession(2,1,null,'mysql',RemoteSecurity::hash('test-device'),$d['device_id']);
    $model->agentPoll($model->authenticate($d['token']));$model->viewerSeen($s);
    $model->decide($s,'accept');$s=$model->findByCode($s['session_code']);
    $model->signal($s,'STAFF','OFFER',['type'=>'offer','sdp'=>'test']);$model->connected($s,'relay');
    if(count($model->signals($s,'CLIENT',0))!==1)throw new RuntimeException('MySQL signaling failed');
    $model->revoke($d['device_id'],1);$s=$model->findByCode($s['session_code']);
    if($s['status']!=='ENDED'||!$s['started_at']||!$s['ended_at']||$model->hasGrant($s))throw new RuntimeException('MySQL consent/audit/revocation failed');
    echo "PASS: MySQL legacy migration, enrollment, consent, signaling, duration, revocation\n";
} finally {
    if(!preg_match('/^taskflow_remote_test_[a-f0-9]{16}$/D',$name))throw new RuntimeException('Unexpected temporary database name');
    $db->exec("DROP DATABASE `$name`");
}
