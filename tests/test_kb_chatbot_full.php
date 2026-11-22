<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/autoload.php';
$config = require __DIR__ . '/../app/Config/app.php';
require_once __DIR__ . '/../app/Helpers.php';

use App\Core\Auth;
use App\Core\Session;
use App\Models\User;
use App\Services\AIService;
use App\Controllers\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;

echo "=======================================================\n";
echo " TEST SUITE: KB AI CHATBOT POPUP & KNOWLEDGE ASSISTANT\n";
echo "=======================================================\n\n";

$passCount = 0;
function assertPass(bool $condition, string $msg): void {
    global $passCount;
    if (!$condition) {
        echo " [FAIL] {$msg}\n";
        exit(1);
    }
    echo " [PASS] {$msg}\n";
    $passCount++;
}

Session::start();

// 1. Authenticate user
$userModel = new User();
$user = $userModel->findById(1);
$roles = $userModel->getRoles(1);
Auth::login($user, $roles);
assertPass(Auth::check(), "User authenticated for KB Chatbot API test");

// 2. Test Router registration
$req = new Request();
$res = new Response();
$router = new Router($req, $res);
require __DIR__ . '/../app/routes.php';

$routerRef = new ReflectionClass($router);
$routesProp = $routerRef->getProperty('routes');
$routesProp->setAccessible(true);
$allRoutes = $routesProp->getValue($router);

assertPass(isset($allRoutes['POST']['/api/ai/kb-chat']), "Route POST /api/ai/kb-chat is registered in Router");

// 3. Test AIService answers domain questions
$ai = new AIService();

// 3a. VPN query
$vpnRes = $ai->answerKnowledgeQuery("Làm sao kết nối VPN làm việc từ xa?", 1);
assertPass(!empty($vpnRes['reply']), "VPN query returns non-empty reply");
assertPass(strpos($vpnRes['reply'], 'FortiClient') !== false || strpos($vpnRes['reply'], 'vpn.company.local') !== false, "VPN query contains technical VPN instructions");
assertPass(!empty($vpnRes['articles']), "VPN query finds relevant KB articles from DB");

// 3b. Password query
$passRes = $ai->answerKnowledgeQuery("Hướng dẫn đổi mật khẩu Windows", 1);
assertPass(strpos($passRes['reply'], 'Ctrl + Alt + Delete') !== false, "Password query contains Ctrl+Alt+Del steps");
assertPass(strpos($passRes['reply'], '8 ký tự') !== false, "Password query mentions password policy");

// 3c. Printer query
$printerRes = $ai->answerKnowledgeQuery("Máy in tầng 2 bị lỗi không in được", 1);
assertPass(strpos($printerRes['reply'], '192.168.1.200') !== false, "Printer query contains network printer IP");
assertPass(strpos($printerRes['reply'], 'spooler') !== false, "Printer query contains spooler restart fix");

// 3d. Outlook query
$outlookRes = $ai->answerKnowledgeQuery("Outlook báo Disconnected", 1);
assertPass(strpos($outlookRes['reply'], 'Work Offline') !== false, "Outlook query instructs toggling Work Offline");

// 3e. Disk cleanup query
$diskRes = $ai->answerKnowledgeQuery("Ổ C của tôi bị đầy báo màu đỏ", 1);
assertPass(strpos($diskRes['reply'], 'cleanmgr') !== false, "Disk query mentions cleanmgr tool");

// 3f. System Guide query
$systemRes = $ai->answerKnowledgeQuery("Hướng dẫn tạo ticket hỗ trợ kỹ thuật", 1);
assertPass(strpos($systemRes['reply'], 'Phiếu Hỗ Trợ') !== false, "System query instructs how to create ticket");
assertPass(!empty($systemRes['suggested_actions']), "System query returns quick action links");

// 4. Test View Components
$popupFile = dirname(__DIR__) . '/app/Views/kb/chatbot_popup.php';
assertPass(file_exists($popupFile), "app/Views/kb/chatbot_popup.php exists");

$popupContent = file_get_contents($popupFile);
assertPass(strpos($popupContent, 'kbChatbotContainer') !== false, "Widget contains container #kbChatbotContainer");
assertPass(strpos($popupContent, 'kbChatbotToggleBtn') !== false, "Widget contains toggle button #kbChatbotToggleBtn");
assertPass(strpos($popupContent, 'kbChatbotWindow') !== false, "Widget contains popup window #kbChatbotWindow");
assertPass(strpos($popupContent, 'bottom: 24px') !== false && strpos($popupContent, 'left: 24px') !== false, "Widget CSS strictly pins to bottom-left corner (bottom: 24px; left: 24px)");
assertPass(strpos($popupContent, 'kb-prompt-btn') !== false, "Widget includes quick question suggestion chips");
assertPass(strpos($popupContent, '/api/ai/kb-chat') !== false, "Widget JavaScript sends fetch requests to /api/ai/kb-chat");

// 5. Test Integration in KB views
$kbIndexContent = file_get_contents(dirname(__DIR__) . '/app/Views/kb/index.php');
assertPass(strpos($kbIndexContent, 'chatbot_popup.php') !== false, "kb/index.php includes chatbot_popup.php");

$kbShowContent = file_get_contents(dirname(__DIR__) . '/app/Views/kb/show.php');
assertPass(strpos($kbShowContent, 'chatbot_popup.php') !== false, "kb/show.php includes chatbot_popup.php");

echo "\n=======================================================\n";
echo " TOTAL TESTS: {$passCount} | PASSED: {$passCount} | FAILED: 0\n";
echo "=======================================================\n";
echo " ALL KB AI CHATBOT TESTS PASSED 100%!\n";
