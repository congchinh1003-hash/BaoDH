<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Ticket;

class AttachmentController extends Controller
{
    private Ticket $ticketModel;

    public function __construct()
    {
        $this->ticketModel = new Ticket();
    }

    /**
     * Inline file view for in-browser preview (Images, PDF, Docx/Xlsx buffers, Text)
     */
    public function view(Request $request, Response $response, string $id): void
    {
        $this->serveFile((int)$id, false);
    }

    /**
     * Force file download with original filename
     */
    public function download(Request $request, Response $response, string $id): void
    {
        $this->serveFile((int)$id, true);
    }

    /**
     * Core handler for serving file streams securely and compatibly across localhost & free hostings
     */
    private function serveFile(int $attachmentId, bool $forceDownload): void
    {
        if (!Auth::check()) {
            http_response_code(401);
            die("Vui lòng đăng nhập để truy cập tệp đính kèm.");
        }

        $att = $this->ticketModel->getAttachmentById($attachmentId);
        if (!$att) {
            http_response_code(404);
            die("Không tìm thấy tệp đính kèm hoặc tệp đã bị xóa.");
        }

        // Permission check
        $userId = (int)Auth::id();
        $isPrivileged = Auth::isAdmin() || Auth::isManager() || Auth::isStaff();
        $isOwner = ($userId === (int)$att['requester_id'] || $userId === (int)$att['uploaded_by']);

        if (!$isPrivileged && !$isOwner) {
            http_response_code(403);
            die("Bạn không có quyền truy cập tệp đính kèm này.");
        }

        // Internal comment note privacy protection
        if (!empty($att['comment_is_internal']) && !$isPrivileged) {
            http_response_code(403);
            die("Tệp đính kèm này thuộc ghi chú nội bộ của bộ phận IT.");
        }

        // Multi-path file resolution for shared/free hosting compatibility
        $relativePath = $att['file_path'] ?? '';
        $fileName = $att['file_name'] ?? basename($relativePath);
        $filePath = $this->resolveFilePath($relativePath);

        if (!$filePath || !is_file($filePath)) {
            http_response_code(404);
            die("Tệp tin không tồn tại trên hệ thống lưu trữ (Storage). Vui lòng liên hệ quản trị viên.");
        }

        $fileSize = filesize($filePath);
        $mime = $this->determineMimeType($filePath, $att['mime_type'] ?? '', $fileName);

        // Sanitize headers
        $cleanFileName = preg_replace('/[^\w\.\-\s]/u', '_', $fileName);
        $encodedName = rawurlencode($fileName);

        // Clear output buffer to prevent corrupted binary transfers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Close session write lock so large files or slow transfers don't lock browser requests
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        if ($forceDownload) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $cleanFileName . '"; filename*=UTF-8\'\'' . $encodedName);
        } else {
            header('Content-Type: ' . $mime);
            header('Content-Disposition: inline; filename="' . $cleanFileName . '"; filename*=UTF-8\'\'' . $encodedName);
        }

        header('Content-Length: ' . $fileSize);
        header('Cache-Control: private, max-age=86400');
        header('X-Content-Type-Options: nosniff');
        header('Accept-Ranges: bytes');

        readfile($filePath);
        exit;
    }

    /**
     * Resolves physical file location considering various deployment structures:
     * - DocumentRoot at public/ (standard VPS/built-in)
     * - DocumentRoot at repository root (InfinityFree, cPanel, 000webhost, shared hosting)
     */
    private function resolveFilePath(string $relativePath): ?string
    {
        $cleanRel = ltrim(str_replace('\\', '/', $relativePath), '/');
        $baseName = basename($cleanRel);

        $candidates = [
            __DIR__ . '/../../public/' . $cleanRel,
            __DIR__ . '/../../' . $cleanRel,
            __DIR__ . '/../../public/uploads/' . $baseName,
            __DIR__ . '/../../uploads/' . $baseName,
            dirname(__DIR__, 2) . '/public/uploads/' . $baseName,
            dirname(__DIR__, 2) . '/uploads/' . $baseName,
        ];

        foreach ($candidates as $candidate) {
            $real = realpath($candidate);
            if ($real && is_file($real) && is_readable($real)) {
                return $real;
            }
        }

        return null;
    }

    /**
     * Determines proper MIME type with strict fallbacks
     */
    private function determineMimeType(string $filePath, string $dbMime, string $fileName): string
    {
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $mimeMap = [
            'pdf'  => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'doc'  => 'application/msword',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls'  => 'application/vnd.ms-excel',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'ppt'  => 'application/vnd.ms-powerpoint',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'svg'  => 'image/svg+xml',
            'txt'  => 'text/plain; charset=utf-8',
            'log'  => 'text/plain; charset=utf-8',
            'csv'  => 'text/csv; charset=utf-8',
            'json' => 'application/json; charset=utf-8',
            'xml'  => 'application/xml; charset=utf-8',
            'zip'  => 'application/zip',
            'rar'  => 'application/x-rar-compressed',
            '7z'   => 'application/x-7z-compressed',
        ];

        if (isset($mimeMap[$ext])) {
            return $mimeMap[$ext];
        }

        if (!empty($dbMime) && $dbMime !== 'application/octet-stream') {
            return $dbMime;
        }

        $detected = @mime_content_type($filePath);
        return $detected ?: 'application/octet-stream';
    }
}
