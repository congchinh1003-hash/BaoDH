# IT SERVICE DESK & ASSET MANAGEMENT
## Functional Specification / Business Rules / Technical Requirements

> Mục đích: Dùng làm tài liệu đầu vào cho AI hoặc developer xây dựng hệ thống IT Service Desk & Asset Management.
>
> Công nghệ: PHP + MySQL + HTML + CSS + JavaScript.
>
> Nguyên tắc: Ưu tiên nghiệp vụ chạy end-to-end, dữ liệu có lịch sử/audit, dễ cấu hình và mở rộng.

---

# 1. MỤC TIÊU HỆ THỐNG

Xây dựng hệ thống Web IT Service Desk & Asset Management để quản lý toàn bộ vòng đời yêu cầu hỗ trợ IT:

USER TẠO TICKET
→ XÁC ĐỊNH MỨC ĐỘ + SLA
→ PHÂN NHÓM IT
→ PHÂN CÔNG NHÂN VIÊN
→ TIẾP NHẬN / XỬ LÝ
→ COMMENT / TRAO ĐỔI
→ CHỜ USER / CHỜ VENDOR NẾU CÓ
→ RESOLVED
→ USER XÁC NHẬN
→ CLOSED
→ ĐÁNH GIÁ

Hệ thống phục vụ 4 nhóm quyền:

1. USER
2. IT STAFF
3. IT MANAGER / LEADER
4. ADMIN

Ngoài ticket hỗ trợ, hệ thống quản lý tài sản CNTT và liên kết Asset với Ticket.

---

# 2. NGUYÊN TẮC NGHIỆP VỤ CỐT LÕI

## Rule 2.1 - Mỗi yêu cầu là một Ticket

Mỗi ticket có mã duy nhất, ví dụ:

- IT-000001
- IT-000002
- IT-000003

Ticket đã phát sinh nghiệp vụ không được xóa vật lý.

Nếu tạo nhầm:
- CANCELLED hoặc
- DUPLICATE

Lịch sử vẫn được giữ.

## Rule 2.2 - Ticket phải có trạng thái rõ ràng

Ticket mới tạo có thể chưa phân công, nhưng phải nằm trong hàng đợi UNASSIGNED để Manager/Leader nhìn thấy.

Ticket đang xử lý phải xác định:
- Nhóm IT hiện tại
- Nhân viên IT phụ trách

## Rule 2.3 - Chuyển nhóm không mất lịch sử

Ví dụ:

IT Helpdesk → IT Network → IT Application

Phải lưu:
- Nhóm cũ
- Nhóm mới
- Người chuyển
- Thời gian
- Lý do chuyển

## Rule 2.4 - Chuyển người xử lý không reset SLA

Ví dụ:
- 08:00 tạo ticket
- SLA 3 giờ
- Due 11:00
- 09:30 chuyển IT A → IT B

Due vẫn là 11:00.

Không được tính lại SLA từ lúc chuyển người.

## Rule 2.5 - SLA được snapshot trên Ticket

Tại thời điểm tạo ticket, lưu:
- Priority
- SLA config
- SLA value
- SLA unit
- SLA start
- SLA due

Sau này Admin thay đổi cấu hình SLA, ticket cũ không thay đổi SLA.

## Rule 2.6 - Không xóa lịch sử

Các thay đổi quan trọng phải có audit/history.

Lịch sử phải trả lời được:
- Ai thực hiện?
- Thực hiện lúc nào?
- Giá trị cũ?
- Giá trị mới?
- Lý do?

---

# 3. ROLE VÀ PHÂN QUYỀN

## 3.1 USER

Được:
- Đăng nhập
- Tạo ticket
- Xem ticket của mình
- Comment
- Upload file
- Theo dõi trạng thái
- Theo dõi SLA
- Xác nhận hoàn thành
- Reopen
- Đánh giá
- Xem tài sản đang sử dụng
- Xem lịch sử tài sản của mình

Không được:
- Xem ticket không thuộc quyền
- Tự đổi người xử lý
- Tự đổi nhóm IT
- Tự đổi SLA
- Xóa lịch sử
- Đóng ticket trực tiếp

## 3.2 IT STAFF

Được:
- Xem ticket thuộc nhóm mình
- Xem ticket được phân công
- Nhận ticket
- Xử lý ticket
- Comment public
- Internal note
- Upload file
- Chuyển ticket
- Chuyển nhóm nếu có permission
- Chuyển người xử lý nếu có permission
- Liên kết Asset
- Cập nhật kết quả xử lý
- Resolve ticket

## 3.3 IT MANAGER / LEADER

Được:
- Xem ticket của nhóm
- Xem ticket chưa phân công
- Phân công
- Reassign
- Transfer
- Điều chỉnh Priority
- Xem workload nhóm
- Xem workload từng nhân viên
- Xem SLA
- Xem ticket sắp quá SLA
- Xem ticket quá SLA
- Quản lý thành viên nhóm
- Xem báo cáo
- Export báo cáo

