<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\User;

class AssetController extends Controller
{
    private Asset $assetModel;
    private AssetCategory $categoryModel;

    public function __construct()
    {
        $this->assetModel = new Asset();
        $this->categoryModel = new AssetCategory();
    }

    /**
     * List all assets (IT Staff, Manager, Admin)
     */
    public function index(Request $request, Response $response): string
    {
        if (!Auth::can('menu.assets')) {
            $this->setFlash('danger', 'Bạn không có quyền truy cập Quản lý tài sản.');
            $response->redirect('/dashboard');
            return '';
        }

        if (!Auth::isStaff() && !Auth::isManager()) {
            $response->redirect('/my-assets');
            return '';
        }

        $filters = [
            'status'      => $request->input('status'),
            'category_id' => $request->input('category_id'),
            'search'      => $request->input('search'),
        ];

        $kpis = $this->assetModel->getKPIs();
        $assets = $this->assetModel->getAll($filters);
        $categories = $this->categoryModel->getAllActive();

        return $this->render('assets/index', [
            'pageTitle'  => 'Quản lý Tài sản CNTT (Assets)',
            'kpis'       => $kpis,
            'assets'     => $assets,
            'categories' => $categories,
            'filters'    => $filters
        ]);
    }

    /**
     * Show form to add new asset
     */
    public function create(Request $request, Response $response): string
    {
        if (!Auth::isStaff() && !Auth::isManager()) {
            $this->setFlash('danger', 'Bạn không có quyền thêm mới tài sản.');
            $response->redirect('/assets');
        }

        $categories = $this->categoryModel->getAllActive();
        $userModel = new User();
        $users = $userModel->getAllUsers();

        return $this->render('assets/create', [
            'pageTitle'  => 'Thêm Mới Thiết Bị / Tài Sản CNTT',
            'categories' => $categories,
            'users'      => $users
        ]);
    }

    /**
     * Store newly created asset
     */
    public function store(Request $request, Response $response): void
    {
        if (!Auth::isStaff() && !Auth::isManager()) {
            $this->setFlash('danger', 'Bạn không có quyền thực hiện.');
            $response->redirect('/assets');
            return;
        }

        $name = trim($request->input('name') ?? '');
        $categoryId = (int)$request->input('category_id');
        $assetCode = trim($request->input('asset_code') ?? '');

        if (empty($name) || !$categoryId) {
            $this->setFlash('danger', 'Vui lòng nhập đầy đủ tên thiết bị và loại tài sản.');
            $response->redirect('/assets/create');
            return;
        }

        // Auto generate asset code if left blank
        if (empty($assetCode)) {
            $assetCode = $this->assetModel->generateAssetCode($categoryId);
        } else {
            // Check unique
            if ($this->assetModel->findByCode($assetCode)) {
                $this->setFlash('danger', "Mã tài sản {$assetCode} đã tồn tại trong hệ thống.");
                $response->redirect('/assets/create');
                return;
            }
        }

        try {
            $currentUserId = !empty($request->input('current_user_id')) ? (int)$request->input('current_user_id') : null;
            $status = $currentUserId ? 'IN_USE' : ($request->input('status') ?: 'IN_STOCK');

            $assetId = $this->assetModel->create([
                'asset_code'          => $assetCode,
                'name'                => $name,
                'category_id'         => $categoryId,
                'manufacturer'        => trim($request->input('manufacturer') ?? ''),
                'model'               => trim($request->input('model') ?? ''),
                'serial_number'       => trim($request->input('serial_number') ?? ''),
                'purchase_date'       => $request->input('purchase_date') ?: null,
                'warranty_expire'     => $request->input('warranty_expire') ?: null,
                'status'              => $status,
                'department'          => trim($request->input('department') ?? ''),
                'location'            => trim($request->input('location') ?? ''),
                'current_user_id'     => $currentUserId,
                'specifications'      => trim($request->input('specifications') ?? ''),
                'notes'               => trim($request->input('notes') ?? ''),
                'created_by_user_id'  => Auth::id()
            ]);

            $this->setFlash('success', "Thêm mới tài sản {$assetCode} thành công!");
            $response->redirect("/assets/{$assetId}");
        } catch (\Exception $e) {
            $this->setFlash('danger', 'Lỗi khi tạo tài sản: ' . $e->getMessage());
            $response->redirect('/assets/create');
        }
    }

