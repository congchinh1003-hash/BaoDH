<?php

namespace App\Services;

use App\Models\Category;
use App\Models\ITGroup;
use App\Models\KnowledgeBase;
use App\Models\Priority;

class AIService
{
    /**
     * Smart Triage: Automatically categorize, prioritize, and suggest IT group (Rule 58)
     */
    public function smartTriage(string $title, string $description): array
    {
        $text = mb_strtolower($title . ' ' . $description, 'UTF-8');

        // 1. DETERMINE CATEGORY & SERVICE
        $categoryModel = new Category();
        $allCategories = $categoryModel->getAllActive();
        
        $detectedCategoryId = null;
        $categoryConfidence = 70;
        $reasoningCategory = "Phát hiện từ khóa tiêu chuẩn";

        // Keyword rules for categories
        if (preg_match('/(wifi|mạng|network|lan|vpn|internet|ping|router|switch|dns|ip|kết nối|mất mạng)/i', $text)) {
            $cat = $this->findCategoryByCodeOrKeyword($allCategories, ['NET', 'NETWORK', 'MẠNG']);
            if ($cat) {
                $detectedCategoryId = $cat['id'];
                $reasoningCategory = "Phát hiện từ khóa liên quan đến Hạ tầng Mạng, WiFi, VPN";
            }
        } elseif (preg_match('/(máy in|màn hình|bàn phím|chuột|laptop|pc|máy tính|phần cứng|ram|ổ cứng|ssd|sạc|pin|loa)/i', $text)) {
            $cat = $this->findCategoryByCodeOrKeyword($allCategories, ['HDW', 'HARDWARE', 'PHẦN CỨNG']);
            if ($cat) {
                $detectedCategoryId = $cat['id'];
                $reasoningCategory = "Phát hiện từ khóa liên quan đến Thiết bị Phần cứng & Thiết bị ngoại vi";
            }
        } elseif (preg_match('/(mật khẩu|tài khoản|password|đăng nhập|login|domain|ad|reset pass|khóa tài khoản)/i', $text)) {
            $cat = $this->findCategoryByCodeOrKeyword($allCategories, ['ACC', 'ACCOUNT', 'TÀI KHOẢN']);
            if ($cat) {
                $detectedCategoryId = $cat['id'];
                $reasoningCategory = "Phát hiện từ khóa liên quan đến Tài khoản & Đăng nhập";
            }
        } elseif (preg_match('/(erp|crm|hrm|outlook|email|mail|excel|word|phần mềm|cài đặt|license|bản quyền)/i', $text)) {
            $cat = $this->findCategoryByCodeOrKeyword($allCategories, ['APP', 'APPLICATION', 'PHẦN MỀM', 'SOFTWARE']);
            if ($cat) {
                $detectedCategoryId = $cat['id'];
                $reasoningCategory = "Phát hiện từ khóa liên quan đến Phần mềm Ứng dụng & Email";
            }
        }

        // Default to first category if none matched
        if (!$detectedCategoryId && !empty($allCategories)) {
            $detectedCategoryId = $allCategories[0]['id'];
            $categoryConfidence = 50;
        }

        // 2. DETERMINE PRIORITY
        $priorityModel = new Priority();
        $allPriorities = $priorityModel->getAllActive();

        $detectedPriorityId = null;
        $priorityCode = 'NORMAL';
        $reasoningPriority = "Mức độ ưu tiên tiêu chuẩn cho yêu cầu cá nhân";

        // Check for URGENT criteria
        if (preg_match('/(cháy|bốc khói|sập mạng|tê liệt|toàn công ty|toàn phòng|ngừng trệ|hỏng toàn bộ|khẩn cấp|nghiêm trọng|mất dữ liệu|ransomware|virus|tấn công)/i', $text)) {
            $priorityCode = 'URGENT';
            $reasoningPriority = "Phát hiện sự cố nghiêm trọng có nguy cơ ảnh hưởng trên diện rộng hoặc tê liệt hệ thống";
        } elseif (preg_match('/(gấp|ngay|không làm việc được|sếp cần|họp khẩn|lỗi không in được hợp đồng|hỏng máy giám đốc)/i', $text)) {
            $priorityCode = 'HIGH';
            $reasoningPriority = "Phát hiện tính cấp thiết ảnh hưởng trực tiếp đến tiến độ công việc quan trọng";
        }

        foreach ($allPriorities as $p) {
            if (strtoupper($p['code']) === $priorityCode) {
                $detectedPriorityId = $p['id'];
                break;
            }
        }
        if (!$detectedPriorityId && !empty($allPriorities)) {
            $detectedPriorityId = $allPriorities[0]['id'];
        }

        // 3. SEARCH MATCHING KNOWLEDGE BASE ARTICLES
        $kbModel = new KnowledgeBase();
        $suggestedArticles = $kbModel->search($title . ' ' . $description, 3);

        $categoryInfo = null;
        if ($detectedCategoryId) {
            foreach ($allCategories as $c) {
                if ((int)$c['id'] === (int)$detectedCategoryId) {
                    $categoryInfo = $c;
                    break;
                }
            }
        }

        $priorityInfo = null;
        if ($detectedPriorityId) {
            foreach ($allPriorities as $p) {
                if ((int)$p['id'] === (int)$detectedPriorityId) {
                    $priorityInfo = $p;
                    break;
                }
            }
        }

        return [
            'success'            => true,
            'category_id'        => $detectedCategoryId,
            'category'           => $categoryInfo,
            'priority_id'        => $detectedPriorityId,
            'priority_code'      => $priorityCode,
            'priority'           => $priorityInfo,
            'reasoning'          => $reasoningCategory . '. ' . $reasoningPriority . '.',
            'reasoning_category' => $reasoningCategory,
            'reasoning_priority' => $reasoningPriority,
            'confidence'         => $categoryConfidence,
            'suggested_articles' => $suggestedArticles,
            'kb_suggestions'     => $suggestedArticles
        ];
    }