## 3.4 ADMIN

Được toàn quyền:
- User
- Role
- Permission
- IT Group
- Category
- Service
- Priority
- SLA
- Workflow
- Asset
- Knowledge Base
- Notification
- Report
- System Config
- Audit Log

---

# 4. IT GROUP

Hỗ trợ nhiều nhóm IT.

Ví dụ:
- IT Helpdesk
- IT Network
- IT System
- IT Application
- IT Security
- IT Hardware
- IT Software
- IT Infrastructure

Một nhân viên có thể:
- Thuộc một nhóm chính
- Thuộc thêm các nhóm khác nếu doanh nghiệp cần

Mỗi Ticket có:
- Current Group
- Current Assignee

---

# 5. CATEGORY VÀ SERVICE

Ticket có thể gồm:
- Category
- Service

Ví dụ:

Network
- VPN
- WiFi
- LAN

Application
- ERP
- HRM
- CRM

Hardware
- Laptop
- Desktop
- Printer

Admin phải cấu hình Category/Service, không hard-code trong code.

---

# 6. TẠO TICKET

User nhập:

- Tiêu đề (bắt buộc)
- Nội dung (bắt buộc)
- Category (bắt buộc)
- Service
- Priority (bắt buộc)
- Asset liên quan
- Địa điểm
- Thông tin liên hệ
- File đính kèm

Category có thể yêu cầu thêm field riêng.

Ví dụ Hardware:
- Asset bắt buộc

Ví dụ Network:
- Location
- Device
- Network name

Các field tùy Category phải cấu hình được.

---

# 7. PRIORITY

Có 3 mức mặc định:

| Priority | SLA |
|---|---|
| Thường | 2 ngày |
| Ưu tiên | 1 ngày |
| Khẩn cấp | 3 giờ |

Không hard-code SLA trong PHP.

Admin có thể thay đổi cấu hình.

---

# 8. RULE CHỌN PRIORITY

User được chọn:
- Thường
- Ưu tiên
- Khẩn cấp

Hệ thống tự lấy SLA tương ứng.

User không nhập Due Date.

Ví dụ:
User chọn Khẩn cấp
→ SLA = 3 giờ

---

# 9. ĐIỀU CHỈNH PRIORITY

IT Manager/Leader có thể điều chỉnh Priority nếu thực tế không phù hợp.

Ví dụ:
User chọn Khẩn cấp
→ Manager xác định chỉ là Ưu tiên
→ đổi thành Ưu tiên

Bắt buộc lưu:
- Old Priority
- New Priority
- Reason
- Changed By
- Changed At

Nếu doanh nghiệp yêu cầu thay đổi SLA theo Priority mới, phải có rule rõ ràng.

Khuyến nghị:
- Khi Priority được chỉnh trước khi ticket bắt đầu xử lý, hệ thống có thể tính lại SLA theo rule cấu hình.
- Nếu Priority chỉnh sau khi SLA đã chạy, không được âm thầm reset thời gian; mọi điều chỉnh phải được audit và áp dụng theo một rule cấu hình rõ ràng.

---

# 10. SLA

Mỗi Ticket lưu:

- sla_id
- priority
- sla_value
- sla_unit
- sla_started_at
- sla_due_at
- sla_status
- sla_paused_seconds

Ví dụ:

Created:
20/09/2026 08:00

Priority:
Khẩn cấp

SLA:
3 giờ

Due:
20/09/2026 11:00

---

# 11. SLA CALENDAR

Hệ thống nên hỗ trợ 2 chế độ:

## 11.1 Calendar Time

SLA chạy theo thời gian thực.

Ví dụ:
3 giờ = 3 giờ thực tế.

## 11.2 Business Hours

Ví dụ:
- 08:00 - 17:00
- Thứ 2 - Thứ 6
- Có ngày nghỉ/lễ

Admin cấu hình:
- Working days
- Working hours
- Holiday calendar
- Time zone

Thiết kế database ngay từ đầu phải có khả năng hỗ trợ cả hai.

---

# 12. SLA KHÔNG RESET KHI

Không reset SLA khi:
- Đổi người xử lý
- Đổi nhóm IT
- IT comment
- User comment
- Upload file
- Thay Category
- Thay Service
- Chuyển ticket giữa các nhóm

---

# 13. SLA PAUSE

Mặc định:
- WAITING_USER → Pause SLA

Có thể cấu hình:
- WAITING_VENDOR → Pause SLA

Admin có thể bật/tắt việc pause SLA theo trạng thái.

## Ví dụ

08:00 tạo ticket
SLA = 3 giờ
Due = 11:00

09:00 → WAITING_USER