    /**
     * Show Asset Detail Page (Tabs: Specs, Handovers, Repairs, Tickets)
     */
    public function show(Request $request, Response $response, string $id): string
    {
        $assetId = (int)$id;
        $asset = $this->assetModel->findById($assetId);

        if (!$asset) {
            $this->setFlash('danger', 'Không tìm thấy tài sản yêu cầu.');
            $response->redirect('/assets');
        }

        // Permission check: If normal user, only allow viewing if it is assigned to them
        if (!Auth::isStaff() && !Auth::isManager() && (int)$asset['current_user_id'] !== Auth::id()) {
            $this->setFlash('danger', 'Bạn không có quyền xem thông tin tài sản này.');
            $response->redirect('/my-assets');
        }

        $handovers = $this->assetModel->getHandovers($assetId);
        $repairs = $this->assetModel->getRepairs($assetId);
        $tickets = $this->assetModel->getTickets($assetId);

        $userModel = new User();
        $users = $userModel->getAllUsers();

        return $this->render('assets/show', [
            'pageTitle' => "Tài sản {$asset['asset_code']} - {$asset['name']}",
            'asset'     => $asset,
            'handovers' => $handovers,
            'repairs'   => $repairs,
            'tickets'   => $tickets,
            'users'     => $users
        ]);
    }

    /**
     * Edit asset form
     */
    public function edit(Request $request, Response $response, string $id): string
    {
        if (!Auth::isStaff() && !Auth::isManager()) {
            $this->setFlash('danger', 'Bạn không có quyền chỉnh sửa tài sản.');
            $response->redirect('/assets');
        }

        $asset = $this->assetModel->findById((int)$id);
        if (!$asset) {
            $this->setFlash('danger', 'Không tìm thấy tài sản.');
            $response->redirect('/assets');
        }

        $categories = $this->categoryModel->getAllActive();

        return $this->render('assets/edit', [
            'pageTitle'  => "Chỉnh sửa Tài sản {$asset['asset_code']}",
            'asset'      => $asset,
            'categories' => $categories
        ]);
    }

    /**
     * Update asset metadata
     */
    public function update(Request $request, Response $response, string $id): void
    {
        if (!Auth::isStaff() && !Auth::isManager()) {
            $this->setFlash('danger', 'Bạn không có quyền thực hiện.');
            $response->redirect("/assets/{$id}");
            return;
        }

        $assetId = (int)$id;
        $name = trim($request->input('name') ?? '');
        $categoryId = (int)$request->input('category_id');

        if (empty($name) || !$categoryId) {
            $this->setFlash('danger', 'Vui lòng điền đầy đủ tên thiết bị và loại tài sản.');
            $response->redirect("/assets/{$assetId}/edit");
            return;
        }

        try {
            $this->assetModel->update($assetId, [
                'name'            => $name,
                'category_id'     => $categoryId,
                'manufacturer'    => trim($request->input('manufacturer') ?? ''),
                'model'           => trim($request->input('model') ?? ''),
                'serial_number'   => trim($request->input('serial_number') ?? ''),
                'purchase_date'   => $request->input('purchase_date') ?: null,
                'warranty_expire' => $request->input('warranty_expire') ?: null,
                'department'      => trim($request->input('department') ?? ''),
                'location'        => trim($request->input('location') ?? ''),
                'specifications'  => trim($request->input('specifications') ?? ''),
                'notes'           => trim($request->input('notes') ?? '')
            ]);

            $this->setFlash('success', 'Cập nhật thông tin tài sản thành công!');
        } catch (\Exception $e) {
            $this->setFlash('danger', 'Lỗi khi cập nhật: ' . $e->getMessage());
        }

        $response->redirect("/assets/{$assetId}");
    }

