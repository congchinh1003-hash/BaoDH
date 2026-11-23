<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Model;
use PDO;

class RemoteSession extends Model
{
    public function __construct()
    {
        parent::__construct();
        self::ensureTablesExist();
    }

    public static function ensureTablesExist(): void
    {
        static $checked = false;
        if ($checked) return;
        $checked = true;

        $db = Database::getConnection();
        try {
            $db->exec("
                CREATE TABLE IF NOT EXISTS `remote_support_sessions` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `session_code` VARCHAR(20) NOT NULL UNIQUE,
                    `ticket_id` INT NULL,
                    `staff_id` INT NOT NULL,
                    `client_id` INT NOT NULL,
                    `status` VARCHAR(30) NOT NULL DEFAULT 'WAITING',
                    `notes` TEXT NULL,
                    `ultraviewer_id` VARCHAR(50) NULL,
                    `ultraviewer_pass` VARCHAR(50) NULL,
                    `started_at` DATETIME NULL,
                    `ended_at` DATETIME NULL,
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX `idx_code` (`session_code`),
                    INDEX `idx_ticket` (`ticket_id`),
                    INDEX `idx_status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
            $db->exec("
                CREATE TABLE IF NOT EXISTS `remote_support_signals` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `session_id` INT NOT NULL,
                    `sender_type` VARCHAR(20) NOT NULL,
                    `signal_type` VARCHAR(30) NOT NULL,
                    `payload` LONGTEXT NOT NULL,
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX `idx_session_sig` (`session_id`, `id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
            $db->exec("
                CREATE TABLE IF NOT EXISTS `remote_support_control_events` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `session_id` INT NOT NULL,
                    `event_type` VARCHAR(30) NOT NULL,
                    `payload` TEXT NOT NULL,
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX `idx_ctrl_sess` (`session_id`, `id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
            $db->exec("
                CREATE TABLE IF NOT EXISTS `remote_sessions` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `ticket_id` INT NOT NULL,
                    `it_id` INT NOT NULL,
                    `enduser_id` INT NOT NULL,
                    `ws_url` VARCHAR(255) NOT NULL DEFAULT 'ws://127.0.0.1:6080',
                    `session_pass` VARCHAR(50) NOT NULL DEFAULT '123456',
                    `status` ENUM('pending', 'accepted', 'rejected', 'ended', 'expired') DEFAULT 'pending',
                    `expires_at` DATETIME NULL,
                    `responded_at` DATETIME NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX `idx_rs_ticket` (`ticket_id`),
                    INDEX `idx_rs_status` (`status`),
                    INDEX `idx_rs_user` (`enduser_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
            try {
                $db->exec("ALTER TABLE `remote_support_sessions` ADD COLUMN `agent_online` TINYINT(1) DEFAULT 0, ADD COLUMN `agent_resolution` VARCHAR(30) NULL, ADD COLUMN `agent_last_seen` DATETIME NULL, ADD COLUMN `ws_url` VARCHAR(255) NULL, ADD COLUMN `session_pass` VARCHAR(50) NULL");
            } catch (\Throwable $e) {}
        } catch (\Throwable $e) {
            error_log("RemoteSession ensureTablesExist notice: " . $e->getMessage());
        }
    }

    public function createSession(int $staffId, int $clientId, ?int $ticketId = null, ?string $notes = null): array
    {
        $pin = (string)random_int(100000, 999999);
        $code = 'REM-' . $pin;

        $stmt = $this->db->prepare("
            INSERT INTO remote_support_sessions (session_code, ticket_id, staff_id, client_id, status, notes)
            VALUES (:session_code, :ticket_id, :staff_id, :client_id, 'WAITING', :notes)
        ");
        $stmt->execute([
            'session_code' => $code,
            'ticket_id'    => $ticketId,
            'staff_id'     => $staffId,
            'client_id'    => $clientId,
            'notes'        => $notes
        ]);

        return $this->findByCode($code) ?? ['id' => (int)$this->db->lastInsertId(), 'session_code' => $code];
    }

    public function findByCode(string $code): ?array
    {
        $code = trim(strtoupper($code));
        $prefixed = str_starts_with($code, 'REM-') ? $code : ('REM-' . $code);
        $stmt = $this->db->prepare("
            SELECT s.*, 
                   COALESCE(st.full_name, 'Chưa có kỹ thuật viên') as staff_name, 
                   st.email as staff_email, st.username as staff_username,
                   cl.full_name as client_name, cl.email as client_email, cl.department as client_department,
                   t.ticket_code, t.title as ticket_title
            FROM remote_support_sessions s
            LEFT JOIN users st ON s.staff_id = st.id
            JOIN users cl ON s.client_id = cl.id
            LEFT JOIN tickets t ON s.ticket_id = t.id
            WHERE s.session_code = :code OR s.session_code = :prefixed
            LIMIT 1
        ");
        $stmt->execute([
            'code'     => $code,
            'prefixed' => $prefixed
        ]);
        return $stmt->fetch() ?: null;
    }

    public function findActiveForTicket(int $ticketId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT s.*, COALESCE(st.full_name, 'Chưa có kỹ thuật viên') as staff_name, cl.full_name as client_name
            FROM remote_support_sessions s
            LEFT JOIN users st ON s.staff_id = st.id
            JOIN users cl ON s.client_id = cl.id
            WHERE s.ticket_id = :ticket_id AND s.status IN ('WAITING', 'CONNECTED')
            ORDER BY s.id DESC LIMIT 1
        ");
        $stmt->execute(['ticket_id' => $ticketId]);
        return $stmt->fetch() ?: null;
    }

    public function findActiveForClient(int $clientId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT s.*, COALESCE(st.full_name, 'Chưa có kỹ thuật viên') as staff_name, t.ticket_code, t.title as ticket_title
            FROM remote_support_sessions s
            LEFT JOIN users st ON s.staff_id = st.id
            LEFT JOIN tickets t ON s.ticket_id = t.id
            WHERE s.client_id = :client_id AND s.status = 'WAITING'
            ORDER BY s.id DESC LIMIT 1
        ");
        $stmt->execute(['client_id' => $clientId]);
        return $stmt->fetch() ?: null;
    }

    public function findOrCreateActiveForClient(int $clientId): array
    {
        $existing = $this->findActiveForClient($clientId);
        if ($existing) {
            return $existing;
        }

        $pin = (string)random_int(100000, 999999);
        $code = 'REM-' . $pin;

        $stmt = $this->db->prepare("
            INSERT INTO remote_support_sessions (session_code, ticket_id, staff_id, client_id, status, notes)
            VALUES (:session_code, NULL, 0, :client_id, 'WAITING', 'Phiên hỗ trợ do người dùng tạo')
        ");
        $stmt->execute([
            'session_code' => $code,
            'client_id'    => $clientId
        ]);

        return $this->findByCode($code) ?? ['session_code' => $code, 'status' => 'WAITING'];
    }

    public function assignStaff(int $sessionId, int $staffId): void
    {
        $stmt = $this->db->prepare("UPDATE remote_support_sessions SET staff_id = :staff_id WHERE id = :id");
        $stmt->execute(['staff_id' => $staffId, 'id' => $sessionId]);
    }

    public function updateStatus(int $id, string $status): void
    {
        $extra = "";
        if ($status === 'CONNECTED') {
            $extra = ", started_at = COALESCE(started_at, NOW())";
        } elseif ($status === 'ENDED' || $status === 'REJECTED') {
            $extra = ", ended_at = NOW()";
        }
        $stmt = $this->db->prepare("UPDATE remote_support_sessions SET status = :status $extra WHERE id = :id");
        $stmt->execute(['status' => $status, 'id' => $id]);
    }

    public function updateUltraviewer(int $id, string $uvId, string $uvPass): void
    {
        $stmt = $this->db->prepare("UPDATE remote_support_sessions SET ultraviewer_id = :uv_id, ultraviewer_pass = :uv_pass WHERE id = :id");
        $stmt->execute(['uv_id' => $uvId, 'uv_pass' => $uvPass, 'id' => $id]);
    }

    public function addSignal(int $sessionId, string $senderType, string $signalType, string $payload): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO remote_support_signals (session_id, sender_type, signal_type, payload)
            VALUES (:session_id, :sender_type, :signal_type, :payload)
        ");
        $stmt->execute([
            'session_id'  => $sessionId,
            'sender_type' => $senderType,
            'signal_type' => $signalType,
            'payload'     => $payload
        ]);
        $id = (int)$this->db->lastInsertId();

        // Periodically purge old frame signals to keep DB lightweight (1 in 8 chance)
        if (mt_rand(1, 8) === 1) {
            try {
                $this->db->exec("DELETE FROM remote_support_signals WHERE signal_type = 'FRAME' AND created_at < DATE_SUB(NOW(), INTERVAL 1 MINUTE)");
            } catch (\Throwable $e) {}
        }

        return $id;
    }

    public function getSignals(int $sessionId, string $forType, int $afterId = 0): array
    {
        // When 'STAFF' queries signals, get signals sent by 'CLIENT'. Vice-versa.
        $senderType = ($forType === 'STAFF') ? 'CLIENT' : 'STAFF';
        $stmt = $this->db->prepare("
            SELECT * FROM remote_support_signals
            WHERE session_id = :session_id AND sender_type = :sender_type AND id > :after_id
            ORDER BY id ASC
        ");
        $stmt->execute([
            'session_id'  => $sessionId,
            'sender_type' => $senderType,
            'after_id'    => $afterId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllRecent(int $limit = 20): array
    {
        $stmt = $this->db->prepare("
            SELECT s.*, 
                   st.full_name as staff_name,
                   cl.full_name as client_name, cl.department as client_department,
                   t.ticket_code, t.title as ticket_title
            FROM remote_support_sessions s
            JOIN users st ON s.staff_id = st.id
            JOIN users cl ON s.client_id = cl.id
            LEFT JOIN tickets t ON s.ticket_id = t.id
            ORDER BY s.id DESC LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addControlEvent(int $sessionId, string $eventType, array $payload): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO remote_support_control_events (session_id, event_type, payload)
            VALUES (:session_id, :event_type, :payload)
        ");
        $stmt->execute([
            'session_id' => $sessionId,
            'event_type' => $eventType,
            'payload'    => json_encode($payload, JSON_UNESCAPED_UNICODE)
        ]);
        $id = (int)$this->db->lastInsertId();

        // Periodically purge old events (1 in 15 chance)
        if (mt_rand(1, 15) === 1) {
            try {
                $this->db->exec("DELETE FROM remote_support_control_events WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 SECOND)");
            } catch (\Throwable $e) {}
        }

        return $id;
    }

    public function getPendingControlEvents(int $sessionId, int $afterId = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT id, event_type, payload FROM remote_support_control_events
            WHERE session_id = :session_id AND id > :after_id
            ORDER BY id ASC LIMIT 50
        ");
        $stmt->execute([
            'session_id' => $sessionId,
            'after_id'   => $afterId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLatestControlEventId(int $sessionId): int
    {
        $stmt = $this->db->prepare("
            SELECT COALESCE(MAX(id), 0) FROM remote_support_control_events
            WHERE session_id = :session_id
        ");
        $stmt->execute(['session_id' => $sessionId]);
        return (int)$stmt->fetchColumn();
    }

    public function updateAgentHeartbeat(int $sessionId, ?string $resolution = null): void
    {
        $stmt = $this->db->prepare("
            UPDATE remote_support_sessions 
            SET agent_online = 1, agent_resolution = COALESCE(:res, agent_resolution), agent_last_seen = NOW()
            WHERE id = :id
        ");
        $stmt->execute(['res' => $resolution, 'id' => $sessionId]);
    }

    public function getAgentStatus(int $sessionId): array
    {
        $stmt = $this->db->prepare("
            SELECT agent_online, agent_resolution,
                   (agent_last_seen IS NOT NULL AND agent_last_seen >= DATE_SUB(NOW(), INTERVAL 8 SECOND)) as is_active
            FROM remote_support_sessions WHERE id = :id
        ");
        $stmt->execute(['id' => $sessionId]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'online'     => (bool)($res['is_active'] ?? false),
            'resolution' => $res['agent_resolution'] ?? '1920x1080'
        ];
    }

    // =========================================================================
    // noVNC / TightVNC WEBSOCKIFY INTEGRATION (REQUEST -> ACCEPT -> CONTROL)
    // =========================================================================

    public function createVncRequest(int $ticketId, int $itId, int $enduserId, string $wsUrl = 'http://127.0.0.1:6080/vnc_lite.html', string $pass = '123456'): int
    {
        try { $this->db->exec("ALTER TABLE remote_sessions ADD COLUMN expires_at DATETIME NULL, ADD COLUMN responded_at DATETIME NULL"); } catch (\Throwable $e) {}
        try { $this->db->exec("ALTER TABLE remote_sessions MODIFY COLUMN status ENUM('pending','accepted','rejected','ended','expired') DEFAULT 'pending'"); } catch (\Throwable $e) {}
        // Cancel existing pending requests for this ticket
        $stmt = $this->db->prepare("UPDATE remote_sessions SET status = 'expired' WHERE ticket_id = :tid AND status = 'pending'");
        $stmt->execute(['tid' => $ticketId]);

        $stmt = $this->db->prepare("
            INSERT INTO remote_sessions (ticket_id, it_id, enduser_id, ws_url, session_pass, status, expires_at)
            VALUES (:ticket_id, :it_id, :enduser_id, :ws_url, :session_pass, 'pending', DATE_ADD(NOW(), INTERVAL 30 SECOND))
        ");
        $stmt->execute([
            'ticket_id'    => $ticketId,
            'it_id'        => $itId,
            'enduser_id'   => $enduserId,
            'ws_url'       => $wsUrl,
            'session_pass' => $pass
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function getPendingVncRequestForUser(int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT rs.*, GREATEST(0, TIMESTAMPDIFF(SECOND, NOW(), rs.expires_at)) AS seconds_remaining, u.full_name as it_name, t.title as ticket_title, t.ticket_code
            FROM remote_sessions rs
            JOIN users u ON rs.it_id = u.id
            JOIN tickets t ON rs.ticket_id = t.id
            WHERE rs.enduser_id = :uid AND rs.status = 'pending' AND (rs.expires_at IS NULL OR rs.expires_at > NOW())
            ORDER BY rs.id DESC LIMIT 1
        ");
        $stmt->execute(['uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getPendingVncRequestForIt(int $itId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT rs.*, u.full_name as it_name, eu.full_name as enduser_name, t.title as ticket_title, t.ticket_code
            FROM remote_sessions rs
            JOIN users u ON rs.it_id = u.id
            JOIN users eu ON rs.enduser_id = eu.id
            JOIN tickets t ON rs.ticket_id = t.id
            WHERE rs.it_id = :it_id AND rs.status = 'pending' AND rs.created_at >= (NOW() - INTERVAL 15 MINUTE)
            ORDER BY rs.id DESC LIMIT 1
        ");
        $stmt->execute(['it_id' => $itId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $row['is_test_self'] = true;
            return $row;
        }
        return null;
    }

    public function expireStaleVncRequests(): void
    {
        $stmt = $this->db->prepare("UPDATE remote_sessions SET status = 'expired' WHERE status = 'pending' AND expires_at IS NOT NULL AND expires_at <= NOW()");
        $stmt->execute();
    }

    public function expireVncSessionIfNeeded(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE remote_sessions SET status = 'expired' WHERE id = :id AND status = 'pending' AND expires_at IS NOT NULL AND expires_at <= NOW()");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function updateVncStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare("UPDATE remote_sessions SET status = :status, responded_at = IF(:status IN ('accepted','rejected'), NOW(), responded_at) WHERE id = :id");
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    public function getVncSession(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT rs.*, u.full_name as it_name, eu.full_name as enduser_name, t.title as ticket_title, t.ticket_code
            FROM remote_sessions rs
            JOIN users u ON rs.it_id = u.id
            JOIN users eu ON rs.enduser_id = eu.id
            JOIN tickets t ON rs.ticket_id = t.id
            WHERE rs.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getActiveVncForTicket(int $ticketId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT rs.*, u.full_name as it_name
            FROM remote_sessions rs
            JOIN users u ON rs.it_id = u.id
            WHERE rs.ticket_id = :tid AND rs.status IN ('pending', 'accepted')
            ORDER BY rs.id DESC LIMIT 1
        ");
        $stmt->execute(['tid' => $ticketId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