Ticket đã dùng 1 giờ SLA.

10:00 User phản hồi
→ IN_PROGRESS

1 giờ chờ User được tính là Pause.

Ticket còn 2 giờ SLA.

Due mới = 12:00.

Phải lưu lịch sử khoảng thời gian pause.

---

# 14. MANUAL SLA PAUSE

IT Staff không được tự ý bấm Pause SLA.

SLA pause chủ yếu theo trạng thái workflow.

Manager/Admin có thể can thiệp trong trường hợp đặc biệt nhưng bắt buộc nhập:
- Reason
- User
- Time

Mọi can thiệp phải có Audit Log.

---

# 15. SLA WARNING

Hệ thống cảnh báo khi ticket gần quá hạn.

Ngưỡng phải cấu hình được.

Ví dụ:
- 70% SLA → Warning
- 90% SLA → Critical
- 100% → Overdue

Các notification có thể gửi tới:
- Assignee
- Team Leader
- IT Manager

---

# 16. OVERDUE

Khi Current Time > SLA Due:

sla_status = OVERDUE

Ticket phải hiển thị rõ:
- QUÁ SLA
- Quá bao lâu

Ví dụ:
OVERDUE
Quá 01:25:30

Không tự reset về bình thường.

---

# 17. ESCALATION

Có thể cấu hình:

- 70% SLA: cảnh báo Assignee
- 90% SLA: cảnh báo Assignee + Leader
- 100% SLA: cảnh báo Manager
- 120% SLA: cảnh báo Manager + Admin

Các ngưỡng phải cấu hình được.

---

# 18. TICKET STATUS / WORKFLOW

Trạng thái chính:

- NEW
- ASSIGNED
- IN_PROGRESS
- WAITING_USER
- WAITING_VENDOR
- RESOLVED
- CLOSED

Trạng thái phụ:
- REOPENED
- CANCELLED
- DUPLICATE

Workflow đề xuất:

NEW
→ ASSIGNED
→ IN_PROGRESS
→ WAITING_USER
→ IN_PROGRESS
→ WAITING_VENDOR
→ IN_PROGRESS
→ RESOLVED
→ CLOSED

---

# 19. Ý NGHĨA STATUS

## NEW
Ticket vừa tạo.

## ASSIGNED
Đã có nhân viên phụ trách.

## IN_PROGRESS
Đang xử lý.

## WAITING_USER
Đang chờ User cung cấp thông tin/xác nhận.

## WAITING_VENDOR
Đang chờ nhà cung cấp hoặc bên thứ ba.

## RESOLVED
IT đã hoàn thành xử lý và chờ User xác nhận.

## CLOSED
User xác nhận hoàn thành hoặc Auto Close theo cấu hình.

## REOPENED
User xác nhận chưa giải quyết xong.

## CANCELLED
Yêu cầu bị hủy.

## DUPLICATE
Trùng với ticket khác.

---

# 20. RESOLVED / CLOSED

IT không chuyển trực tiếp:
IN_PROGRESS → CLOSED

IT thực hiện:
IN_PROGRESS → RESOLVED

Khi Resolve bắt buộc nhập:
- Resolution
- Root cause nếu có
- Solution / Action taken

Sau đó User nhận notification.

---

# 21. USER CONFIRMATION

User thấy kết quả xử lý:

- Đã hoàn thành
- Chưa hoàn thành

## Đã hoàn thành

RESOLVED → CLOSED

## Chưa hoàn thành

User nhập lý do:
- REOPENED

Ticket quay lại IT.

Không mất lịch sử xử lý trước đó.

---

# 22. AUTO CLOSE

Có thể cấu hình:
RESOLVED → Auto CLOSED sau X ngày

Ví dụ:
- Auto close after 3 days

Trước khi tự động đóng, hệ thống gửi notification.

---

# 23. REOPEN

Cho phép Reopen theo cấu hình.

Ví dụ:
- Ticket CLOSED không quá 7 ngày → được Reopen

Khi Reopen:
- reopen_count + 1
- lưu reopen_reason
- lưu reopen_by
- lưu reopen_at

Lịch sử cũ vẫn giữ.

---

# 24. DUPLICATE TICKET

Nếu Ticket B trùng Ticket A:

Ticket B:
- Status = DUPLICATE
- duplicate_of = Ticket A

Không xóa Ticket B.

Có thể hiển thị liên kết:
"Ticket trùng với IT-000001"

---

# 25. CANCELLED

Khi hủy phải lưu:
- Cancel reason
- Cancelled by
- Cancelled at

Không xóa ticket.

---

# 26. COMMENT

Có 2 loại:

## PUBLIC COMMENT
User và IT đều nhìn thấy.

## INTERNAL NOTE
Chỉ IT / Manager / Admin nhìn thấy.

Mỗi comment:
- comment_id
- ticket_id
- user_id
- type
- content
- created_at