    /**
     * Handover asset to user
     */
    public function handover(Request $request, Response $response, string $id): void
    {
        if (!Auth::isStaff() && !Auth::isManager()) {
            $this->setFlash('danger', 'Bạn không có quyền bàn giao tài sản.');
            $response->redirect("/assets/{$id}");
            return;
        }

        $assetId = (int)$id;
        $toUserId = (int)$request->input('to_user_id');
        $location = trim($request->input('location') ?? '');
        $reason = trim($request->input('reason') ?? '');

        if (!$toUserId) {
            $this->setFlash('danger', 'Vui lòng chọn nhân viên tiếp nhận bàn giao.');
            $response->redirect("/assets/{$assetId}");
            return;
        }

        try {
            $this->assetModel->handover($assetId, $toUserId, $location, $reason ?: 'Bàn giao thiết bị sử dụng', Auth::id());
            
            // Send notification to recipient
            $asset = $this->assetModel->findById($assetId);
            $notificationModel = new \App\Models\Notification();
            $notificationModel->create([
                'user_id' => $toUserId,
                'title'   => 'Bàn giao thiết bị CNTT mới',
                'message' => "Bạn vừa được bàn giao thiết bị {$asset['asset_code']} ({$asset['name']}).",
                'type'    => 'asset',
                'link'    => "/assets/{$assetId}"
            ]);

            $this->setFlash('success', 'Bàn giao tài sản cho nhân viên thành công! Lịch sử đã được ghi nhận.');
        } catch (\Exception $e) {
            $this->setFlash('danger', 'Lỗi khi bàn giao: ' . $e->getMessage());
        }

        $response->redirect("/assets/{$assetId}");
    }

    /**
     * Recall asset to IT stock
     */
    public function recall(Request $request, Response $response, string $id): void
    {
        if (!Auth::isStaff() && !Auth::isManager()) {
            $this->setFlash('danger', 'Bạn không có quyền thu hồi tài sản.');
            $response->redirect("/assets/{$id}");
            return;
        }

        $assetId = (int)$id;
        $location = trim($request->input('location') ?? 'Kho IT');
        $reason = trim($request->input('reason') ?? 'Thu hồi thiết bị về kho');

        try {
            $assetBefore = $this->assetModel->findById($assetId);
            $previousUserId = $assetBefore['current_user_id'] ?? null;

            $this->assetModel->recall($assetId, $location, $reason, Auth::id());

            // Notify previous user
            if ($previousUserId && (int)$previousUserId !== (int)Auth::id()) {
                (new \App\Models\Notification())->create([
                    'user_id' => (int)$previousUserId,
                    'title'   => 'Thu hồi thiết bị CNTT',
                    'message' => "Thiết bị {$assetBefore['asset_code']} ({$assetBefore['name']}) đã được thu hồi về kho IT.",
                    'type'    => 'asset',
                    'link'    => "/assets/{$assetId}"
                ]);
            }

            $this->setFlash('success', 'Đã thu hồi thiết bị về kho IT thành công.');
        } catch (\Exception $e) {
            $this->setFlash('danger', 'Lỗi khi thu hồi: ' . $e->getMessage());
        }

        $response->redirect("/assets/{$assetId}");
    }

