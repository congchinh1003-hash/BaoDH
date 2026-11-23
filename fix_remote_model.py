import pathlib
p = pathlib.Path('app/Models/RemoteSession.php')
t = p.read_text(encoding='utf-8')
B = chr(96)
old = B + 'created_at' + B + ' TIMESTAMP DEFAULT CURRENT_TIMESTAMP,'
assert old in t, 'created_at TIMESTAMP not found'
new = B + 'expires_at' + B + ' DATETIME NULL,\n                    ' + B + 'responded_at' + B + ' DATETIME NULL,\n                    ' + old
t = t.replace(old, new, 1)
assert "ENUM('pending', 'accepted', 'rejected', 'ended')" in t
t = t.replace("ENUM('pending', 'accepted', 'rejected', 'ended')", "ENUM('pending', 'accepted', 'rejected', 'ended', 'expired')", 1)
old_create = "public function createVncRequest(int $ticketId, int $itId, int $enduserId, string $wsUrl = 'http://127.0.0.1:6080/vnc_lite.html', string $pass = '123456'): int\n    {\n"
assert old_create in t, 'createVncRequest signature not found'
mig = '        try { $this->db->exec("ALTER TABLE remote_sessions ADD COLUMN expires_at DATETIME NULL, ADD COLUMN responded_at DATETIME NULL"); } catch (\\Throwable $e) {}\n'
mig += '        try { $this->db->exec("ALTER TABLE remote_sessions MODIFY COLUMN status ENUM(' + chr(39) + 'pending' + chr(39) + ',' + chr(39) + 'accepted' + chr(39) + ',' + chr(39) + 'rejected' + chr(39) + ',' + chr(39) + 'ended' + chr(39) + ',' + chr(39) + 'expired' + chr(39) + ') DEFAULT ' + chr(39) + 'pending' + chr(39) + '"); } catch (\\Throwable $e) {}\n'
t = t.replace(old_create, old_create + mig, 1)
t = t.replace("UPDATE remote_sessions SET status = 'ended' WHERE ticket_id = :tid AND status = 'pending'", "UPDATE remote_sessions SET status = 'expired' WHERE ticket_id = :tid AND status = 'pending'", 1)
old_ins = "INSERT INTO remote_sessions (ticket_id, it_id, enduser_id, ws_url, session_pass, status)"
assert old_ins in t, 'insert header not found'
t = t.replace(old_ins, "INSERT INTO remote_sessions (ticket_id, it_id, enduser_id, ws_url, session_pass, status, expires_at)", 1)
old_val = "VALUES (:ticket_id, :it_id, :enduser_id, :ws_url, :session_pass, 'pending')"
assert old_val in t, 'insert values not found'
t = t.replace(old_val, "VALUES (:ticket_id, :it_id, :enduser_id, :ws_url, :session_pass, 'pending', DATE_ADD(NOW(), INTERVAL 30 SECOND))", 1)
p.write_text(t, encoding='utf-8')
print('patched model part 1')