Không được cho User đọc Internal Note.

---

# 27. WATCHER / FOLLOWER

Ticket có thể có thêm người theo dõi.

Ví dụ:
- Requester
- Assignee
- Manager
- Watcher

Watcher nhận notification nhưng không mặc định có quyền sửa ticket.

---

# 28. ATTACHMENT

User và IT có thể upload file khi:
- Tạo ticket
- Comment
- Resolve

Database chỉ lưu metadata:
- file_name
- file_path
- file_size
- mime_type
- uploaded_by
- uploaded_at

Không lưu file binary trực tiếp trong MySQL.

Phải validate:
- Extension
- MIME
- Size
- Authorization khi download

---

# 29. ASSIGNMENT

Có 2 cơ chế:

## Manual Assignment

Manager/Leader:
Group → Assignee

## Auto Assignment

Ví dụ:
Category Network → IT Network

Sau đó có thể tự chọn nhân viên theo:
- Round Robin
- Người ít ticket đang xử lý
- Skill
- Ca trực

Không bắt buộc làm AI ở Phase 1.

---

# 30. UNASSIGNED QUEUE

Ticket mới nếu chưa có assignee phải nằm ở:

UNASSIGNED

Manager/Leader phải nhìn thấy.

Không để ticket chưa phân công bị "mất" trong hệ thống.

Có thể cấu hình:
NEW > X phút chưa phân công → cảnh báo Leader.

---

# 31. TICKET KHÔNG CÓ HOẠT ĐỘNG

Cấu hình "Stale Ticket".

Ví dụ:
IN_PROGRESS quá X giờ/ngày không có activity:
→ gửi reminder cho Assignee

Có thể escalates lên Leader.

X phải cấu hình được.

---

# 32. TICKET ACTIVITY / TIMELINE

Ticket phải có timeline đầy đủ.

Ví dụ:

08:00 User tạo ticket
08:02 Auto assign Helpdesk
08:05 IT A nhận ticket
08:10 A chuyển IN_PROGRESS
08:30 A comment
09:00 WAITING_USER
10:00 User phản hồi
10:01 IN_PROGRESS
11:00 RESOLVED
11:20 User CLOSED

Timeline phải là nguồn dữ liệu audit quan trọng.

---

# 33. NOTIFICATION

Thông báo khi:
- Tạo ticket
- Phân công
- Reassign
- Đổi nhóm
- Comment
- User phản hồi
- Sắp quá SLA
- Quá SLA
- Resolve
- Close
- Reopen
- Auto close

MVP:
- In-app notification

Có thể mở rộng:
- Email
- Push notification

---

# 34. USER DASHBOARD

User Dashboard phải có KPI:

- Tổng yêu cầu
- Đang xử lý
- Chờ phản hồi
- Đã xử lý
- Đã đóng
- Quá SLA

Bảng ticket:
- Ticket ID
- Tiêu đề
- Priority
- Nhóm IT
- Assignee
- Status
- SLA còn lại

Filter:
- Ticket ID
- Date range
- Status
- Priority
- Group
- Service

---

# 35. IT STAFF DASHBOARD - WORK QUEUE

Đây là màn hình làm việc chính của nhân viên IT.

KPI:
- Tổng ticket đang xử lý
- Cần xử lý hôm nay
- Quá SLA
- Sắp quá SLA
- Khẩn cấp
- Đang xử lý
- Chờ User
- Chờ Vendor
- Đã Resolve hôm nay
- Đã Closed hôm nay

Ví dụ:

VIỆC CỦA TÔI
- Quá SLA: 2
- Sắp quá SLA: 3
- Khẩn cấp: 4
- Cần xử lý hôm nay: 7
- Đang xử lý: 10
- Chờ User: 4

---

# 36. IT STAFF - DANH SÁCH VIỆC CẦN XỬ LÝ

Mặc định ưu tiên:

1. OVERDUE
2. Sắp OVERDUE
3. Khẩn cấp
4. Ưu tiên
5. Thường
6. Ticket cũ hơn

Bảng:
- Ticket
- Tiêu đề
- Priority
- Status
- SLA còn lại
- Group
- Requester
- Last activity
- Created at

Có thể filter theo:
- Status
- Priority
- Category
- Date
- SLA

---

# 37. IT STAFF - NHÓM CÔNG VIỆC

Dashboard nên chia:

## Cần xử lý ngay
- Overdue
- Sắp Overdue
- Khẩn cấp

## Đang xử lý
- IN_PROGRESS

## Đang chờ
- WAITING_USER
- WAITING_VENDOR

## Đã hoàn thành
- RESOLVED hôm nay
- CLOSED hôm nay

Mục đích: IT đăng nhập là biết mình còn bao nhiêu việc và việc nào cần ưu tiên.

---

