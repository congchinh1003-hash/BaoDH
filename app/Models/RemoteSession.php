<?php
namespace App\Models;

use App\Core\Database;
use App\Services\RemoteSecurity;
use DomainException;
use PDO;

/** Existing session table retained; screen frames never enter the database. Times are UTC. */
class RemoteSession
{
    private PDO $db;
    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
        if ($db === null) self::ensureTablesExist();
    }
    public static function ensureTablesExist(): void
    {
        static $checked = false;
        if ($checked) return;
        $db = Database::getConnection();
        $db->exec("CREATE TABLE IF NOT EXISTS remote_support_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY, session_code VARCHAR(20) NOT NULL UNIQUE,
            ticket_id INT NULL, staff_id INT NOT NULL, client_id INT NOT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'WAITING', notes TEXT NULL,
            started_at DATETIME NULL, ended_at DATETIME NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $columns = $db->query('SHOW COLUMNS FROM remote_support_sessions')->fetchAll(PDO::FETCH_COLUMN);
        foreach (['device_id' => 'INT NULL', 'operator_device' => 'VARCHAR(64) NULL',
            'approved_at' => 'DATETIME NULL', 'expires_at' => 'DATETIME NULL',
            'agent_seen_at' => 'DATETIME NULL', 'viewer_seen_at' => 'DATETIME NULL',
            'transport' => 'VARCHAR(20) NULL', 'end_reason' => 'VARCHAR(60) NULL'] as $name => $type) {
            if (!in_array($name, $columns, true)) $db->exec("ALTER TABLE remote_support_sessions ADD COLUMN $name $type");
        }
        $db->exec("CREATE TABLE IF NOT EXISTS remote_agent_devices (
            id INT AUTO_INCREMENT PRIMARY KEY, client_id INT NOT NULL, name VARCHAR(100) NOT NULL,
            enrollment_hash VARCHAR(64) NULL UNIQUE, enrollment_expires DATETIME NULL,
            token_hash VARCHAR(64) NULL UNIQUE, last_seen DATETIME NULL,
            revoked_at DATETIME NULL, created_at DATETIME NOT NULL,
            INDEX idx_remote_device_client (client_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $db->exec("CREATE TABLE IF NOT EXISTS remote_access_grants (
            device_id INT NOT NULL, staff_id INT NOT NULL, operator_device VARCHAR(64) NOT NULL,
            granted_at DATETIME NOT NULL, revoked_at DATETIME NULL,
            PRIMARY KEY(device_id, staff_id, operator_device)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $db->exec("CREATE TABLE IF NOT EXISTS remote_support_signals (
            id INT AUTO_INCREMENT PRIMARY KEY, session_id INT NOT NULL,
            sender_type VARCHAR(20) NOT NULL, signal_type VARCHAR(30) NOT NULL,
            payload LONGTEXT NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_session_sig(session_id, id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $checked = true;
    }
    private function run(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->db->prepare($sql); $stmt->execute($params); return $stmt;
    }
    private function now(): string { return gmdate('Y-m-d H:i:s'); }
    public function devices(int $clientId): array
    {
        return $this->run('SELECT id,name,last_seen,token_hash IS NOT NULL AS enrolled FROM remote_agent_devices WHERE client_id=? AND revoked_at IS NULL ORDER BY id DESC', [$clientId])->fetchAll(PDO::FETCH_ASSOC);
    }
    public function issueEnrollment(int $clientId): array
    {
        $token = RemoteSecurity::token();
        $this->run('INSERT INTO remote_agent_devices (client_id,name,enrollment_hash,enrollment_expires,created_at) VALUES (?,?,?,?,?)',
            [$clientId, 'Windows PC', RemoteSecurity::hash($token), gmdate('Y-m-d H:i:s', time()+900), $this->now()]);
        return ['id' => (int)$this->db->lastInsertId(), 'token' => $token];
    }
    public function enroll(string $enrollment, string $name): array
    {
        $token = RemoteSecurity::token(); $hash = RemoteSecurity::hash($enrollment);
        $changed = $this->run('UPDATE remote_agent_devices SET token_hash=?,name=?,enrollment_hash=NULL WHERE enrollment_hash=? AND enrollment_expires>? AND revoked_at IS NULL',
            [RemoteSecurity::hash($token), mb_substr($name,0,100), $hash, $this->now()])->rowCount();
        if ($changed !== 1) throw new DomainException('Enrollment is invalid, expired, or already used.');
        $device = $this->authenticate($token);
        return ['device_id' => (int)$device['id'], 'token' => $token];
    }
    public function authenticate(string $token): array
    {
        if (!preg_match('/^[a-f0-9]{64}$/D', $token)) throw new DomainException('Invalid agent credential.');
        $row = $this->run('SELECT * FROM remote_agent_devices WHERE token_hash=? AND revoked_at IS NULL', [RemoteSecurity::hash($token)])->fetch(PDO::FETCH_ASSOC);
        if (!$row) throw new DomainException('Invalid agent credential.');
        return $row;
    }
    public function createSession(int $staffId, int $clientId, ?int $ticketId, ?string $notes, string $operatorDevice, ?int $deviceId = null): array
    {
        $devices = array_values(array_filter($this->devices($clientId), fn($d) => (bool)$d['enrolled']));
        if ($deviceId === null && count($devices) !== 1) throw new DomainException('Select an enrolled PC in Remote Support. The user must install the agent once.');
        $deviceId ??= (int)$devices[0]['id'];
        $device = $this->run('SELECT * FROM remote_agent_devices WHERE id=? AND client_id=? AND token_hash IS NOT NULL AND revoked_at IS NULL', [$deviceId, $clientId])->fetch(PDO::FETCH_ASSOC);
        if (!$device) throw new DomainException('The selected PC does not belong to this user.');
        $this->db->beginTransaction();
        try {
            $this->run('UPDATE remote_agent_devices SET name=name WHERE id=?', [$deviceId]);
            $this->expireStale();
            $busy = $this->run("SELECT id FROM remote_support_sessions WHERE device_id=? AND status IN ('WAITING','ACCEPTED','CONNECTED')", [$deviceId])->fetchColumn();
            if ($busy) throw new DomainException('This PC already has a support session. End it before starting another.');
            $code = 'REM-' . strtoupper(bin2hex(random_bytes(8)));
            $this->run("INSERT INTO remote_support_sessions (session_code,ticket_id,staff_id,client_id,device_id,operator_device,status,notes,created_at,expires_at) VALUES (?,?,?,?,?,?,'WAITING',?,?,?)",
                [$code,$ticketId,$staffId,$clientId,$deviceId,$operatorDevice,$notes,$this->now(),gmdate('Y-m-d H:i:s',time()+300)]);
            $this->db->commit(); return $this->findByCode($code);
        } catch (\Throwable $e) { $this->db->rollBack(); throw $e; }
    }
    public function findByCode(string $code): ?array
    {
        $row = $this->run('SELECT s.*,st.full_name AS staff_name,cl.full_name AS client_name,d.name AS device_name FROM remote_support_sessions s LEFT JOIN users st ON st.id=s.staff_id JOIN users cl ON cl.id=s.client_id LEFT JOIN remote_agent_devices d ON d.id=s.device_id WHERE s.session_code=?', [strtoupper(trim($code))])->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
    public function findActiveForTicket(int $ticketId): ?array
    {
        $this->expireStale();
        $code = $this->run("SELECT session_code FROM remote_support_sessions WHERE ticket_id=? AND device_id IS NOT NULL AND status IN ('WAITING','ACCEPTED','CONNECTED') ORDER BY id DESC LIMIT 1", [$ticketId])->fetchColumn();
        return $code ? $this->findByCode($code) : null;
    }
    public function recent(int $userId, bool $staff): array
    {
        $this->expireStale();
        return $this->run('SELECT s.*,st.full_name AS staff_name,cl.full_name AS client_name,d.name AS device_name FROM remote_support_sessions s LEFT JOIN users st ON st.id=s.staff_id JOIN users cl ON cl.id=s.client_id LEFT JOIN remote_agent_devices d ON d.id=s.device_id WHERE ' . ($staff ? 's.staff_id' : 's.client_id') . '=? ORDER BY s.id DESC LIMIT 50', [$userId])->fetchAll(PDO::FETCH_ASSOC);
    }
    public function hasGrant(array $s): bool
    {
        return (bool)$this->run('SELECT 1 FROM remote_access_grants WHERE device_id=? AND staff_id=? AND operator_device=? AND revoked_at IS NULL', [$s['device_id'],$s['staff_id'],$s['operator_device']])->fetchColumn();
    }
    public function agentPoll(array $device): ?array
    {
        $this->expireStale();
        $this->run('UPDATE remote_agent_devices SET last_seen=? WHERE id=?', [$this->now(),$device['id']]);
        $code = $this->run("SELECT session_code FROM remote_support_sessions WHERE device_id=? AND status IN ('WAITING','ACCEPTED','CONNECTED') ORDER BY id DESC LIMIT 1", [$device['id']])->fetchColumn();
        if (!$code) return null;
        $s = $this->findByCode($code);
        $this->run('UPDATE remote_support_sessions SET agent_seen_at=? WHERE id=?', [$this->now(),$s['id']]);
        $s['trusted'] = $this->hasGrant($s); return $s;
    }
    public function decide(array $s, string $action): void
    {
        if ($s['status'] !== 'WAITING' || $s['expires_at'] <= $this->now()) throw new DomainException('Session is no longer waiting for consent.');
        if ($action === 'deny') { $this->end($s, 'REJECTED', 'user_denied'); return; }
        if ($action !== 'accept') throw new DomainException('Invalid consent decision.');
        $this->db->beginTransaction();
        try {
            $this->run('UPDATE remote_agent_devices SET name=name WHERE id=?', [$s['device_id']]);
            $changed = $this->run("UPDATE remote_support_sessions SET status='ACCEPTED',approved_at=?,agent_seen_at=? WHERE id=? AND status='WAITING' AND expires_at>?", [$this->now(),$this->now(),$s['id'],$this->now()])->rowCount();
            if ($changed !== 1) throw new DomainException('Session is no longer waiting for consent.');
            $this->run('DELETE FROM remote_access_grants WHERE device_id=? AND staff_id=? AND operator_device=?', [$s['device_id'],$s['staff_id'],$s['operator_device']]);
            $this->run('INSERT INTO remote_access_grants (device_id,staff_id,operator_device,granted_at) VALUES (?,?,?,?)', [$s['device_id'],$s['staff_id'],$s['operator_device'],$this->now()]);
            $this->db->commit();
        } catch (\Throwable $e) { $this->db->rollBack(); throw $e; }
    }
    public function connected(array $s, string $transport): void
    {
        if (!in_array($transport,['p2p','relay'],true) || !in_array($s['status'],['ACCEPTED','CONNECTED'],true) || !$this->hasGrant($s)) throw new DomainException('Consent is required before sharing.');
        $this->run("UPDATE remote_support_sessions SET status='CONNECTED',started_at=COALESCE(started_at,?),transport=? WHERE id=? AND status IN ('ACCEPTED','CONNECTED')", [$this->now(),$transport,$s['id']]);
    }
    public function end(array $s, string $status = 'ENDED', string $reason = 'disconnected'): void
    {
        $this->run("UPDATE remote_support_sessions SET status=?,ended_at=?,end_reason=? WHERE id=? AND status IN ('WAITING','ACCEPTED','CONNECTED')", [$status,$this->now(),$reason,$s['id']]);
        $this->run('DELETE FROM remote_support_signals WHERE session_id=?', [$s['id']]);
    }
    public function revoke(int $deviceId, int $clientId): void
    {
        if (!$this->run('SELECT id FROM remote_agent_devices WHERE id=? AND client_id=?', [$deviceId,$clientId])->fetchColumn()) throw new DomainException('PC does not belong to you.');
        $this->db->beginTransaction();
        try {
            $this->run('UPDATE remote_agent_devices SET name=name WHERE id=?', [$deviceId]);
            $this->run('UPDATE remote_access_grants SET revoked_at=? WHERE device_id=?', [$this->now(),$deviceId]);
            $this->run("UPDATE remote_support_sessions SET status='ENDED',ended_at=?,end_reason='access_revoked' WHERE device_id=? AND status IN ('WAITING','ACCEPTED','CONNECTED')", [$this->now(),$deviceId]);
            $this->run('DELETE FROM remote_support_signals WHERE session_id IN (SELECT id FROM remote_support_sessions WHERE device_id=?)', [$deviceId]);
            $this->db->commit();
        } catch (\Throwable $e) { $this->db->rollBack(); throw $e; }
    }
    public function expireStale(): void
    {
        $now = $this->now(); $cutoff = gmdate('Y-m-d H:i:s',time()-20);
        $this->run("UPDATE remote_support_sessions SET status='EXPIRED',ended_at=expires_at,end_reason='consent_timeout' WHERE status='WAITING' AND expires_at<=?", [$now]);
        $stale=$this->run("SELECT id,approved_at,agent_seen_at,viewer_seen_at FROM remote_support_sessions WHERE status IN ('ACCEPTED','CONNECTED') AND ((agent_seen_at IS NOT NULL AND agent_seen_at<?) OR (viewer_seen_at IS NOT NULL AND viewer_seen_at<?) OR (viewer_seen_at IS NULL AND approved_at<?))", [$cutoff,$cutoff,$cutoff])->fetchAll(PDO::FETCH_ASSOC);
        foreach ($stale as $s) {
            $seen=array_filter([$s['agent_seen_at'],$s['viewer_seen_at'] ?? $s['approved_at']]);
            $last=min(array_map(fn($date)=>strtotime($date.' UTC'),$seen));
            $ended=gmdate('Y-m-d H:i:s',min(time(),$last+20));
            $this->run("UPDATE remote_support_sessions SET status='ENDED',ended_at=?,end_reason='connection_lost' WHERE id=? AND status IN ('ACCEPTED','CONNECTED')", [$ended,$s['id']]);
        }
        $this->run("DELETE FROM remote_support_signals WHERE session_id IN (SELECT id FROM remote_support_sessions WHERE status NOT IN ('WAITING','ACCEPTED','CONNECTED'))");
    }
    public function signal(array $s, string $role, string $type, array $payload): void
    {
        if (!in_array($s['status'],['ACCEPTED','CONNECTED'],true) || !$this->hasGrant($s)) throw new DomainException('Consent is required before signaling.');
        if (!in_array($type,['OFFER','ANSWER','CANDIDATE'],true) || ($type==='OFFER' && $role!=='STAFF') || ($type==='ANSWER' && $role!=='CLIENT')) throw new DomainException('Invalid signaling direction.');
        $json = json_encode($payload,JSON_THROW_ON_ERROR);
        if (strlen($json)>65536) throw new DomainException('Signal is too large.');
        $count = (int)$this->run('SELECT COUNT(*) FROM remote_support_signals WHERE session_id=?',[$s['id']])->fetchColumn();
        if ($count >= 512) throw new DomainException('Signal limit exceeded.');
        $this->run('INSERT INTO remote_support_signals (session_id,sender_type,signal_type,payload,created_at) VALUES (?,?,?,?,?)', [$s['id'],$role,$type,$json,$this->now()]);
    }
    public function signals(array $s, string $role, int $after): array
    {
        if (!in_array($s['status'],['ACCEPTED','CONNECTED'],true)) return [];
        $rows = $this->run('SELECT id,signal_type,payload FROM remote_support_signals WHERE session_id=? AND sender_type=? AND id>? ORDER BY id LIMIT 100', [$s['id'],$role==='STAFF'?'CLIENT':'STAFF',max(0,$after)])->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) $row['payload']=json_decode($row['payload'],true);
        return $rows;
    }
    public function viewerSeen(array $s): void
    {
        $this->run('UPDATE remote_support_sessions SET viewer_seen_at=? WHERE id=?', [$this->now(),$s['id']]);
    }
}
