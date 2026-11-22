<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\BackupService;
use PDO;

class BackupController extends Controller
{
    private BackupService $backupService;
    private PDO $db;

    public function __construct()
    {
        $this->backupService = new BackupService();
        $this->db = Database::getConnection();
    }

    private function logAudit(string $action, ?string $newVal, ?string $reason): void
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO audit_logs (user_id, action, object_type, object_id, old_value, new_value, reason, created_at)
                VALUES (:uid, :act, 'BACKUP', NULL, NULL, :new_val, :reason, NOW())
            ");
            $stmt->execute([
                'uid'     => Auth::id(),
                'act'     => $action,
                'new_val' => $newVal,
                'reason'  => $reason
            ]);
        } catch (\Throwable $e) {}
    }

    public function index(Request $request, Response $response): string
    {
        if (!Auth::can('menu.backup') && !Auth::can('system.backup')) {
            Session::setFlash('danger', 'Bạn không có quyền truy cập khu vực Sao lưu & Phục hồi CSDL.');
            $response->redirect('/dashboard');
            return '';
        }

        $backups = $this->backupService->listBackups();
        $totalSize = array_sum(array_column($backups, 'size'));

        return $this->render('admin/backups', [
            'pageTitle'  => 'Sao Lưu & Khôi Phục Cơ Sở Dữ Liệu (Disaster Recovery)',
            'backups'    => $backups,
            'totalSize'  => $this->formatBytes($totalSize),
            'count'      => count($backups)
        ]);
    }

    public function create(Request $request, Response $response): void
    {
        if (!Auth::can('system.backup')) {
            Session::setFlash('error', 'Bạn không có quyền thực hiện sao lưu dữ liệu.');
            $response->redirect('/admin/backups');
            return;
        }

        $note = trim($request->input('note', ''));
        try {
            $result = $this->backupService->createBackup($note);
            $this->backupService->cleanupOldBackups(20);

            $this->logAudit('CREATE_BACKUP', $result['filename'], "Tạo bản sao lưu CSDL ({$result['tables']} bảng, {$result['size']} bytes)");
            Session::setFlash('success', "Đã tạo bản sao lưu thành công: <strong>{$result['filename']}</strong> ({$result['tables']} bảng dữ liệu).");
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Lỗi khi sao lưu dữ liệu: ' . $e->getMessage());
        }

        $response->redirect('/admin/backups');
    }

    public function download(Request $request, Response $response, string $filename): void
    {
        if (!Auth::can('system.backup')) {
            Session::setFlash('error', 'Bạn không có quyền tải xuống file sao lưu.');
            $response->redirect('/admin/backups');
            return;
        }

        $cleanName = basename($filename);
        $filepath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'backups' . DIRECTORY_SEPARATOR . $cleanName;

        if (!file_exists($filepath)) {
            Session::setFlash('error', "Không tìm thấy file sao lưu: {$cleanName}");
            $response->redirect('/admin/backups');
            return;
        }

        $this->logAudit('DOWNLOAD_BACKUP', $cleanName, "Tải xuống file sao lưu CSDL");

        header('Content-Description: File Transfer');
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $cleanName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }

    public function restore(Request $request, Response $response): void
    {
        if (!Auth::can('system.backup')) {
            Session::setFlash('error', 'Bạn không có quyền phục hồi cơ sở dữ liệu.');
            $response->redirect('/admin/backups');
            return;
        }

        $filename = trim($request->input('filename', ''));
        if (empty($filename)) {
            Session::setFlash('error', 'Vui lòng chọn file sao lưu cần phục hồi.');
            $response->redirect('/admin/backups');
            return;
        }

        $result = $this->backupService->restoreBackup($filename);
        if ($result['success']) {
            $this->logAudit('RESTORE_BACKUP', $filename, "Khôi phục thành công toàn bộ cơ sở dữ liệu");
            Session::setFlash('success', $result['message']);
        } else {
            Session::setFlash('error', $result['message']);
        }

        $response->redirect('/admin/backups');
    }

    public function delete(Request $request, Response $response): void
    {
        if (!Auth::can('system.backup')) {
            Session::setFlash('error', 'Bạn không có quyền xóa file sao lưu.');
            $response->redirect('/admin/backups');
            return;
        }

        $filename = trim($request->input('filename', ''));
        if (empty($filename)) {
            Session::setFlash('error', 'File sao lưu không hợp lệ.');
            $response->redirect('/admin/backups');
            return;
        }

        if ($this->backupService->deleteBackup($filename)) {
            $this->logAudit('DELETE_BACKUP', $filename, "Xóa bản sao lưu CSDL");
            Session::setFlash('success', "Đã xóa bản sao lưu '{$filename}'.");
        } else {
            Session::setFlash('error', "Không thể xóa bản sao lưu '{$filename}'.");
        }

        $response->redirect('/admin/backups');
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