# 38. MANAGER / LEADER DASHBOARD

Manager phải nhìn được toàn bộ workload nhóm.

KPI:
- Tổng ticket
- Ticket mới
- Unassigned
- Active
- Waiting User
- Waiting Vendor
- Resolved
- Closed
- Overdue
- Near SLA
- SLA achievement

Manager cần trả lời được:
- Có bao nhiêu ticket chưa ai nhận?
- Nhóm đang tồn bao nhiêu ticket?
- Nhân viên nào đang có bao nhiêu ticket?
- Có bao nhiêu ticket sắp quá SLA?
- Ticket nào đã quá SLA?
- Ticket nào tồn lâu?

---

# 39. MANAGEMENT DASHBOARD - SẾP

Dashboard cấp Sếp/Quản lý tập trung vào KPI vận hành, không cần quá nhiều chi tiết kỹ thuật.

KPI chính:
- Total Requests
- Active
- Backlog
- Unassigned
- Overdue
- SLA Achievement %
- Average Resolution Time
- Customer Satisfaction

Biểu đồ:
- Ticket Trend theo ngày/tuần/tháng
- Ticket by IT Group
- Ticket by Category
- Ticket by Priority
- SLA Trend
- Backlog Aging
- Workload by Employee

Filter:
- Ngày
- Tuần
- Tháng
- Quý
- Nhóm IT
- Category
- Priority

---

# 40. WORKLOAD THEO NHÓM

Ví dụ:

| Nhóm IT | Tổng | Active | Waiting | Overdue | Unassigned |
|---|---:|---:|---:|---:|---:|
| Helpdesk | 120 | 25 | 12 | 3 | 2 |
| Network | 80 | 16 | 8 | 5 | 0 |
| Application | 150 | 31 | 15 | 7 | 4 |

Manager click vào Group để xem danh sách ticket.

---

# 41. WORKLOAD THEO NHÂN VIÊN

Bảng:

| Nhân viên | Active | Need Action | Overdue | Resolved | Closed | Reopen |
|---|---:|---:|---:|---:|---:|---:|
| Nguyễn A | 10 | 4 | 1 | 25 | 20 | 2 |
| Nguyễn B | 8 | 2 | 0 | 20 | 18 | 1 |
| Nguyễn C | 15 | 7 | 3 | 18 | 16 | 4 |

Need Action là ticket nhân viên phải tiếp tục xử lý.

Không tính:
- WAITING_USER
- WAITING_VENDOR
nếu SLA đang pause.

Số liệu dùng để quản lý workload và điều phối; không nên chỉ lấy số ticket đóng để đánh giá nhân viên.

---

# 42. QUY TẮC WORKLOAD

Mỗi ticket active phải:
- Thuộc một nhóm đang chịu trách nhiệm
- Có assignee hoặc nằm trong UNASSIGNED

Dashboard Manager phải phát hiện:
- Unassigned
- Quá tải
- Overdue
- Ticket không activity
- Ticket backlog lâu

Có thể sau này thêm:
- Capacity từng nhân viên
- Skill
- Shift

---

# 43. BÁO CÁO BACKLOG

Backlog = Ticket chưa CLOSED.

Phân nhóm tuổi:
- 0-1 ngày
- 2-3 ngày
- 4-7 ngày
- 8-30 ngày
- >30 ngày

Manager có thể drill-down từ biểu đồ/bảng tới danh sách ticket.

---

# 44. BÁO CÁO SLA

Chỉ số:
- Tổng ticket có SLA
- Đạt SLA
- Quá SLA
- SLA Achievement %
- Average Resolution Time
- Median Resolution Time
- Average First Response Time nếu có triển khai
- Số ticket Near SLA

Filter:
- Date
- Group
- Assignee
- Priority
- Category
- Service

---

# 45. BÁO CÁO THEO PRIORITY

Thống kê:
- Thường
- Ưu tiên
- Khẩn cấp

Theo:
- Số lượng
- % tổng ticket
- SLA đạt
- SLA quá hạn

---

# 46. BÁO CÁO CATEGORY / SERVICE

Ví dụ:
- Network
- Hardware
- ERP
- Email
- Account
- Security

Mục đích:
- Phát hiện loại yêu cầu phát sinh nhiều
- Phục vụ tăng Knowledge Base
- Phục vụ phân bổ nguồn lực

---

# 47. TREND / BIỂU ĐỒ

Dashboard hỗ trợ:
- Ticket theo ngày
- Ticket theo tuần
- Ticket theo tháng
- So sánh tháng này với tháng trước
- Trend SLA
- Trend Backlog

Có thể dùng Chart.js.

---

# 48. BÁO CÁO HIỆU SUẤT NHÂN VIÊN

Chỉ số:
- Assigned
- Resolved
- Closed
- Active
- Need Action
- Overdue
- SLA achievement
- Average Resolution Time
- Reopen count
- Rating
- Backlog