    /**
     * Transfer asset between locations / users
     */
    public function transfer(Request $request, Response $response, string $id): void
    {
        if (!Auth::isStaff() && !Auth::isManager()) {
            $this->setFlash('danger', 'Bạn không có quyền điều chuyển tài sản.');
            $response->redirect("/assets/{$id}");
            return;
        }

        $assetId = (int)$id;
        $toUserId = !empty($request->input('to_user_id')) ? (int)$request->input('to_user_id') : null;
        $location = trim($request->input('location') ?? '');
        $reason = trim($request->input('reason') ?? 'Điều chuyển thiết bị');

        try {
            $this->assetModel->transfer($assetId, $toUserId, $location, $reason, Auth::id());

            // Notify new recipient if assigned to a user
            if (!empty($toUserId) && (int)$toUserId !== (int)Auth::id()) {
                $asset = $this->assetModel->findById($assetId);
                (new \App\Models\Notification())->create([
                    'user_id' => (int)$toUserId,
                    'title'   => 'Điều chuyển thiết bị CNTT',
                    'message' => "Bạn vừa được điều chuyển tiếp nhận thiết bị {$asset['asset_code']} ({$asset['name']}).",
                    'type'    => 'asset',
                    'link'    => "/assets/{$assetId}"
                ]);
            }

            $this->setFlash('success', 'Điều chuyển thiết bị thành công.');
        } catch (\Exception $e) {
            $this->setFlash('danger', 'Lỗi khi điều chuyển: ' . $e->getMessage());
        }

        $response->redirect("/assets/{$assetId}");
    }

    /**
     * Add repair record
     */
    public function addRepair(Request $request, Response $response, string $id): void
    {
        if (!Auth::isStaff() && !Auth::isManager()) {
            $this->setFlash('danger', 'Bạn không có quyền ghi nhận sửa chữa.');
            $response->redirect("/assets/{$id}");
            return;
        }

        $assetId = (int)$id;
        $issue = trim($request->input('issue_description') ?? '');

        if (empty($issue)) {
            $this->setFlash('danger', 'Vui lòng mô tả sự cố / nội dung cần sửa chữa.');
            $response->redirect("/assets/{$assetId}");
            return;
        }

        try {
            $this->assetModel->addRepair([
                'asset_id'          => $assetId,
                'repair_date'       => $request->input('repair_date') ?: date('Y-m-d'),
                'issue_description' => $issue,
                'root_cause'        => trim($request->input('root_cause') ?? ''),
                'solution'          => trim($request->input('solution') ?? ''),
                'replaced_parts'    => trim($request->input('replaced_parts') ?? ''),
                'cost'              => (float)($request->input('cost') ?? 0),
                'vendor'            => trim($request->input('vendor') ?? ''),
                'performed_by'      => Auth::id(),
                'notes'             => trim($request->input('notes') ?? ''),
                'set_status_repair' => (bool)$request->input('set_status_repair')
            ]);

            $this->setFlash('success', 'Đã lưu thông tin sửa chữa / bảo trì vào lý lịch máy.');
        } catch (\Exception $e) {
            $this->setFlash('danger', 'Lỗi khi ghi nhận sửa chữa: ' . $e->getMessage());
        }

        $response->redirect("/assets/{$assetId}");
    }

    /**
     * Complete repair and restore asset status
     */
    public function completeRepair(Request $request, Response $response, string $id): void
    {
        if (!Auth::isStaff() && !Auth::isManager()) {
            $this->setFlash('danger', 'Bạn không có quyền cập nhật trạng thái sửa chữa.');
            $response->redirect("/assets/{$id}");
            return;
        }

        $assetId = (int)$id;
        $nextStatus = $request->input('next_status') ?: 'IN_STOCK';

        try {
            $this->assetModel->completeRepair($assetId, $nextStatus);
            $this->setFlash('success', "Đã hoàn thành sửa chữa. Trạng thái thiết bị chuyển sang: {$nextStatus}");
        } catch (\Exception $e) {
            $this->setFlash('danger', 'Lỗi: ' . $e->getMessage());
        }

        $response->redirect("/assets/{$assetId}");
    }

