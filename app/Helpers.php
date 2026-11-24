<?php
use App\Core\Language;
use App\Core\Session;

if (!function_exists('asset_url')) {
    function asset_url(string $path): string {
        $path = ltrim($path, '/');
        $file = dirname(__DIR__) . '/public/' . $path;
        $version = is_file($file) ? (string)filemtime($file) : '1';
        return \App\Core\Response::baseUrl() . '/' . $path . '?v=' . $version;
    }
}

if (!function_exists('demo_accounts_by_role')) {
    function demo_accounts_by_role(array $users): array {
        $accounts = [];
        foreach ($users as $user) {
            $roles = array_map('trim', explode(',', strtoupper($user['roles'] ?? 'USER')));
            foreach (['ADMIN', 'IT_MANAGER', 'IT_STAFF', 'USER'] as $role) {
                if (in_array($role, $roles, true)) {
                    $accounts[$role] ??= $user;
                    break;
                }
            }
        }
        return $accounts;
    }
}

if (!function_exists('__')) {
    function __($key, $default = null) {
        return Language::get($key, $default);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token() {
        return Session::csrfToken();
    }
}

if (!function_exists('renderMobileStatusBadge')) {
    function renderMobileStatusBadge(?string $status): string {
        $status = strtoupper($status ?? 'NEW');
        $map = [
            'NEW'              => '<span class="badge-status badge-new"><i class="bi bi-dot"></i>Mới</span>',
            'ASSIGNED'         => '<span class="badge-status badge-assigned"><i class="bi bi-person"></i>Đã phân công</span>',
            'IN_PROGRESS'      => '<span class="badge-status badge-progress"><i class="bi bi-arrow-repeat"></i>Đang xử lý</span>',
            'PENDING_APPROVAL' => '<span class="badge-status badge-pending"><i class="bi bi-shield-exclamation"></i>Chờ duyệt</span>',
            'RESOLVED'         => '<span class="badge-status badge-resolved"><i class="bi bi-check2"></i>Đã giải quyết</span>',
            'CLOSED'           => '<span class="badge-status badge-closed"><i class="bi bi-lock"></i>Đã đóng</span>',
            'CANCELLED'        => '<span class="badge-status badge-closed"><i class="bi bi-x"></i>Đã hủy</span>',
        ];
        return $map[$status] ?? ('<span class="badge bg-secondary">' . htmlspecialchars($status) . '</span>');
    }
}