Filter:
- Ngày
- Tuần
- Tháng
- Quý

Lưu ý:
Không đánh giá nhân viên chỉ bằng số ticket đóng. Nên xem cùng:
- Priority
- Category
- SLA
- Thời gian thực xử lý
- Thời gian chờ User/Vendor
- Reopen
- Khối lượng công việc

---

# 49. RATING / SATISFACTION

Sau khi CLOSED, User được đánh giá:
- 1 đến 5 sao
- Comment

Báo cáo:
- Average Rating
- Number of ratings
- Rating by Group
- Rating by Employee

---

# 50. EXPORT

Báo cáo cần hỗ trợ:
- Excel
- CSV
- Print

Export phải áp dụng đúng filter hiện tại.

---

# 51. ASSET MANAGEMENT

Asset types:
- Laptop
- Desktop
- Monitor
- Printer
- Phone
- Server
- Network Device
- Software License
- Other

Thông tin Asset:
- Asset Code
- Asset Name
- Category
- Manufacturer
- Model
- Serial Number
- Purchase Date
- Warranty Expire
- Status
- Department
- Location
- Current User

---

# 52. ASSET STATUS

- IN_STOCK
- IN_USE
- REPAIR
- LOST
- DAMAGED
- LIQUIDATED
- DISPOSED

---

# 53. ASSET HANDOVER

Các nghiệp vụ:
- Bàn giao
- Thu hồi
- Điều chuyển
- Đưa đi sửa
- Nhận lại sau sửa
- Thanh lý

Mỗi lần thay đổi lưu:
- Asset
- From User
- To User
- From Location
- To Location
- Date
- Reason
- Performed By

Không update đè lịch sử.

---

# 54. ASSET REPAIR HISTORY

Mỗi Asset có lịch sử sửa chữa:
- Ngày sửa
- Nội dung lỗi
- Nguyên nhân
- Hình thức xử lý
- Linh kiện thay thế
- Chi phí
- Đơn vị sửa chữa
- Người thực hiện
- Ghi chú

---

# 55. ASSET ↔ TICKET

Một Ticket có thể liên kết một hoặc nhiều Asset.

Một Asset có thể có nhiều Ticket.

Asset detail phải xem được:
- Ticket history
- Repair history
- Assignment history

---

# 56. QR CODE ASSET

Mỗi Asset có QR Code.

Ví dụ URL:
`/asset/LAP-000123`

Scan QR có thể xem:
- Asset Code
- Thông tin cơ bản
- Người sử dụng
- Trạng thái
- Warranty
- Ticket liên quan

Quyền xem phụ thuộc Role.

---

# 57. KNOWLEDGE BASE

Knowledge Base chứa bài hướng dẫn:
- Reset Password
- VPN
- WiFi
- Email
- Printer
- Windows
- ERP
- Security

Thông tin bài:
- Title
- Category
- Content
- Attachment
- Author
- Status
- Published Date

User có thể tìm KB trước khi tạo ticket.

---

# 58. AI CHỨC NĂNG NÂNG CAO

Có thể bổ sung sau:

- AI phân loại Ticket
- AI xác định Category
- AI gợi ý IT Group
- AI gợi ý Priority
- AI tìm Knowledge Base phù hợp
- AI gợi ý hướng xử lý
- AI tóm tắt lịch sử Ticket
- AI phát hiện ticket trùng
- AI gợi ý nội dung reply cho IT

AI không được tự động:
- Đóng ticket
- Thay đổi SLA
- Thay Priority quan trọng
nếu chưa có rule và quyền rõ ràng.

---

# 59. SECURITY

Backend phải kiểm tra permission.

Không chỉ ẩn button ở Frontend.

Phải có:
- Password hashing
- Session authentication
- RBAC
- Prepared Statements / PDO
- CSRF protection
- XSS protection
- Upload validation
- Download authorization
- Audit Log

Ví dụ:
User truy cập `/ticket?id=12345`
→ Backend phải kiểm tra ticket có thuộc quyền User hay không.

---

# 60. AUDIT LOG

Các thao tác phải audit:
- Create Ticket
- Assign
- Reassign
- Transfer
- Change Priority
- Change Status
- Change SLA
- Comment
- Resolve
- Close
- Reopen
- Cancel
- Duplicate
- Asset Assignment
- Asset Transfer

Audit fields:
- User
- Action
- Object Type
- Object ID
- Old Value
- New Value
- Reason
- IP
- Created At

Audit không cho User/IT xóa.

---

# 61. DATABASE - CORE TABLES

## User / Access
- users
- roles
- permissions
- user_roles
- role_permissions

## IT Organization
- it_groups
- it_group_members

## Configuration
- ticket_categories
- ticket_services
- ticket_priorities
- sla_configs
- sla_calendars
- workflow_statuses
- notification_configs