    /**
     * User's personal assets view
     */
    public function myAssets(Request $request, Response $response): string
    {
        $userId = Auth::id();
        $assets = $this->assetModel->getByUser($userId);

        return $this->render('assets/my_assets', [
            'pageTitle' => 'Thiết Bị CNTT Của Tôi',
            'assets'    => $assets
        ]);
    }

    /**
     * Printable QR Code Asset Tag (Rule 56)
     */
    public function qr(Request $request, Response $response, string $id): string
    {
        $asset = $this->assetModel->findById((int)$id);
        if (!$asset) {
            $this->setFlash('danger', 'Không tìm thấy thiết bị.');
            $response->redirect('/assets');
        }

        return $this->render('assets/qr_print', [
            'pageTitle' => "In Tem QR Code - {$asset['asset_code']}",
            'asset'     => $asset
        ]);
    }

    /**
     * QR Code Scan Endpoint: /asset/{code}
     */
    public function lookup(Request $request, Response $response, string $code): void
    {
        $asset = $this->assetModel->findByCode($code);
        if (!$asset) {
            $this->setFlash('danger', "Không tìm thấy thiết bị có mã: {$code}");
            $response->redirect('/assets');
            return;
        }

        $response->redirect("/assets/{$asset['id']}");
    }

    /**
     * Change asset status (DAMAGED, LOST, LIQUIDATED, DISPOSED) (Rule 52)
     */
    public function changeStatus(Request $request, Response $response, string $id): void
    {
        if (!Auth::isStaff() && !Auth::isManager()) {
            $this->setFlash('danger', 'Bạn không có quyền thay đổi trạng thái tài sản.');
            $response->redirect("/assets/{$id}");
            return;
        }

        $assetId = (int)$id;
        $status = trim($request->input('status') ?? '');
        $reason = trim($request->input('reason') ?? '');

        if (empty($status) || empty($reason)) {
            $this->setFlash('danger', 'Vui lòng chọn trạng thái mới và nhập lý do thay đổi.');
            $response->redirect("/assets/{$assetId}");
            return;
        }

        try {
            $this->assetModel->changeStatus($assetId, $status, $reason, Auth::id());
            $this->setFlash('success', "Đã cập nhật trạng thái thiết bị sang: {$status}");
        } catch (\Exception $e) {
            $this->setFlash('danger', 'Lỗi: ' . $e->getMessage());
        }

        $response->redirect("/assets/{$assetId}");
    }

    public function exportExcel(Request $request, Response $response): void
    {
        if (!Auth::can('export.assets')) {
            $this->setFlash('danger', 'Bạn không có quyền xuất dữ liệu danh sách Tài sản.');
            $response->redirect('/assets');
            return;
        }

        $assets = $this->assetModel->getAll();

        $filename = 'assets_export_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($out, [
            'Mã Thiết Bị', 'Tên Thiết Bị', 'Loại Tài Sản', 'Số Serial', 'Model',
            'Trạng Thái', 'Người Sử Dụng', 'Phòng Ban', 'Vị Trí', 'Giá Mua (VNĐ)',
            'Ngày Mua', 'Hết Hạn Bảo Hành'
        ]);

        foreach ($assets as $a) {
            fputcsv($out, [
                $a['asset_code'],
                $a['name'],
                $a['category_name'] ?? 'N/A',
                $a['serial_number'] ?? '',
                $a['model'] ?? '',
                $a['status'],
                $a['current_user_name'] ?? 'Chưa bàn giao',
                $a['user_department'] ?? $a['department'] ?? 'N/A',
                $a['location'] ?? '',
                isset($a['purchase_cost']) ? number_format((float)$a['purchase_cost'], 0, ',', '.') : '0',
                !empty($a['purchase_date']) ? date('d/m/Y', strtotime($a['purchase_date'])) : '',
                !empty($a['warranty_expire']) ? date('d/m/Y', strtotime($a['warranty_expire'])) : ''
            ]);
        }

        fclose($out);
        exit;
    }
}

