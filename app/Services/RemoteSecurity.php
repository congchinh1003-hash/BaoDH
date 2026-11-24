<?php
namespace App\Services;

use RuntimeException;

final class RemoteSecurity
{
    public static function installationConfig(): array
    {
        // This trusted default is the public deployment supplied by the owner.
        // Never construct installer URLs from an untrusted HTTP Host header.
        $renderUrl = rtrim(getenv('RENDER_EXTERNAL_URL') ?: '', '/');
        $url = rtrim(getenv('REMOTE_PUBLIC_URL') ?: ($renderUrl ? $renderUrl . '/public' : 'https://baodh-kfy4.onrender.com/public'), '/');
        if (!filter_var($url, FILTER_VALIDATE_URL) || parse_url($url, PHP_URL_SCHEME) !== 'https') {
            throw new RuntimeException('Địa chỉ máy chủ tải agent phải sử dụng HTTPS.');
        }
        return ['public_url' => $url];
    }

    public static function config(): array
    {
        $url = self::installationConfig()['public_url'];
        $relay = getenv('REMOTE_RELAY_URL') ?: preg_replace('/^https:/', 'wss:', $url) . '/remote-support/relay';
        $key = getenv('REMOTE_RELAY_SECRET') ?: '';
        if (!filter_var($relay, FILTER_VALIDATE_URL) || parse_url($relay, PHP_URL_SCHEME) !== 'wss' || strlen($key) < 32) {
            throw new RuntimeException('Máy chủ hỗ trợ từ xa chưa sẵn sàng. Bạn vẫn có thể tải và cài agent; vui lòng thử kết nối lại sau khi máy chủ cập nhật.');
        }
        $ice = json_decode(getenv('REMOTE_ICE_SERVERS') ?: '[{"urls":"stun:stun.l.google.com:19302"}]', true);
        if (!is_array($ice)) throw new RuntimeException('REMOTE_ICE_SERVERS must be a JSON array.');
        return ['public_url' => $url, 'relay_url' => $relay, 'key' => $key, 'ice_servers' => $ice];
    }

    public static function token(): string { return bin2hex(random_bytes(32)); }
    public static function hash(string $token): string { return hash('sha256', $token); }

    public static function relayTicket(array $session, string $role, ?int $now = null): string
    {
        $now ??= time();
        $claims = ['aud' => 'taskflow-relay', 'sid' => (int)$session['id'], 'role' => $role,
            'sub' => $role === 'agent' ? (int)$session['device_id'] : (int)$session['staff_id'], 'exp' => $now + 45];
        $payload = rtrim(strtr(base64_encode(json_encode($claims, JSON_THROW_ON_ERROR)), '+/', '-_'), '=');
        $sig = hash_hmac('sha256', $payload, self::config()['key'], true);
        return $payload . '.' . rtrim(strtr(base64_encode($sig), '+/', '-_'), '=');
    }

    public static function operatorDevice(int $userId): string
    {
        $secret = $_COOKIE['taskflow_remote_operator'] ?? '';
        if (!is_string($secret) || !preg_match('/^[a-f0-9]{64}$/D', $secret)) {
            $secret = self::token();
            setcookie('taskflow_remote_operator', $secret, ['expires' => time() + 31536000,
                'path' => '/', 'secure' => true, 'httponly' => true, 'samesite' => 'Strict']);
            $_COOKIE['taskflow_remote_operator'] = $secret;
        }
        return self::hash($userId . ':' . $secret);
    }
}