## Ticket
- tickets
- ticket_assignments
- ticket_comments
- ticket_attachments
- ticket_history
- ticket_transfers
- ticket_watchers
- ticket_sla_pauses
- ticket_ratings
- ticket_notifications

## Asset
- assets
- asset_categories
- asset_assignments
- asset_repairs
- asset_history
- asset_tickets

## Knowledge Base
- knowledge_base
- knowledge_categories

## System
- notifications
- audit_logs

---

# 62. TICKET TABLE - ĐỀ XUẤT

tickets:
- id
- ticket_code
- title
- description
- requester_id
- category_id
- service_id
- priority_id
- group_id
- assignee_id
- status
- sla_id
- sla_value
- sla_unit
- sla_started_at
- sla_due_at
- sla_status
- sla_paused_seconds
- reopen_count
- created_at
- updated_at
- resolved_at
- closed_at

Có thể bổ sung:
- first_response_at
- last_activity_at
- resolution_due_at nếu doanh nghiệp tách KPI response/resolution

---

# 63. KHÔNG HARD-CODE

Không hard-code:
- Priority
- SLA
- Category
- Service
- Group
- Status
- Warning Threshold
- Auto Close Days
- Working Hours
- Holiday
- Escalation

Các dữ liệu này phải cấu hình được.

---

# 64. CRON JOB

Cần cron job định kỳ để xử lý:
- SLA warning
- SLA overdue
- Escalation
- Auto close
- Unassigned reminder
- Stale ticket reminder
- Notification queue

Có thể chạy mỗi phút cho SLA nếu cần độ chính xác cao.

---

# 65. SOURCE CODE STRUCTURE

Đề xuất:

/config
/controllers
/models
/services
/middleware
/routes
/views
/public
/assets
/uploads
/database
/cron

Service nên tách riêng:
- TicketService.php
- SlaService.php
- AssignmentService.php
- NotificationService.php
- AssetService.php
- ReportService.php

Không viết toàn bộ hệ thống trong một file PHP.

---

# 66. TECH STACK

Backend:
- PHP
- PDO
- MySQL

Frontend:
- HTML5
- CSS3
- JavaScript
- Fetch API / AJAX

Có thể sử dụng:
- Bootstrap
- Chart.js
- DataTables
- SweetAlert

Không bắt buộc framework.

---

# 67. PHASE 1 - CORE MVP

Bắt buộc hoàn thành:
- Login
- User
- Role
- Permission
- IT Group
- Category
- Service
- Priority
- SLA
- Create Ticket
- Assignment
- Reassign
- Transfer Group
- Workflow
- Comment
- Internal Note
- Attachment
- Notification
- SLA Countdown
- SLA Pause
- SLA Warning
- Overdue
- User Dashboard
- IT Work Queue Dashboard
- Manager Dashboard
- Basic Reports
- Audit Log

MVP phải chạy end-to-end.

---

# 68. PHASE 2 - MANAGEMENT

- Asset Management
- Asset Assignment
- Asset Repair History
- Asset ↔ Ticket
- Rating
- Knowledge Base
- Export Excel/CSV
- Advanced Reports
- QR Code
- Business Hours SLA
- Holiday Calendar
- Escalation

---

# 69. PHASE 3 - ADVANCED

- Auto Assignment
- Email Notification
- Push Notification
- AI Classification
- AI Suggestion
- Duplicate Detection
- Knowledge Base Recommendation
- AI Summary

---

# 70. TEST CASE - TICKET STANDARD

Case:
User tạo ticket Priority = Thường

Expected:
- SLA = 2 ngày
- Ticket = NEW
- Có timestamp tạo
- Chưa có Assignee nếu chưa auto assign
- Xuất hiện trong UNASSIGNED

---

# 71. TEST CASE - TICKET PRIORITY

Case:
User chọn Ưu tiên

Expected:
- SLA = 1 ngày
- Countdown đúng
- Warning đúng
- Overdue đúng

---

# 72. TEST CASE - TICKET URGENT

Case:
User chọn Khẩn cấp lúc 08:00

Expected:
- SLA = 3 giờ
- Due = 11:00
- Countdown chạy
- 70%/90% warning theo config
- 11:00 trở đi = OVERDUE

---

# 73. TEST CASE - TRANSFER GROUP

Case:
IT Helpdesk chuyển ticket sang IT Network

Expected:
- Current Group = IT Network
- Assignee cập nhật theo rule
- Transfer History được lưu
- SLA không reset
- Audit Log được lưu

---

# 74. TEST CASE - REASSIGN

Case:
IT A → IT B

Expected:
- Assignee = IT B
- SLA không reset
- History lưu A → B
- Notification IT B

---

# 75. TEST CASE - WAITING USER