    /**
     * AI Copilot: Summarize ticket history and exchanges (Rule 58)
     */
    public function summarizeTicket(array $ticket, array $comments): string
    {
        $summary = "Ticket #{$ticket['ticket_code']} do {$ticket['requester_name']} khởi tạo lúc " . date('d/m/Y H:i', strtotime($ticket['created_at'])) . ".\n";
        $summary .= "• Nội dung ban đầu: \"" . mb_substr($ticket['description'], 0, 150) . "...\"\n";
        $summary .= "• Phân loại: {$ticket['category_name']} | Mức độ: {$ticket['priority_name']} | Trạng thái hiện tại: {$ticket['status']}.\n";

        if (!empty($ticket['assignee_name'])) {
            $summary .= "• Nhân viên phụ trách: {$ticket['assignee_name']}.\n";
        } else {
            $summary .= "• Hiện tại: Đang chờ phân công trong hàng đợi.\n";
        }

        $commentCount = count($comments);
        if ($commentCount > 0) {
            $summary .= "• Tiến trình trao đổi: Đã có {$commentCount} lượt phản hồi/ghi chú giữa người dùng và kỹ thuật viên.";
        } else {
            $summary .= "• Tiến trình: Chưa có phản hồi nào.";
        }

        return $summary;
    }

    /**
     * AI Copilot: Suggest technical resolution steps for IT Staff
     */
    public function suggestSolution(array $ticket): array
    {
        $catName = mb_strtolower($ticket['category_name'] ?? '', 'UTF-8');
        $title = mb_strtolower($ticket['title'] ?? '', 'UTF-8');

        $steps = [];

        if (str_contains($catName, 'mạng') || str_contains($catName, 'network') || str_contains($title, 'wifi') || str_contains($title, 'vpn')) {
            $steps = [
                "Bước 1: Kiểm tra trạng thái đèn cổng LAN hoặc kết nối sóng WiFi tại máy người dùng.",
                "Bước 2: Mở CMD, chạy lệnh `ping 8.8.8.8` và `ping 192.168.1.1` để xác định mất mạng nội bộ hay mạng Internet.",
                "Bước 3: Chạy `ipconfig /release` và `ipconfig /renew`, sau đó thử `ipconfig /flushdns`.",
                "Bước 4: Nếu lỗi VPN: Kiểm tra tài khoản người dùng có bị khóa trên FortiGate / Domain AD không."
            ];
        } elseif (str_contains($catName, 'máy in') || str_contains($title, 'in') || str_contains($title, 'print')) {
            $steps = [
                "Bước 1: Kiểm tra máy in có đang báo đèn vàng kẹt giấy hoặc hết mực hay không.",
                "Bước 2: Kiểm tra hàng đợi in (Print Queue) trên máy tính người dùng xem có lệnh in nào bị treo (Spooler error).",
                "Bước 3: Khởi động lại dịch vụ Print Spooler bằng lệnh: `net stop spooler && net start spooler`.",
                "Bước 4: Kiểm tra địa chỉ IP máy in mạng xem có ping được từ máy người dùng không."
            ];
        } elseif (str_contains($catName, 'tài khoản') || str_contains($title, 'mật khẩu') || str_contains($title, 'password')) {
            $steps = [
                "Bước 1: Mở Active Directory Users and Computers trên máy chủ Domain Controller.",
                "Bước 2: Tìm tài khoản người dùng, kiểm tra xem cờ 'Account is locked out' có đang bị bật do gõ sai mật khẩu nhiều lần không.",
                "Bước 3: Bấm chuột phải chọn 'Reset Password', đặt mật khẩu tạm thời và tích chọn 'User must change password at next logon'.",
                "Bước 4: Hướng dẫn người dùng đăng nhập lại và đổi sang mật khẩu cá nhân mới."
            ];
        } else {
            $steps = [
                "Bước 1: Liên hệ xác nhận trực tiếp với người dùng qua số điện thoại nội bộ hoặc nhắn tin qua Service Desk.",
                "Bước 2: Kết nối UltraViewer / AnyDesk để quan sát trực tiếp hiện tượng lỗi trên máy trạm.",
                "Bước 3: Kiểm tra log hệ thống trong Event Viewer (Application & System Log).",
                "Bước 4: Tiến hành xử lý và cập nhật nội dung giải pháp vào phần Resolution trước khi chuyển trạng thái sang RESOLVED."
            ];
        }

        return $steps;
    }

