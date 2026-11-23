import pathlib
p = pathlib.Path('app/Models/RemoteSession.php')
t = p.read_text(encoding='utf-8')
t = t.replace("SELECT rs.*, u.full_name as it_name, t.title as ticket_title, t.ticket_code", "SELECT rs.*, GREATEST(0, TIMESTAMPDIFF(SECOND, NOW(), rs.expires_at)) AS seconds_remaining, u.full_name as it_name, t.title as ticket_title, t.ticket_code", 1)
t = t.replace("WHERE rs.enduser_id = :uid AND rs.status = 'pending'", "WHERE rs.enduser_id = :uid AND rs.status = 'pending' AND (rs.expires_at IS NULL OR rs.expires_at > NOW())", 1)
old = '    public function updateVncStatus(int $id, string $status): bool'
assert old in t, 'updateVncStatus not found'
extra = '    public function expireStaleVncRequests(): void\n    {\n        $stmt = $this->db->prepare("UPDATE remote_sessions SET status = \'expired\' WHERE status = \'pending\' AND expires_at IS NOT NULL AND expires_at <= NOW()");\n        $stmt->execute();\n    }\n\n    public function expireVncSessionIfNeeded(int $id): bool\n    {\n        $stmt = $this->db->prepare("UPDATE remote_sessions SET status = \'expired\' WHERE id = :id AND status = \'pending\' AND expires_at IS NOT NULL AND expires_at <= NOW()");\n        $stmt->execute([' + chr(39) + 'id' + chr(39) + ' => $id]);\n        return $stmt->rowCount() > 0;\n    }\n\n'
t = t.replace(old, extra + old, 1)
t = t.replace('"UPDATE remote_sessions SET status = :status WHERE id = :id"', '"UPDATE remote_sessions SET status = :status, responded_at = IF(:status IN (' + chr(39) + 'accepted' + chr(39) + ',' + chr(39) + 'rejected' + chr(39) + '), NOW(), responded_at) WHERE id = :id"', 1)
p.write_text(t, encoding='utf-8')
print('patched model part 2')