Case:
09:00 ticket IN_PROGRESS
→ WAITING_USER

Expected:
- SLA pause
- lưu pause_started_at
- có notification cho User

10:00 User comment
→ IN_PROGRESS

Expected:
- SLA resume
- pause duration = 1 giờ

---

# 76. TEST CASE - RESOLVE / CLOSE

Case:
IT xử lý xong

Expected:
- IN_PROGRESS → RESOLVED
- Bắt buộc nhập Resolution
- User nhận notification

User chọn "Đã hoàn thành":
- RESOLVED → CLOSED

User chọn "Chưa hoàn thành":
- REOPENED
- nhập reason
- quay lại IT queue

---

# 77. TEST CASE - OVERDUE

Case:
Current Time > Due Time

Expected:
- sla_status = OVERDUE
- hiển thị thời gian quá SLA
- gửi escalation notification
- không reset

---

# 78. TEST CASE - UNASSIGNED

Case:
Ticket mới tạo nhưng chưa có Assignee

Expected:
- Ticket nằm UNASSIGNED
- Manager/Leader nhìn thấy
- Có thể cảnh báo nếu quá X phút chưa phân công

---

# 79. TEST CASE - STALE TICKET

Case:
IN_PROGRESS nhưng không activity quá X giờ

Expected:
- reminder cho Assignee
- có thể escalation Leader

---

# 80. TEST CASE - PERMISSION

Case:
User cố truy cập ticket của User khác

Expected:
- Backend từ chối
- Không trả dữ liệu ticket
- Ghi log nếu cần

---

# 81. TEST CASE - INTERNAL NOTE

Case:
IT tạo Internal Note

Expected:
- IT/Manager/Admin thấy
- User không thấy

---

# 82. TEST CASE - DUPLICATE

Case:
Ticket B trùng Ticket A

Expected:
- Ticket B = DUPLICATE
- duplicate_of = A
- giữ lịch sử
- không xóa B

---

# 83. TEST CASE - ASSET

Case:
Laptop LAP-001 được bàn giao User A

Expected:
- Asset status = IN_USE
- Current User = A
- Assignment History có record

Sau đó thu hồi:
- Current User = null hoặc kho
- History thêm record mới
- Không overwrite record cũ

---

# 84. NGUYÊN TẮC DASHBOARD

## User Dashboard
Tập trung:
- Ticket của tôi
- Trạng thái
- SLA
- Trao đổi

## IT Staff Dashboard
Tập trung:
- Việc của tôi
- Need Action
- Near SLA
- Overdue
- Waiting
- Work queue

## Manager Dashboard
Tập trung:
- Team workload
- Unassigned
- Backlog
- SLA
- Overdue
- Workload từng nhân viên

## Sếp Dashboard
Tập trung:
- Total Requests
- Active
- Backlog
- Overdue
- SLA Achievement
- Average Resolution Time
- Customer Satisfaction
- Trend
- Group workload
- Employee workload

---

# 85. NGUYÊN TẮC CUỐI

Hệ thống phải đảm bảo:

1. Một Ticket có một SLA snapshot.
2. Đổi người xử lý không reset SLA.
3. Đổi nhóm không reset SLA.
4. Waiting User/Vendor có cơ chế pause rõ ràng.
5. Ticket chưa phân công phải có UNASSIGNED queue.
6. Mọi thay đổi quan trọng phải có History/Audit.
7. User không thể tự đóng ticket trực tiếp.
8. IT Resolve, User Confirm, sau đó mới Closed.
9. Dashboard IT phải thể hiện "việc cần xử lý".
10. Dashboard Manager/Sếp phải thể hiện workload, backlog, SLA và overdue.
11. Không xóa dữ liệu lịch sử nghiệp vụ.
12. Không hard-code cấu hình nghiệp vụ.
13. Permission phải kiểm tra ở backend.
14. MVP phải chạy end-to-end trước khi làm AI/QR/Push.

---

# 86. YÊU CẦU ĐỐI VỚI AI/DEVELOPER

Khi sử dụng tài liệu này để xây dựng hệ thống:

1. Phân tích database trước.
2. Tạo SQL schema.
3. Xây Authentication + RBAC.
4. Xây Ticket Core.
5. Xây Workflow.
6. Xây SLA Service.
7. Xây Assignment/Transfer.
8. Xây Comment/Attachment/Notification.
9. Xây Dashboard theo từng Role.
10. Xây Reports.
11. Xây Asset.
12. Sau khi core chạy ổn mới thêm Knowledge Base, QR và AI.

Không chỉ tạo giao diện mockup.

Mỗi chức năng phải có:
- Database
- Backend
- Validation
- Permission
- UI
- Audit
- Error handling
- Test case

Ưu tiên tính đúng nghiệp vụ và khả năng vận hành thực tế hơn giao diện phức tạp.
