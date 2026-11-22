<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class JumpSession extends Model
{
    /**
     * Creates an exclusive session on a server.
     * If there is an existing ACTIVE session, kicks it out and records who kicked them.
     */
    public function createExclusiveSession(int $serverId, int $userId, ?string $clientIp): array
    {
        $this->db->beginTransaction();

        try {
            // 1. Find any current ACTIVE session on this server
            $stmtFind = $this->db->prepare("
                SELECT s.*, u.username, u.full_name 
                FROM jump_server_sessions s
                JOIN users u ON s.user_id = u.id
                WHERE s.server_id = :sid AND s.status = 'ACTIVE'
            ");
            $stmtFind->execute(['sid' => $serverId]);
            $existingSessions = $stmtFind->fetchAll(PDO::FETCH_ASSOC);

            $kickedInfo = [];
            foreach ($existingSessions as $existing) {
                // If it's already the same user, we can just replace the old token
                // If it's a different user, they get KICKED
                $stmtKick = $this->db->prepare("
                    UPDATE jump_server_sessions 
                    SET status = 'KICKED', 
                        ended_at = NOW(), 
                        kicked_by_user_id = :kicker 
                    WHERE id = :id
                ");
                $stmtKick->execute([
                    'kicker' => $userId,
                    'id'     => $existing['id']
                ]);

                $kickedInfo[] = [
                    'session_id' => $existing['id'],
                    'user_id'    => $existing['user_id'],
                    'username'   => $existing['username'],
                    'full_name'  => $existing['full_name']
                ];
            }

            // 2. Generate a unique session token
            $sessionToken = bin2hex(random_bytes(32));

            // 3. Insert new ACTIVE session
            $stmtInsert = $this->db->prepare("
                INSERT INTO jump_server_sessions (session_token, server_id, user_id, client_ip, status, started_at, last_heartbeat_at)
                VALUES (:token, :sid, :uid, :ip, 'ACTIVE', NOW(), NOW())
            ");
            $stmtInsert->execute([
                'token' => $sessionToken,
                'sid'   => $serverId,
                'uid'   => $userId,
                'ip'    => $clientIp
            ]);
            $newSessionId = (int)$this->db->lastInsertId();

            $this->db->commit();

            return [
                'session_id'    => $newSessionId,
                'session_token' => $sessionToken,
                'kicked_users'  => $kickedInfo
            ];
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Polling check for session status (heartbeat).
     * If ACTIVE, updates last_heartbeat_at.
     * If KICKED, returns info about who kicked them.
     */
    public function checkSessionStatus(string $sessionToken): ?array
    {
        $stmt = $this->db->prepare("
            SELECT s.*, 
                   kicker.full_name as kicker_name, 
                   kicker.username as kicker_username
            FROM jump_server_sessions s
            LEFT JOIN users kicker ON s.kicked_by_user_id = kicker.id
            WHERE s.session_token = :token
            LIMIT 1
        ");
        $stmt->execute(['token' => $sessionToken]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$session) {
            return null;
        }

        if ($session['status'] === 'ACTIVE') {
            // Update heartbeat
            $stmtUpdate = $this->db->prepare("UPDATE jump_server_sessions SET last_heartbeat_at = NOW() WHERE id = :id");
            $stmtUpdate->execute(['id' => $session['id']]);
        }

        return $session;
    }

    public function terminateSession(string $sessionToken): bool
    {
        $stmt = $this->db->prepare("
            UPDATE jump_server_sessions 
            SET status = 'TERMINATED', ended_at = NOW() 
            WHERE session_token = :token AND status = 'ACTIVE'
        ");
        return $stmt->execute(['token' => $sessionToken]);
    }

    public function forceKick(int $sessionId, int $adminUserId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE jump_server_sessions 
            SET status = 'KICKED', ended_at = NOW(), kicked_by_user_id = :kicker
            WHERE id = :id AND status = 'ACTIVE'
        ");
        return $stmt->execute([
            'kicker' => $adminUserId,
            'id'     => $sessionId
        ]);
    }

    public function logCommand(int $sessionId, int $serverId, int $userId, string $command): void
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO jump_command_logs (session_id, server_id, user_id, command, created_at)
                VALUES (:sid, :srv_id, :uid, :cmd, NOW())
            ");
            $stmt->execute([
                'sid'    => $sessionId,
                'srv_id' => $serverId,
                'uid'    => $userId,
                'cmd'    => $command
            ]);
        } catch (\Throwable $e) {
            // non-blocking
        }
    }

    public function getActiveSessions(): array
    {
        $stmt = $this->db->query("
            SELECT s.*, 
                   srv.name as server_name, srv.ip_address, srv.os_type, srv.protocol,
                   u.full_name, u.full_name as user_name, u.username, u.email
            FROM jump_server_sessions s
            JOIN jump_servers srv ON s.server_id = srv.id
            JOIN users u ON s.user_id = u.id
            WHERE s.status = 'ACTIVE'
            ORDER BY s.started_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCommandLogs(int $limit = 100): array
    {
        $stmt = $this->db->prepare("
            SELECT l.*, 
                   srv.name as server_name, srv.ip_address,
                   u.full_name, u.full_name as user_name, u.username
            FROM jump_command_logs l
            JOIN jump_servers srv ON l.server_id = srv.id
            JOIN users u ON l.user_id = u.id
            ORDER BY l.created_at DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

