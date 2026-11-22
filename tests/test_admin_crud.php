<?php
require_once __DIR__ . '/../app/autoload.php';
require_once __DIR__ . '/../app/Helpers.php';
\App\Core\Session::start();
\App\Core\Language::load();

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Controllers\AdminController;
use App\Models\User;

$db = Database::getConnection();

// Login as Admin
$userModel = new User();
$admin = $userModel->findById(1);
Auth::login($admin, ['ADMIN']);

$controller = new AdminController();
class MockResponse extends Response {
    public function redirect(string $url): void {
        // no-op in CLI tests
    }
}
$response = new MockResponse();

// Cleanup any leftover test data
$db->exec("DELETE FROM users WHERE username = 'test_crud_user'");
$db->exec("DELETE FROM ticket_services WHERE code LIKE 'SRV_TEST_%'");
$db->exec("DELETE FROM ticket_categories WHERE code LIKE 'CAT_TEST_%'");
$db->exec("DELETE FROM it_group_members WHERE group_id IN (SELECT id FROM it_groups WHERE code LIKE 'GRP_TEST_%')");
$db->exec("DELETE FROM it_groups WHERE code LIKE 'GRP_TEST_%'");
$db->exec("DELETE FROM sla_configs WHERE name LIKE 'SLA Test CRUD%'");

echo "=======================================================\n";
echo " TESTING ADMIN CRUD (CREATE, READ, UPDATE, DELETE)\n";
echo "=======================================================\n\n";

$pass = 0;
$fail = 0;

function assertTest(bool $condition, string $msg, &$pass, &$fail) {
    if ($condition) {
        echo " [PASS] $msg\n";
        $pass++;
    } else {
        echo " [FAIL] $msg\n";
        $fail++;
    }
}

// 1. TEST USERS CRUD
echo "--- 1. Testing Users CRUD ---\n";
$_POST = [
    'username'   => 'test_crud_user',
    'email'      => 'test_crud_user@itdesk.local',
    'full_name'  => 'Test CRUD User',
    'password'   => 'password123',
    'department' => 'Kế toán',
    'phone'      => '0912345678',
    'roles'      => ['USER', 'STAFF']
];
$request = new Request();
$controller->createUser($request, $response);

$createdUser = $userModel->findByUsername('test_crud_user');
assertTest(!empty($createdUser), "Created user 'test_crud_user' in database", $pass, $fail);

if ($createdUser) {
    $uid = (int)$createdUser['id'];
    // Test update
    $_POST = [
        'full_name'  => 'Test CRUD User (Updated)',
        'email'      => 'test_crud_user_upd@itdesk.local',
        'department' => 'Kiểm toán',
        'phone'      => '0988888888',
        'roles'      => ['STAFF']
    ];
    $request = new Request();
    $controller->updateUser($request, $response, (string)$uid);
    $updUser = $userModel->findById($uid);
    assertTest($updUser['full_name'] === 'Test CRUD User (Updated)', "Updated user full_name successfully", $pass, $fail);

    // Test toggle status
    $controller->toggleUserStatus($request, $response, (string)$uid);
    $lockedUser = $userModel->findById($uid);
    assertTest((int)$lockedUser['is_active'] === 0, "Locked user (is_active = 0) successfully", $pass, $fail);

    // Test delete user
    $controller->deleteUser($request, $response, (string)$uid);
    $deletedUser = $userModel->findById($uid);
    assertTest(empty($deletedUser), "Deleted user permanently successfully", $pass, $fail);
}

// 2. TEST CATEGORIES & SERVICES CRUD
echo "\n--- 2. Testing Categories & Services CRUD ---\n";
$_POST = [
    'name'        => 'Test Danh Mục CRUD',
    'code'        => 'CAT_TEST_CRUD',
    'description' => 'Mô tả danh mục test'
];
$request = new Request();
$controller->createCategory($request, $response);

$stmtCat = $db->query("SELECT * FROM ticket_categories WHERE code = 'CAT_TEST_CRUD' LIMIT 1");
$cat = $stmtCat->fetch();
assertTest(!empty($cat), "Created category 'CAT_TEST_CRUD'", $pass, $fail);