    /**
     * AI Copilot: Draft a professional and polite response for IT staff
     */
    public function draftResponse(array $ticket, array $sender): string
    {
        $requesterName = $ticket['requester_name'] ?? 'Anh/Chị';
        $ticketCode = $ticket['ticket_code'];
        $staffName = $sender['full_name'] ?? 'Bộ phận IT Support';

        return "Chào {$requesterName},\n\nBộ phận IT Support đã tiếp nhận yêu cầu #{$ticketCode} của bạn về sự cố: \"{$ticket['title']}\".\n\nKỹ thuật viên đang tiến hành kiểm tra hệ thống và sẽ hỗ trợ xử lý dứt điểm cho bạn trong thời gian sớm nhất theo cam kết SLA. Nếu bạn đang ở tại bàn làm việc, xin vui lòng chuẩn bị máy hoặc cho phép chúng tôi kết nối hỗ trợ từ xa khi cần thiết.\n\nTrân trọng cảm ơn bạn!\n---\n{$staffName} - IT Service Desk";
    }

    private function findCategoryByCodeOrKeyword(array $categories, array $keywords): ?array
    {
        foreach ($categories as $cat) {
            $code = strtoupper($cat['code'] ?? '');
            $name = mb_strtoupper($cat['name'] ?? '', 'UTF-8');
            foreach ($keywords as $kw) {
                if (str_contains($code, $kw) || str_contains($name, $kw)) {
                    return $cat;
                }
            }
        }
        return null;
    }