if ($cat) {
    $catId = (int)$cat['id'];
    // Update category
    $_POST = [
        'name'        => 'Test Danh Mục CRUD (Sửa)',
        'code'        => 'CAT_TEST_CRUD_UPD',
        'description' => 'Mô tả sửa'
    ];
    $request = new Request();
    $controller->updateCategory($request, $response, (string)$catId);
    $catUpd = $db->query("SELECT * FROM ticket_categories WHERE id = $catId")->fetch();
    assertTest($catUpd['name'] === 'Test Danh Mục CRUD (Sửa)', "Updated category name", $pass, $fail);

    // Create Service under Category
    $_POST = [
        'category_id' => $catId,
        'name'        => 'Test Dịch Vụ Con',
        'code'        => 'SRV_TEST_CRUD',
        'description' => 'Dịch vụ con test'
    ];
    $request = new Request();
    $controller->createService($request, $response);
    $srv = $db->query("SELECT * FROM ticket_services WHERE code = 'SRV_TEST_CRUD' LIMIT 1")->fetch();
    assertTest(!empty($srv), "Created service 'SRV_TEST_CRUD'", $pass, $fail);

    if ($srv) {
        $srvId = (int)$srv['id'];
        // Update Service
        $_POST = [
            'category_id' => $catId,
            'name'        => 'Test Dịch Vụ Con (Sửa)',
            'code'        => 'SRV_TEST_CRUD_UPD',
            'description' => 'Mô tả dịch vụ sửa'
        ];
        $request = new Request();
        $controller->updateService($request, $response, (string)$srvId);
        $srvUpd = $db->query("SELECT * FROM ticket_services WHERE id = $srvId")->fetch();
        assertTest($srvUpd['name'] === 'Test Dịch Vụ Con (Sửa)', "Updated service name", $pass, $fail);

        // Delete Service
        $controller->deleteService($request, $response, (string)$srvId);
        $srvDel = $db->query("SELECT * FROM ticket_services WHERE id = $srvId")->fetch();
        assertTest(empty($srvDel), "Deleted service successfully", $pass, $fail);
    }

    // Delete Category
    $controller->deleteCategory($request, $response, (string)$catId);
    $catDel = $db->query("SELECT * FROM ticket_categories WHERE id = $catId")->fetch();
    assertTest(empty($catDel), "Deleted category successfully", $pass, $fail);
}

// 3. TEST IT GROUPS CRUD
echo "\n--- 3. Testing IT Groups CRUD ---\n";
$_POST = [
    'name'        => 'Nhóm Test CRUD IT',
    'code'        => 'GRP_TEST_CRUD',
    'description' => 'Mô tả nhóm test IT'
];
$request = new Request();
$controller->createGroup($request, $response);
$grp = $db->query("SELECT * FROM it_groups WHERE code = 'GRP_TEST_CRUD' LIMIT 1")->fetch();
assertTest(!empty($grp), "Created IT group 'GRP_TEST_CRUD'", $pass, $fail);

if ($grp) {
    $grpId = (int)$grp['id'];
    // Update group
    $_POST = [
        'name'        => 'Nhóm Test CRUD IT (Sửa)',
        'code'        => 'GRP_TEST_CRUD_UPD',
        'description' => 'Mô tả sửa'
    ];
    $request = new Request();
    $controller->updateGroup($request, $response, (string)$grpId);
    $grpUpd = $db->query("SELECT * FROM it_groups WHERE id = $grpId")->fetch();
    assertTest(($grpUpd['name'] ?? '') === 'Nhóm Test CRUD IT (Sửa)', "Updated IT group name", $pass, $fail);

    // Delete group
    $controller->deleteGroup($request, $response, (string)$grpId);
    $grpDel = $db->query("SELECT * FROM it_groups WHERE id = $grpId")->fetch();
    assertTest(empty($grpDel), "Deleted IT group successfully", $pass, $fail);
}

// 4. TEST SLA CONFIGS CRUD
echo "\n--- 4. Testing SLA Configs CRUD ---\n";
$_POST = [
    'priority_id'                => 1, // Normal
    'name'                       => 'SLA Test CRUD 12 Giờ',
    'resolution_time'            => 12,
    'resolution_unit'            => 'HOUR',
    'warning_threshold_percent'  => 60,
    'critical_threshold_percent' => 85
];
$request = new Request();
$controller->createSla($request, $response);
$sla = $db->query("SELECT * FROM sla_configs WHERE name = 'SLA Test CRUD 12 Giờ' LIMIT 1")->fetch();
assertTest(!empty($sla), "Created SLA config 'SLA Test CRUD 12 Giờ'", $pass, $fail);

if ($sla) {
    $slaId = (int)$sla['id'];
    // Update SLA
    $_POST = [
        'name'                       => 'SLA Test CRUD 16 Giờ',
        'resolution_time'            => 16,
        'resolution_unit'            => 'HOUR',
        'warning_threshold_percent'  => 65,
        'critical_threshold_percent' => 90,
        'is_active'                  => 1
    ];
    $request = new Request();
    $controller->updateSla($request, $response, (string)$slaId);
    $slaUpd = $db->query("SELECT * FROM sla_configs WHERE id = $slaId")->fetch();
    assertTest((int)$slaUpd['resolution_time'] === 16, "Updated SLA resolution_time to 16", $pass, $fail);

    // Delete SLA
    $controller->deleteSla($request, $response, (string)$slaId);
    $slaDel = $db->query("SELECT * FROM sla_configs WHERE id = $slaId")->fetch();
    assertTest(empty($slaDel), "Deleted SLA config successfully", $pass, $fail);
}

echo "\n=======================================================\n";
echo " SUMMARY: TOTAL TESTS: " . ($pass + $fail) . " | PASSED: $pass | FAILED: $fail\n";
echo "=======================================================\n";

if ($fail === 0) {
    echo " ALL ADMIN CRUD TESTS PASSED 100%!\n";
} else {
    exit(1);
}