    /**
     * AI Knowledge Chatbot Query Engine
     * Provides comprehensive IT technical support, troubleshooting steps, and system guidance.
     */
    public function answerKnowledgeQuery(string $question, int $userId = 0): array
    {
        $q = trim($question);
        $norm = mb_strtolower($q, 'UTF-8');

        // 1. Search database Knowledge Base articles
        $kbModel = new KnowledgeBase();
        $matchedArticles = $kbModel->search($q, 4);

        $reply = "";
        $suggestedActions = [];

        // 2. Conversational & Chit-chat Patterns (Greetings, Identity, Thanks, Farewell)
        if (preg_match('/^(xin chào|chào|chào bạn|chào em|chào anh|chào chị|chào bot|hello|hi|hey|alo|ê|hola|good morning|good afternoon|good evening)\b/iu', $norm) || in_array($norm, ['hi', 'hello', 'alo', 'hey', 'chao', 'xin chao'])) {
            $reply = "👋 **Xin chào bạn!** Tôi là **Trợ lý AI Hỗ trợ Kỹ thuật & Tri thức** của hệ thống TaskFlow Service Desk.\n\n"
                   . "Tôi luôn sẵn sàng hỗ trợ bạn 24/7 với các vấn đề kỹ thuật thường gặp:\n\n"
                   . "* 🔐 **Mật khẩu & Tài khoản:** Quên mật khẩu, đổi mật khẩu Windows/Domain, mở khóa tài khoản.\n"
                   . "* 🌐 **Mạng & VPN:** Mất kết nối Internet/WiFi, cấu hình VPN làm việc từ xa.\n"
                   . "* 🖨️ **Máy in văn phòng:** Hướng dẫn kết nối máy in mạng, xử lý lệnh in bị treo.\n"
                   . "* ✉️ **Email & Outlook:** Lỗi Outlook Disconnected, hòm thư đầy, không gửi/nhận được thư.\n"
                   . "* 💾 **Ổ đĩa & Phần mềm:** Dọn dẹp ổ C báo đỏ, giải phóng dung lượng rác, cài đặt phần mềm.\n"
                   . "* 🎫 **Gửi phiếu yêu cầu:** Hướng dẫn tạo ticket gửi đội kỹ thuật IT hỗ trợ tại chỗ.\n\n"
                   . "Bạn đang gặp khó khăn hay cần tôi hỗ trợ vấn đề gì hôm nay?";
            $suggestedActions[] = ['label' => '🔐 Đổi mật khẩu', 'url' => '/kb?search=mật+khẩu'];
            $suggestedActions[] = ['label' => '🌐 Cấu hình VPN', 'url' => '/kb?search=vpn'];
            $suggestedActions[] = ['label' => '🖨️ Kết nối máy in', 'url' => '/kb?search=máy+in'];
            $suggestedActions[] = ['label' => '🎫 Tạo phiếu hỗ trợ IT', 'url' => '/tickets/create'];
            $matchedArticles = [];

        } elseif (preg_match('/(bạn là ai|bạn tên gì|chức năng của bạn|bạn làm được gì|hướng dẫn sử dụng bot|who are you|help|trợ giúp|làm gì được)/iu', $norm)) {
            $reply = "🤖 **Tôi là Trợ lý AI Thông Minh (Smart Copilot)** được tích hợp trong hệ thống IT Service Desk & TaskFlow ALM.\n\n"
                   . "**Nhiệm vụ chính của tôi:**\n"
                   . "1. **Tư vấn & Hướng dẫn kỹ thuật:** Cung cấp giải pháp từng bước để bạn tự khắc phục nhanh các sự cố máy tính, mạng, máy in, email mà không cần chờ kỹ thuật viên.\n"
                   . "2. **Tra cứu Cơ Sở Tri Thức (KEDB):** Tìm kiếm tức thì trong kho tài liệu chuẩn hóa và cẩm nang kỹ thuật của công ty.\n"
                   . "3. **Điều hướng tiện ích:** Giúp bạn truy cập nhanh Danh mục dịch vụ (`/catalog`), Báo hỏng thiết bị (`/tickets/create`), hoặc Tra cứu tài sản cá nhân (`/my-assets`).\n"
                   . "4. **Hỗ trợ quy trình Agile:** Hướng dẫn sử dụng bảng Kanban, quản lý công việc và báo cáo lỗi dự án.\n\n"
                   . "Hãy nhập trực tiếp triệu chứng hoặc từ khóa bạn quan tâm (ví dụ: *\"quên pass\"*, *\"lỗi máy in\"*, *\"ổ C đầy\"*) để tôi trợ giúp ngay nhé!";
            $suggestedActions[] = ['label' => 'Cơ Sở Tri Thức (KB)', 'url' => '/kb'];
            $suggestedActions[] = ['label' => 'Danh Mục Dịch Vụ', 'url' => '/catalog'];
            $matchedArticles = [];

        } elseif (preg_match('/^(cảm ơn|cám ơn|thank|thanks|thank you|tks|ty|ok cảm ơn|cảm ơn bạn|tks bot)\b/iu', $norm)) {
            $reply = "😊 **Rất vui được hỗ trợ bạn!**\n\n"
                   . "Nếu trong quá trình làm việc bạn gặp bất kỳ sự cố kỹ thuật nào khác, đừng ngần ngại nhắn cho tôi bất cứ lúc nào nhé.\n\n"
                   . "Chúc bạn một ngày làm việc thật hiệu quả và tràn đầy năng lượng! ✨";
            $suggestedActions[] = ['label' => 'Cơ Sở Tri Thức', 'url' => '/kb'];
            $suggestedActions[] = ['label' => 'Dashboard', 'url' => '/dashboard'];
            $matchedArticles = [];

        } elseif (preg_match('/^(tạm biệt|bye|goodbye|bye bye|hẹn gặp lại|see you)\b/iu', $norm)) {
            $reply = "👋 **Tạm biệt bạn!**\n\n"
                   . "Chúc bạn có một ngày làm việc tuyệt vời! Khi nào cần hỗ trợ kỹ thuật, tôi luôn ở đây để giúp bạn. Hẹn gặp lại! 🚀";
            $matchedArticles = [];

        } elseif (preg_match('/(giỏi quá|thông minh|tuyệt vời|good|great|hay quá|chuẩn|cảm ơn nhé)/iu', $norm)) {
            $reply = "🌟 **Cảm ơn bạn rất nhiều vì lời động viên!**\n\n"
                   . "Tôi luôn sẵn sàng đồng hành hỗ trợ bạn giải quyết các vấn đề kỹ thuật nhanh nhất có thể. Đừng ngần ngại đặt câu hỏi nếu bạn cần thêm thông tin nhé! 😊";
            $matchedArticles = [];

        // 3. Domain & System Pattern Matching
        } elseif (preg_match('/(đổi mật khẩu|mật khẩu|password|\bpass\b|reset pass|quên mật khẩu|quên pass|đổi pass|mất pass|khóa tài khoản)/iu', $norm)) {
            $reply = "### 🔐 Hướng dẫn Đổi & Khắc phục sự cố Mật Khẩu\n\n"
                   . "Để tự đổi mật khẩu tài khoản máy tính Windows và mạng Domain công ty, bạn thực hiện như sau:\n\n"
                   . "1. **Bấm tổ hợp phím:** Nhấn `Ctrl + Alt + Delete` trên bàn phím máy tính.\n"
                   . "2. **Chọn chức năng:** Nhấp vào dòng **\"Change a password\" (Đổi mật khẩu)**.\n"
                   . "3. **Nhập thông tin:**\n"
                   . "   - *Old password*: Nhập mật khẩu hiện tại đang dùng.\n"
                   . "   - *New password*: Nhập mật khẩu mới thỏa mãn chính sách an toàn.\n"
                   . "   - *Confirm password*: Nhập lại mật khẩu mới để xác nhận.\n"
                   . "4. **Quy định chính sách mật khẩu (Password Policy):**\n"
                   . "   - Độ dài tối thiểu **8 ký tự**.\n"
                   . "   - Bắt buộc chứa đủ 4 nhóm: Chữ hoa (A-Z), chữ thường (a-z), chữ số (0-9) và ký tự đặc biệt (`!@#$%^&*`).\n"
                   . "   - Không được trùng lặp với 3 mật khẩu đã sử dụng gần nhất.\n\n"
                   . "⚠️ *Nếu tài khoản bị khóa do nhập sai quá 5 lần hoặc quên hoàn toàn mật khẩu, bạn hãy gửi yêu cầu mở khóa qua Danh Mục Dịch Vụ.*";
            $suggestedActions[] = ['label' => 'Yêu cầu Cấp lại / Mở khóa Mật Khẩu', 'url' => '/catalog'];

        } elseif (preg_match('/(vpn|kết nối từ xa|remote|anyconnect|forticlient|openvpn)/i', $norm)) {
            $reply = "### 🌐 Hướng dẫn Kết nối VPN Làm Việc Từ Xa\n\n"
                   . "Để truy cập mạng nội bộ công ty từ nhà hoặc khi đi công tác qua VPN:\n\n"
                   . "1. **Khởi động ứng dụng:** Mở phần mềm **FortiClient VPN** (hoặc OpenVPN Client) trên máy tính.\n"
                   . "2. **Thông số cổng kết nối (Gateway):**\n"
                   . "   - **Remote Gateway:** `vpn.company.local`\n"
                   . "   - **Cổng kết nối (Port):** `10443`\n"
                   . "3. **Đăng nhập:** Nhập tên tài khoản (Username) và Mật khẩu Domain công ty được cấp.\n"
                   . "4. **Kết nối:** Bấm nút **Connect** và chờ thanh trạng thái đạt **100% (Connected)**.\n\n"
                   . "💡 *Sau khi kết nối thành công, bạn có thể truy cập ERP, kho dữ liệu NAS `\\\\nas.company.local` và các máy chủ nội bộ bình thường như đang ngồi tại văn phòng.*";
            $suggestedActions[] = ['label' => 'Xem danh mục bài viết VPN', 'url' => '/kb?search=vpn'];

        } elseif (preg_match('/(máy in|in ấn|không in được|kẹt giấy|spooler|in báo lỗi|mực in)/i', $norm)) {
            $reply = "### 🖨️ Hướng dẫn Kết nối Máy In Mạng & Xử lý Lỗi In\n\n"
                   . "Hệ thống máy in văn phòng công ty được bố trí theo các tầng:\n"
                   . "- **Tầng 2:** Canon LBP 2900 / HP LaserJet Pro (Đường dẫn: `\\\\192.168.1.200`)\n"
                   . "- **Tầng 3:** Ricoh Aficio Photocopy Đa Năng (Đường dẫn: `\\\\192.168.1.201`)\n\n"
                   . "**Cách kết nối máy in nhanh:**\n"
                   . "1. Nhấn tổ hợp phím `Windows + R` trên bàn phím để mở cửa sổ Run.\n"
                   . "2. Nhập `\\\\192.168.1.200` (hoặc `\\\\192.168.1.201`) rồi nhấn **Enter**.\n"
                   . "3. Nhấp chuột phải vào tên máy in mong muốn và chọn **Connect**.\n"
                   . "4. Chờ 10-15 giây để Windows tự động nạp Driver.\n\n"
                   . "**Khắc phục lệnh in bị treo (Print Spooler Error):**\n"
                   . "- Mở Command Prompt với quyền Admin (`cmd` -> Run as Administrator).\n"
                   . "- Gõ lệnh: `net stop spooler && net start spooler` rồi nhấn Enter.";
            $suggestedActions[] = ['label' => 'Xem hướng dẫn máy in', 'url' => '/kb?search=máy+in'];

        } elseif (preg_match('/(outlook|email|mail|disconnected|không gửi được thư|hòm thư đầy|hết dung lượng)/i', $norm)) {
            $reply = "### ✉️ Khắc phục Lỗi Outlook Báo \"Disconnected\" / Không Gửi Được Mail\n\n"
                   . "Nếu thanh trạng thái Outlook hiển thị **Disconnected** hoặc thư bị đọng trong hộp Outbox:\n\n"
                   . "1. **Kiểm tra chế độ làm việc ngoại tuyến (Work Offline):**\n"
                   . "   - Trên Outlook, chọn tab **Send / Receive** trên thanh Ribbon.\n"
                   . "   - Nhìn vào nút **Work Offline**. Nếu nút này đang sáng màu, hãy nhấp vào nó để đưa Outlook trở lại trạng thái **Online**.\n"
                   . "2. **Khởi động lại kết nối:** Đóng hoàn toàn Outlook (kiểm tra Task Manager để chắc chắn tiến trình `OUTLOOK.EXE` đã đóng) sau đó mở lại.\n"
                   . "3. **Kiểm tra mạng Internet/VPN:** Nếu làm việc tại nhà, bắt buộc phải bật kết nối VPN trước khi mở Outlook kết nối Exchange nội bộ.\n"
                   . "4. **Dọn dẹp hòm thư:** Nếu hộp thư vượt quá 95% dung lượng hạn mức, hệ thống sẽ chặn gửi thư mới. Hãy vào mục *Deleted Items* và chọn *Empty Folder*.";
            $suggestedActions[] = ['label' => 'Xem hướng dẫn Outlook', 'url' => '/kb?search=outlook'];

        } elseif (preg_match('/(ổ c.*đầy|ổ đĩa.*đỏ|ổ đĩa.*đầy|dọn dẹp|giải phóng|dung lượng đầy|cleanmgr|temp|xóa rác)/i', $norm)) {
            $reply = "### 💾 Hướng dẫn Dọn Dẹp Ổ Đĩa C Báo Đỏ (Hết Dung Lượng)\n\n"
                   . "Khi ổ C bị đầy, máy tính sẽ chạy chậm và không mở được các file lớn. Bạn có thể tự giải phóng 5GB - 20GB nhanh chóng:\n\n"
                   . "1. **Sử dụng Disk Cleanup:**\n"
                   . "   - Nhấn `Windows + R`, nhập `cleanmgr` và bấm **Enter**.\n"
                   . "   - Chọn ổ đĩa `C:` và bấm OK.\n"
                   . "   - Tích chọn: *Temporary files*, *Recycle Bin*, *Thumbnails*, *Windows Update Cleanup* > bấm **OK** để xóa.\n"
                   . "2. **Xóa tệp rác tạm hệ thống:**\n"
                   . "   - Nhấn `Windows + R`, nhập `%temp%` và bấm **Enter**.\n"
                   . "   - Nhấn `Ctrl + A` để chọn tất cả và nhấn `Shift + Delete` (bỏ qua 'Skip' các tệp đang chạy).\n"
                   . "3. **Chuyển dữ liệu cá nhân:** Không lưu các tệp video, bộ cài đặt dung lượng lớn ở màn hình Desktop hoặc mục Downloads; hãy chuyển sang ổ đĩa dữ liệu `D:` hoặc OneDrive.";
            $suggestedActions[] = ['label' => 'Xem bài viết dọn ổ đĩa C', 'url' => '/kb?search=ổ+đĩa'];

        } elseif (preg_match('/(mạng|wifi|mất mạng|internet|không vào được mạng|lan|rớt mạng|chậm)/i', $norm)) {
            $reply = "### 📶 Hướng dẫn Khắc phục Sự cố Mạng & Kết nối WiFi\n\n"
                   . "Khi máy tính bị mất mạng hoặc biểu tượng mạng báo chấm than vàng/quả cầu tròn:\n\n"
                   . "1. **Kiểm tra phần cứng & Cáp mạng:**\n"
                   . "   - Rút đầu cáp mạng LAN ra và cắm lại chặt vào cổng mạng máy tính (đảm bảo 2 đèn tín hiệu xanh/vàng nhấp nháy).\n"
                   . "   - Nếu dùng WiFi: Tắt WiFi trên máy tính, chờ 5 giây rồi bật lại và chọn mạng `Company_Office_5G`.\n"
                   . "2. **Làm mới địa chỉ IP & Xóa bộ nhớ đệm DNS:**\n"
                   . "   - Mở cửa sổ **Command Prompt (cmd)**.\n"
                   . "   - Nhập lần lượt các lệnh sau:\n"
                   . "     ```shell\n"
                   . "     ipconfig /flushdns\n"
                   . "     ipconfig /release\n"
                   . "     ipconfig /renew\n"
                   . "     ```\n"
                   . "3. **Kiểm tra kết nối Internet:**\n"
                   . "   - Gõ `ping 8.8.8.8` để kiểm tra thông mạng quốc tế.\n"
                   . "   - Gõ `ping 192.168.1.1` để kiểm tra kết nối với Router công ty.";
            $suggestedActions[] = ['label' => 'Tạo phiếu báo mất mạng', 'url' => '/tickets/create'];

        } elseif (preg_match('/(tạo ticket|tạo phiếu|gửi yêu cầu|báo hỏng|nhờ it)/i', $norm)) {
            $reply = "### 🎫 Hướng dẫn Tạo Phiếu Yêu Cầu Hỗ Trợ Kỹ Thuật (Ticket)\n\n"
                   . "Khi gặp sự cố không thể tự khắc phục, bạn có thể tạo phiếu yêu cầu kỹ thuật trực tiếp trên hệ thống:\n\n"
                   . "1. **Cách 1 - Tạo từ Menu Phiếu Hỗ Trợ:**\n"
                   . "   - Vào menu **Phiếu Hỗ Trợ** trên thanh điều hướng bên trái.\n"
                   . "   - Bấm nút **\"Tạo Phiếu Mới\"** (hoặc truy cập `/tickets/create`).\n"
                   . "2. **Cách 2 - Đăng ký theo Danh Mục Dịch Vụ (Service Catalog):**\n"
                   . "   - Vào menu **Danh Mục Dịch Vụ** (`/catalog`) để chọn đúng loại yêu cầu (Cấp thiết bị, Cài đặt phần mềm, Mở quyền truy cập, v.v.).\n"
                   . "3. **Các thông tin cần cung cấp để được xử lý nhanh nhất:**\n"
                   . "   - **Tiêu đề:** Tóm tắt ngắn gọn sự cố (Ví dụ: *Không in được hóa đơn từ máy tính kế toán*).\n"
                   . "   - **Mức độ ưu tiên:** Khẩn cấp (nghiêm trọng ảnh hưởng nhiều người), Cao (ảnh hưởng tiến độ), hoặc Tiêu chuẩn.\n"
                   . "   - **Ảnh chụp màn hình lỗi:** Đính kèm file ảnh lỗi để IT chẩn đoán tức thì.";
            $suggestedActions[] = ['label' => 'Tạo Phiếu Hỗ Trợ Mới', 'url' => '/tickets/create'];
            $suggestedActions[] = ['label' => 'Xem Danh Mục Dịch Vụ', 'url' => '/catalog'];

        } elseif (preg_match('/(dự án|project|backlog|kanban|board|bug|testcase|quản lý công việc)/i', $norm)) {
            $reply = "### 📋 Hướng dẫn Sử dụng Phân hệ Dự Án & Quản Lý Công Việc Agile\n\n"
                   . "Hệ thống tích hợp quy trình chuẩn Jira Agile để quản lý toàn diện dự án phần mềm và công việc kỹ thuật:\n\n"
                   . "1. **Danh sách Dự Án (`/projects`):** Xem các dự án bạn đang tham gia, tiến độ hoàn thành và thành viên.\n"
                   . "2. **Bảng Kanban Board (`/projects/{id}/board`):**\n"
                   . "   - Trực quan hóa các luồng công việc qua các cột: `TODO` (Chờ làm), `IN_PROGRESS` (Đang thực hiện), `RESOLVED` (Đã xử lý), `DONE` (Hoàn thành).\n"
                   . "   - Kéo thả thẻ công việc giữa các cột để cập nhật trạng thái tức thì.\n"
                   . "3. **Cơ cấu phân cấp công việc (Hierarchy):**\n"
                   . "   - **Backlog / User Story:** Yêu cầu nghiệp vụ cấp cao nhất.\n"
                   . "   - **Task / Bug / Issue:** Công việc con nằm trực thuộc Backlog.\n"
                   . "   - **Test Case:** Kịch bản kiểm thử nằm trực thuộc Bug hoặc Task.\n"
                   . "4. **Tạo nhanh công việc:** Bấm nút **\"+ Tạo Công Việc\"** màu xanh trên thanh Topbar bất cứ lúc nào.";
            $suggestedActions[] = ['label' => 'Truy cập Danh sách Dự Án', 'url' => '/projects'];

        } elseif (preg_match('/(phân quyền|ma trận|quyền hạn|permission)/i', $norm)) {
            $reply = "### 🛡️ Hướng dẫn Cấu hình Ma Trận Phân Quyền (RBAC)\n\n"
                   . "Hệ thống hỗ trợ cơ chế phân quyền bảo mật chặt chẽ cho 4 vai trò chính: `ADMIN`, `IT_MANAGER`, `IT_STAFF`, `USER`:\n\n"
                   . "1. **Truy cập:** Dành cho quản trị viên tại đường dẫn: `/admin/permissions`.\n"
                   . "2. **Các nhóm quyền được kiểm soát:**\n"
                   . "   - **Quyền Menu:** Bật/tắt hiển thị menu trên thanh Topbar, Waffle và Sidebar bên trái (Dự án, Tickets, Dịch vụ, Phê duyệt, Tài sản, Cơ sở tri thức, Báo cáo SLA, Sao lưu,...).\n"
                   . "   - **Quyền Xuất Excel:** Cho phép tải file báo cáo Excel tiếng Việt cho các lưới dữ liệu.\n"
                   . "   - **Quyền Tác Vụ:** Phân công phiếu, thay đổi mức độ ưu tiên, sao lưu hệ thống.\n"
                   . "3. **Lưu cập nhật:** Nhấn nút **\"Lưu Ma Trận Phân Quyền\"** để áp dụng tức thì cho người dùng.";
            $suggestedActions[] = ['label' => 'Vào Quản Trị Phân Quyền', 'url' => '/admin/permissions'];

        } elseif (preg_match('/(sao lưu|backup|phục hồi|restore|cơ sở dữ liệu|database)/i', $norm)) {
            $reply = "### 💾 Hướng dẫn Sao Lưu & Phục Hồi Dữ Liệu Hệ Thống\n\n"
                   . "Hệ thống tích hợp công cụ Backup & Disaster Recovery tự động chuẩn PDO, không phụ thuộc môi trường:\n\n"
                   . "1. **Truy cập:** Dành cho quản trị viên tại: `/admin/backups`.\n"
                   . "2. **Tạo bản sao lưu tức thì:** Bấm nút **\"Tạo Bản Sao Lưu Ngay\"**, nhập nhãn ghi chú để hệ thống kết xuất toàn bộ CSDL thành file `.sql`.\n"
                   . "3. **Tải về máy tính:** Nhấn nút **\"Tải Về\"** để lưu trữ an toàn tại máy chủ dự phòng hoặc ổ cứng ngoài.\n"
                   . "4. **Khôi phục (Restore):** Khi gặp sự cố hoặc cần đưa hệ thống về điểm an toàn, nhấn nút **Khôi phục** và nhập từ khóa xác nhận `RESTORE`.";
            $suggestedActions[] = ['label' => 'Vào Trung Tâm Sao Lưu CSDL', 'url' => '/admin/backups'];

        } elseif (preg_match('/(xuất.*excel|export.*excel|tải.*excel|file.*excel|báo cáo.*excel)/i', $norm)) {
            $reply = "### 📊 Hướng dẫn Xuất Dữ Liệu Ra File Excel\n\n"
                   . "Toàn bộ các lưới dữ liệu trong hệ thống đều hỗ trợ xuất file Excel tiếng Việt chuẩn mã hóa **UTF-8 with BOM**, mở trực tiếp bằng Microsoft Excel không bao giờ bị lỗi font:\n\n"
                   . "1. **Các trang hỗ trợ:**\n"
                   . "   - Danh sách Phiếu hỗ trợ (`/tickets`)\n"
                   . "   - Bảng công việc dự án (`/projects/{id}/issues`)\n"
                   . "   - Danh mục tài sản CNTT (`/assets`)\n"
                   . "   - Nhật ký kiểm toán hệ thống (`/admin/audit-logs`)\n"
                   . "2. **Thao tác:** Nhấn nút **\"Xuất Excel\"** màu xanh lá có biểu tượng `bi-file-earmark-excel-fill` ở góc trên bên phải của bảng dữ liệu.";

        } else {
            // General query response
            $reply = "Tôi đã tiếp nhận câu hỏi của bạn: *\"{$q}\"*\n\n"
                   . "Hiện tại câu hỏi này chưa trùng khớp với các kịch bản tự động xử lý nhanh có sẵn. Để được hỗ trợ tốt nhất, bạn có thể:\n\n"
                   . "1. 🔍 **Thử tìm kiếm với từ khóa cụ thể hơn:** Ví dụ: *mật khẩu, vpn, máy in, outlook, wifi, mạng, ổ c, dự án, phân quyền, sao lưu, excel...*\n"
                   . "2. 📚 **Tra cứu trong Cơ sở Tri thức:** Bạn có thể tham khảo các bài viết cẩm nang kỹ thuật của công ty.\n"
                   . "3. 🎫 **Gửi phiếu yêu cầu hỗ trợ (Ticket):** Nếu đây là sự cố máy tính hoặc ứng dụng cần chuyên viên IT can thiệp, hãy tạo phiếu để đội kỹ thuật tiếp nhận xử lý ngay cho bạn.";
            $suggestedActions[] = ['label' => 'Tạo phiếu hỗ trợ IT', 'url' => '/tickets/create'];
            $suggestedActions[] = ['label' => 'Xem tất cả bài viết KB', 'url' => '/kb'];
        }

        // 3. Append matched KB articles if found in database
        if (!empty($matchedArticles)) {
            $reply .= "\n\n---\n#### 📚 Bài viết Hướng dẫn Liên quan trong Cơ sở Tri thức:\n";
            foreach ($matchedArticles as $art) {
                $artId = (int)$art['id'];
                $artTitle = htmlspecialchars($art['title']);
                $reply .= "- [📖 {$artTitle}](/kb/{$artId})\n";
            }
        }

        return [
            'reply'             => $reply,
            'articles'          => $matchedArticles,
            'suggested_actions' => $suggestedActions
        ];
    }
}

