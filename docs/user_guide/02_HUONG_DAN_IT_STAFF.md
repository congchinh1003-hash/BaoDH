# SỔ TAY HƯỚNG DẪN THAO TÁC TỪNG BƯỚC: KỸ THUẬT VIÊN IT (IT STAFF)
## Dành Cho Chuyên Viên Helpdesk, Kỹ Sư Mạng, Kỹ Thuật Viên Phần Cứng & Phần Mềm

---

## MỤC LỤC CHI TIẾT
1. [Đăng Nhập & Quản Lý Hàng Đợi Cá Nhân (Staff Work Queue)](#1-đăng-nhập--quản-lý-hàng-đợi-cá-nhân-staff-work-queue)
2. [Tiếp Nhận Phiếu Mới (Take Ticket) & Chuyển Giao Nhóm IT (Transfer Group)](#2-tiếp-nhận-phiếu-mới-take-ticket--chuyển-giao-nhóm-it-transfer-group)
3. [Thao Tác Kéo Thả Trực Quan Trên Bảng Jira Kanban Board](#3-thao-tác-kéo-thả-trực-quan-trên-bảng-jira-kanban-board)
4. [Kiểm Soát Vòng Đời Trạng Thái & Cơ Chế Tự Động Dừng / Tiếp Tục SLA](#4-kiểm-soát-vòng-đời-trạng-thái--cơ-chế-tự-động-dừng--tiếp-tục-sla)
5. [Khai Thác Trợ Lý Kỹ Thuật AI Copilot (Tóm Tắt, Đề Xuất Giải Pháp & Soạn Thư)](#5-khai-thác-trợ-lý-kỹ-thuật-ai-copilot-tóm-tắt-đề-xuất-giải-pháp--soạn-thư)
6. [Quản Lý Danh Sách Đầu Việc Kỹ Thuật (Checklist / Sub-tasks)](#6-quản-lý-danh-sách-đầu-việc-kỹ-thuật-checklist--sub-tasks)
7. [Trao Đổi Phản Hồi Công Khai & Ghi Chú Nội Bộ Bí Mật (Internal Note)](#7-trao-đổi-phản-hồi-công-khai--ghi-chú-nội-bộ-bí-mật-internal-note)
8. [Liên Kết Thiết Bị Tài Sản (IT Asset Linking) & Tạo Lỗi Đã Biết (KEDB)](#8-liên-kết-thiết-bị-tài-sản-it-asset-linking--tạo-lỗi-đã-biết-kedb)
9. [Nghiệm Thu, Nhập Giải Pháp & Hoàn Thành Phiếu (Resolve Ticket)](#9-nghiệm-thu-nhập-giải-pháp--hoàn-thành-phiếu-resolve-ticket)
10. [Quản Trị Vòng Đời Tài Sản CNTT (ITAM: Bàn Giao, Bảo Hành, Sửa Chữa)](#10-quản-trị-vòng-đời-tài-sản-cntt-itam-bàn-giao-bảo-hành-sửa-chữa)

---

## 1. ĐĂNG NHẬP & QUẢN LÝ HÀNG ĐỢI CÁ NHÂN (STAFF WORK QUEUE)

### Mục đích:
Theo dõi toàn bộ các phiếu sự cố được giao cho bạn, phân loại theo độ khẩn cấp và không bao giờ để sót phiếu bị trễ hạn SLA.

### Các bước thao tác bấm chuột:

#### Bước 1.1: Đăng nhập tài khoản IT Staff
- Truy cập `http://localhost:3000/login`.
- Nhập Tên đăng nhập: `it_helpdesk` (hoặc `it_network`).
- Nhập Mật khẩu: `123456`.
- Bấm nút **"ĐĂNG NHẬP HỆ THỐNG"**.
- **Kết quả:** Hệ thống tự động chuyển bạn thẳng vào giao diện **Bàn Làm Việc Kỹ Thuật (Staff Work Queue)** tại `http://localhost:3000/dashboard`.

```text
GIAO DIỆN HÀNG ĐỢI CÔNG VIỆC KỸ THUẬT (STAFF QUEUE)
+-----------------------------------------------------------------------------------------+
|                                HÀNG ĐỢI CÔNG VIỆC CỦA TÔI                               |
|                                                                                         |
|  [ TAB 1: Tất cả (6) ]  [ TAB 2: Cần Xử Lý Ngay (2) 🔴 ]  [ TAB 3: Đang Xử Lý (3) ]     |
+-----------------------------------------------------------------------------------------+
| MÃ PHIẾU  | TIÊU ĐỀ SỰ CỐ            | NGƯỜI YÊU CẦU  | ƯU TIÊN | TRẠNG THÁI  | THỜI HẠN SLA    |
|-----------+--------------------------+----------------+---------+-------------+-----------------|
| IT-000012 | Lỗi màn hình xanh BSOD   | Nguyễn Văn A   | URGENT  | IN_PROGRESS | 🔴 Quá 00:15:30 |
| IT-000015 | Không kết nối được VPN   | Đỗ Mai Phương  | HIGH    | IN_PROGRESS | 🟡 Còn 00:45:10 |
| IT-000018 | Máy in Canon kẹt giấy l2 | Nguyễn Thị Lan | NORMAL  | WAITING_USR | ⏸️ Tạm dừng SLA |
| IT-000005 | Cài đặt phần mềm CAD     | Hoàng Nam      | LOW     | RESOLVED    | ✅ Đạt SLA      |
+-----------------------------------------------------------------------------------------+
```

#### Bước 1.2: Lọc và ưu tiên xử lý
- **Bấm vào tab "Cần Xử Lý Ngay" (Need Action):** Danh sách sẽ lọc ra các phiếu có mức độ `URGENT` hoặc sắp chạm ngưỡng vi phạm thời gian cam kết SLA.
- **Quan sát màu sắc SLA để xử lý theo thứ tự:**
  1. 🔴 **Màu đỏ:** Xử lý ngay lập tức vì phiếu đã quá hạn cam kết.
  2. 🟡 **Màu vàng:** Phiếu còn dưới 30% thời gian, cần liên hệ người dùng xử lý dứt điểm trong giờ tới.
  3. 🟢 **Màu xanh lá:** Thời gian an toàn (> 30% quỹ thời gian cam kết).
  4. ⏸️ **Màu cam nhạt:** Đang tạm dừng đếm giờ do bạn đang đợi người dùng hoặc đợi mua linh kiện.

---

## 2. TIẾP NHẬN PHIẾU MỚI (TAKE TICKET) & CHUYỂN GIAO NHÓM IT (TRANSFER GROUP)

### Mục đích:
Chủ động nhận các phiếu chưa có người xử lý trong nhóm của bạn hoặc chuyển giao phiếu sang nhóm chuyên môn khác nếu không thuộc phạm vi xử lý của bạn.

### Các bước thao tác bấm chuột:

```text
CÁC NÚT ĐIỀU PHỐI Ở ĐẦU TRANG CHI TIẾT TICKET
+-----------------------------------------------------------------------------------------+
| Phiếu: #IT-000025 | Nhóm: Mạng & Hạ Tầng | Phụ trách: Chưa phân công                   |
|                                                                                         |
|       [ ✋ NHẬN PHIẾU NÀY (TAKE TICKET) ]       [ ⇄ CHUYỂN NHÓM IT KHÁC ]               |
|       ↖ (BẤM VÀO ĐÂY ĐỂ NHẬN PHỤ TRÁCH)         ↖ (BẤM VÀO ĐÂY NẾU SAI NHÓM)            |
+-----------------------------------------------------------------------------------------+
```

#### Bước 2.1: Tiếp nhận phiếu (Take Ticket)
- Khi mở một phiếu đang ở trạng thái `NEW` hoặc chưa có kỹ thuật viên phụ trách (`Chưa phân công`):
- Nhấp chuột vào nút màu xanh **"✋ Nhận Phiếu Này" (Take Ticket)** ở góc phải phía trên.
- **Kết quả:** Hệ thống lập tức gán tên bạn làm người phụ trách, đổi trạng thái phiếu sang `ASSIGNED`, kích hoạt đồng hồ SLA và ghi nhật ký lịch sử phân công.

#### Bước 2.2: Chuyển giao sang nhóm IT khác (Transfer Group)
- Nếu sự cố không thuộc nhóm của bạn (Ví dụ: Bạn thuộc nhóm Phần Cứng nhưng lỗi thuộc về Lập Trình Cơ Sở Dữ Liệu):
- Nhấp chuột vào nút **"⇄ Chuyển Nhóm IT Khác"**.
- Cửa sổ Modal hiện lên:
  - Bấm chọn **Nhóm đích** (Ví dụ: *Ứng Dụng & Phần Mềm ERP*).
  - Bấm vào ô **Lý do chuyển nhóm**: Gõ rõ lý do (Ví dụ: *"Đã kiểm tra máy tính phần cứng bình thường, lỗi do database ERP bị khóa tài khoản người dùng, chuyển nhóm ERP xử lý tiếp."*).
  - Nhấp nút **"Xác Nhận Chuyển Nhóm"**.
- **Kết quả:** Phiếu lập tức rời khỏi hàng đợi của bạn và chuyển đến hàng đợi của nhóm IT mới.

---

## 3. THAO TÁC KÉO THẢ TRỰC QUAN TRÊN BẢNG JIRA KANBAN BOARD

### Mục đích:
Quản lý tiến độ công việc trực quan dạng bảng Kanban như trên phần mềm Jira chuyên nghiệp.

### Các bước thao tác bấm chuột:
- **Bước 3.1:** Trên menu điều hướng bên trái, nhấp chuột vào mục **"Kanban Board"** (hoặc truy cập `http://localhost:3000/tickets/board`).

```text
GIAO DIỆN BẢNG JIRA KANBAN BOARD
+-----------------------------------------------------------------------------------------+
| CHỜ XỬ LÝ (NEW)              ĐANG XỬ LÝ (IN PROGRESS)          ĐÃ GIẢI QUYẾT (RESOLVED) |
| [ 2 Phiếu ]                  [ 3 Phiếu ]                       [ 8 Phiếu ]              |
|------------------------------+---------------------------------+------------------------|
| +--------------------------+ | +-----------------------------+ | +--------------------+ |
| | #IT-000030               | | | #IT-000015                  | | | #IT-000008         | |
| | Mất mạng bàn 14 tầng 2   | | | Lỗi kết nối VPN FortiClient | | | Thay bàn phím mới  | |
| | [🔴 URGENT]              | | | [🟡 HIGH]                   | | | [🟢 NORMAL]        | |
| | Người tạo: Trần Nam      | | | KTV: Lê Quốc Bảo            | | | KTV: Lê Quốc Bảo   | |
| +--------------------------+ | +-----------------------------+ | +--------------------+ |
|            |                 |                                 |                        |
|            +---(GIỮ CHUỘT VÀ KÉO THẺ SANG CỘT IN PROGRESS)---> |                        |
+-----------------------------------------------------------------------------------------+
```

- **Bước 3.2 - Kéo thẻ bắt đầu xử lý:**
  - Nhấp giữ chuột trái vào thẻ phiếu ở cột **CHỜ XỬ LÝ (NEW)**.
  - Kéo thẻ sang cột **ĐANG XỬ LÝ (IN PROGRESS)** và thả chuột ra.
  - Trạng thái phiếu lập tức cập nhật sang `IN_PROGRESS` mà không cần tải lại trang.
- **Bước 3.3 - Kéo thẻ hoàn thành:**
  - Kéo thẻ từ cột **IN PROGRESS** sang cột **ĐÃ GIẢI QUYẾT (RESOLVED)** và thả chuột.
  - Một cửa sổ nhỏ bật ra yêu cầu bạn gõ tóm tắt cách xử lý sự cố > Bấm **"Lưu Giải Pháp"** để hoàn tất.

---

## 4. KIỂM SOÁT VÒNG ĐỜI TRẠNG THÁI & CƠ CHẾ TỰ ĐỘNG DỪNG / TIẾP TỤC SLA

### Mục đích:
Hiểu rõ quy tắc dừng giờ SLA của hệ thống ITIL để bảo vệ KPI cá nhân khi thời gian xử lý bị gián đoạn do yếu tố khách quan (Người dùng đi vắng, đợi phụ tùng thay thế).

### Các bước thao tác bấm chuột:

```text
THANH THAO TÁC TRẠNG THÁI TRÊN TRANG CHI TIẾT TICKET
+-----------------------------------------------------------------------------------------+
| Cập nhật trạng thái phiếu:                                                              |
| [ ĐANG XỬ LÝ (IN_PROGRESS)                                                           ▼ ]|
|  ├── Đang Xử Lý (IN_PROGRESS)        --> Đồng hồ SLA đếm ngược bình thường              |
|  ├── Chờ User Phản Hồi (WAITING_USER) --> [⏸️ HỆ THỐNG TỰ ĐỘNG DỪNG TÍNH GIỜ SLA]        |
|  ├── Chờ Vendor / Hãng (WAITING_VEND) --> [⏸️ HỆ THỐNG TỰ ĐỘNG DỪNG TÍNH GIỜ SLA]        |
|  └── Đã Giải Quyết (RESOLVED)        --> Hoàn thành khắc phục sự cố                     |
|                                                                                         |
| Lý do chuyển trạng thái:                                                                |
| [ Cần người dùng gửi lại ảnh chụp mã lỗi hiển thị trên màn hình máy scan.             ] |
|                                                                                         |
|                      [    ÁP DỤNG TRẠNG THÁI MỚI    ]                                   |
+-----------------------------------------------------------------------------------------+
```

#### Bước 4.1: Chuyển sang chờ người dùng (Tạm dừng SLA)
- Khi bạn đã hướng dẫn nhưng người dùng chưa làm theo hoặc người dùng không có mặt tại bàn làm việc:
- Nhấp chuột vào hộp chọn trạng thái > Chọn **"Chờ User Phản Hồi" (WAITING_USER)**.
- Bấm vào ô lý do: Gõ thông tin cần đợi (Ví dụ: *"Đã gọi điện nhưng máy bận, đang chờ chị Lan gửi ID UltraViewer"*).
- Bấm nút **"Áp Dụng Trạng Thái Mới"**.
- **Kết quả:** Đồng hồ đo thời gian SLA lập tức **đóng băng (Paused)**, huy hiệu đổi sang màu cam `⏸️ Tạm Dừng SLA`. Khoảng thời gian chờ đợi này sẽ **không bị tính vào thời gian trễ của kỹ thuật viên**.

#### Bước 4.2: Tự động kích hoạt lại đồng hồ SLA
- Ngay khi người dùng gõ bình luận phản hồi lại trên phiếu, hệ thống sẽ:
  - Tự động chuyển trạng thái phiếu trở lại **IN_PROGRESS**.
  - Tự động mở lại bộ đếm SLA để bạn tiếp tục xử lý.

---

## 5. KHAI THÁC TRỢ LÝ KỸ THUẬT AI COPILOT (TÓM TẮT, ĐỀ XUẤT GIẢI PHÁP & SOẠN THƯ)

### Mục đích:
Sử dụng trí tuệ nhân tạo tích hợp sẵn trên phiếu để chẩn đoán nhanh nguyên nhân lỗi kỹ thuật và sinh nội dung thư trao đổi chuẩn mực chỉ trong 1 cú nhấp chuột.

### Các bước thao tác bấm chuột:

```text
HỘP CÔNG CỤ AI COPILOT KỸ THUẬT TRÊN TRANG CHI TIẾT PHIẾU
+-----------------------------------------------------------------------------------------+
| 🤖 AI COPILOT KỸ THUẬT                                 [ ✨ Phân Tích Sự Cố Bằng AI ]   |
|                                                               ↖ (BƯỚC 1: BẤM VÀO ĐÂY)   |
+-----------------------------------------------------------------------------------------+
| [ Sau 1 giây xử lý, AI sẽ hiển thị kết quả phân tích: ]                                 |
|                                                                                         |
| 📌 TÓM TẮT SỰ CỐ NHANH:                                                                 |
| Ticket #IT-000015 của Đỗ Mai Phương báo lỗi kết nối VPN từ nhà. Mã lỗi báo -8004.       |
| Đã có 2 lần thử kết nối lại nhưng cổng 10443 không phản hồi.                            |
|                                                                                         |
| 🛠️ 4 BƯỚC XỬ LÝ KỸ THUẬT ĐỀ XUẤT:                                                      |
| 1. Kiểm tra tài khoản người dùng có bị tích cờ 'Account Disabled' trên Active Directory. |
| 2. Kiểm tra nhóm người dùng có nằm trong VPN-Access-Group trên tường lửa FortiGate.      |
| 3. Hướng dẫn người dùng xóa cache TLS trên FortiClient bằng cách cài lại chứng chỉ SSL.  |
| 4. Kiểm tra mạng gia đình người dùng có bị nhà mạng chặn cổng UDP/TCP 10443 không.      |
|                                                                                         |
| 📝 DỰ THẢO THƯ PHẢN HỒI LỊCH SỰ:                                                        |
| "Chào chị Phương, bộ phận IT đã nhận được thông tin lỗi VPN mã -8004 của chị. Em đã kiểm|
| tra tài khoản trên hệ thống vẫn hoạt động tốt. Chị vui lòng khởi động lại modem mạng nhà|
| và kết nối lại giúp em nhé. Nếu vẫn lỗi em sẽ gọi UltraViewer hỗ trợ chị."              |
|                                                                                         |
|              [  📋 ÁP DỤNG DỰ THẢO NÀY VÀO Ô BÌNH LUẬN  ]                               |
|              ↖ (BƯỚC 2: BẤM VÀO ĐÂY ĐỂ TỰ ĐỘNG ĐIỀN CHỮ)                                |
+-----------------------------------------------------------------------------------------+
```

#### Bước 5.1: Kích hoạt phân tích sự cố
- Tại trang chi tiết phiếu (`/tickets/{id}`), tìm đến khung màu xanh nhạt **"AI Copilot Kỹ Thuật"**.
- Nhấp chuột vào nút **"✨ Phân Tích Sự Cố Bằng AI"**.
- Chờ 1 giây để AI phân tích toàn bộ tiêu đề, mô tả và các đoạn chat trước đó.

#### Bước 5.2: Xem khuyến nghị kỹ thuật
- Đọc các bước xử lý kỹ thuật được đề xuất để thực hiện trên máy chủ hoặc thiết bị mạng.

#### Bước 5.3: Áp dụng thư phản hồi tự động
- Nhấp chuột vào nút **"📋 Áp Dụng Dự Thảo Này Vào Ô Bình Luận"**.
- Màn hình sẽ tự động cuộn xuống khung bình luận, toàn bộ câu chữ mẫu lịch sự đã được điền sẵn nguyên vẹn vào ô.
- Bạn có thể chỉnh sửa lại vài chữ nếu muốn, rồi bấm nút **"Gửi Phản Hồi"**.

---

## 6. QUẢN LÝ DANH SÁCH ĐẦU VIỆC KỸ THUẬT (CHECKLIST / SUB-TASKS)

### Mục đích:
Chia nhỏ một sự cố phức tạp (như cài mới máy tính, xử lý virus, thay thế linh kiện) thành từng bước nhỏ để kiểm soát chặt chẽ tiến độ không bị bỏ sót.

### Các bước thao tác bấm chuột:

```text
GIAO DIỆN KHUNG ĐẦU VIỆC KỸ THUẬT (CHECKLIST)
+-----------------------------------------------------------------------------------------+
| ☑️ Đầu Việc Kỹ Thuật (Checklist)     2 / 4 hoàn thành (50%)                              |
| [████████████████████████████░░░░░░░░░░░░░░░░░░░░░░░░░░░░] 50%                           |
|                                                                                         |
| [x] 1. Sao lưu dữ liệu thư mục Desktop & Downloads sang ổ D         [🗑️ Xóa]             |
| [x] 2. Quét sạch mã độc bằng Windows Defender Offline               [🗑️ Xóa]             |
| [ ] 3. Cập nhật bản vá bảo mật Windows Update mới nhất (KB5034123)  [🗑️ Xóa] ↖ BẤM VÀO  |
| [ ] 4. Bàn giao và cho người dùng ký biên bản nghiệm thu            [🗑️ Xóa]   ĐỂ TICK   |
|                                                                                         |
| Thêm đầu việc con mới:                                                                  |
| [ Nhập tên công việc con vào đây...                 ]   [ + THÊM ĐẦU VIỆC ]             |
+-----------------------------------------------------------------------------------------+
```

#### Bước 6.1: Thêm đầu việc con
- Nhấp chuột vào ô: *"Nhập tên công việc con vào đây..."*.
- Gõ tên việc (Ví dụ: *Cài đặt phần mềm đọc PDF Acrobat*).
- Nhấp nút **"+ Thêm Đầu Việc"** (hoặc nhấn phím Enter). Đầu việc mới lập tức xuất hiện trong danh sách.

#### Bước 6.2: Đánh dấu đã làm xong
- Khi làm xong bước nào trong thực tế, bạn chỉ cần nhấp chuột vào ô vuông **[ ]** đầu dòng việc đó.
- Ô vuông chuyển thành dấu tích xanh **[x]**, chữ bị gạch ngang và thanh tiến độ màu xanh lá sẽ tự động tăng % lên (Ví dụ từ 50% lên 75%).

#### Bước 6.3: Xóa đầu việc tạo nhầm
- Nhấp chuột vào biểu tượng thùng rác màu đỏ `[🗑️]` ở cuối dòng việc cần xóa.

---

## 7. TRAO ĐỔI PHẢN HỒI CÔNG KHAI & GHI CHÚ NỘI BỘ BÍ MẬT (INTERNAL NOTE)

### Mục đích:
Phân định rõ ràng giữa tin nhắn gửi cho người dùng xem và ghi chú kỹ thuật bảo mật chỉ nội bộ nhóm IT đọc được (mật khẩu tạm, cấu hình IP, nghi vấn người dùng thao tác sai...).

### Các bước thao tác bấm chuột:

```text
GIAO DIỆN KHUNG BÌNH LUẬN TRÊN PHIẾU
+-----------------------------------------------------------------------------------------+
| Nhập nội dung phản hồi:                                                                 |
| [ Đã reset mật khẩu domain tạm thời là: Admin@1234. Cần hướng dẫn user đổi lại mật   ]  |
| [ khẩu ngay khi đăng nhập.                                                            ] |
|                                                                                         |
| Đính kèm tệp: [ Chọn tệp ]                                                              |
|                                                                                         |
| [🔘 BẬT CÔNG TẮC] Ghi chú nội bộ (Internal Note) - Chỉ IT nhìn thấy                      |
|  ↖ (BẤM VÀO ĐÂY ĐỂ BẬT CÔNG TẮC MÀU VÀNG CAM NÀY LÊN)                                   |
|                                                                                         |
|                                              [  GỬI BÌNH LUẬN NỘI BỘ  ]                 |
+-----------------------------------------------------------------------------------------+
```

#### Bước 7.1: Gửi tin nhắn công khai cho người dùng
- Nhập nội dung vào ô text.
- **Giữ công tắc "Ghi chú nội bộ" ở trạng thái TẮT**.
- Bấm nút **"Gửi Phản Hồi"**. Tin nhắn sẽ hiển thị với nền trắng bình thường và người dùng sẽ đọc được.

#### Bước 7.2: Gửi Ghi chú nội bộ bí mật
- Nhập nội dung kỹ thuật mật vào ô text.
- **Nhấp chuột vào công tắc gạt "Ghi chú nội bộ (Internal Note)"** để bật công tắc sang màu vàng cam (có icon ổ khóa).
- Bấm nút **"Gửi Phản Hồi"**.
- **Kết quả:** Khung tin nhắn xuất hiện với **viền vàng đậm**, nền vàng nhạt và huy hiệu **🔒 Ghi chú nội bộ**. Người dùng cuối khi đăng nhập vào xem phiếu sẽ **hoàn toàn không thấy** đoạn tin nhắn này.

---

## 8. LIÊN KẾT THIẾT BỊ TÀI SẢN (IT ASSET LINKING) & TẠO LỖI ĐÃ BIẾT (KEDB)

### Mục đích:
Gắn máy tính/thiết bị gặp sự cố vào phiếu để sau này mở hồ sơ máy tính đó ra sẽ tra cứu được toàn bộ lịch sử các lần bị hỏng.

### Các bước thao tác bấm chuột:
- **Bước 8.1:** Tại trang chi tiết phiếu (`/tickets/{id}`), nhìn sang cột thông tin bên phải.
- **Bước 8.2 - Chọn thiết bị:**
  - Nhấp chuột vào hộp chọn **"Liên kết tài sản" (Link Asset)**.
  - Danh sách các thiết bị của người dùng hoặc máy tính công ty sẽ xổ xuống (Ví dụ: `LAPTOP-KT-02 (Dell Latitude 5420)`).
  - Chọn đúng thiết bị đang bị lỗi.
  - Bấm nút **"Lưu Liên Kết"**.
- **Bước 8.3 - Đóng góp lỗi đã biết (KEDB / Known Error Database):**
  - Nếu sự cố này có tính chất lặp lại nhiều lần trên nhiều máy tính:
  - Bấm nút **"Xuất Bản Sang Cơ Sở Tri Thức (KB)"**.
  - Hệ thống sẽ tự động điền tiêu đề và nội dung giải pháp sang màn hình biên soạn bài viết KB để bạn hoàn thiện chia sẻ cho cả phòng IT.

---

## 9. NGHIỆM THU, NHẬP GIẢI PHÁP & HOÀN THÀNH PHIẾU (RESOLVE TICKET)

### Mục đích:
Khai báo sự cố đã được khắc phục triệt để, nhập biên bản giải pháp kỹ thuật để đóng giờ tính SLA và chuyển sang cho người dùng nghiệm thu.

### Các bước thao tác bấm chuột:

```text
CỬA SỔ NHẬP GIẢI PHÁP KỸ THUẬT (RESOLVE MODAL)
+-----------------------------------------------------------------------------------------+
|                  GIẢI QUYẾT PHIẾU HỖ TRỢ: #IT-000012                                    |
|                                                                                         |
| Nguyên nhân gốc rễ (Root Cause):                                                        |
| [ Chân cắm thanh RAM 2 bị bám bụi dẫn đến tiếp xúc kém gây màn hình xanh dump.        ] |
|                                                                                         |
| Giải pháp khắc phục đã thực hiện (Resolution Note) * (Bắt buộc):                        |
| [ Đã tháo thanh RAM 8GB, vệ sinh chân tiếp xúc bằng cồn chuyên dụng, cắm lại chặt     ] |
| [ khe cắm số 2. Chạy thử phần mềm MemTest64 trong 20 phút không còn phát sinh lỗi.   ] |
|                                                                                         |
|            [  HỦY BỎ  ]                     [  ✅ XÁC NHẬN GIẢI QUYẾT PHIẾU  ]          |
|                                              ↖ (BẤM VÀO ĐÂY ĐỂ HOÀN TẤT)                |
+-----------------------------------------------------------------------------------------+
```

#### Bước 9.1: Bấm nút Giải quyết
- Nhấp chuột vào nút màu xanh lá cây **"✅ Giải Quyết Phiếu" (Resolve Ticket)** ở đầu trang.

#### Bước 9.2: Nhập nội dung giải pháp
- Hộp thoại bật lên:
  - Ô **"Nguyên nhân gốc rễ"**: Nhập ngắn gọn nguyên nhân sự cố.
  - Ô **"Giải pháp khắc phục"**: Bắt buộc nhập chi tiết các việc bạn đã làm để sửa máy.
- Nhấp chuột vào nút **"XÁC NHẬN GIẢI QUYẾT PHIẾU"**.
- **Kết quả:** Trạng thái phiếu đổi sang **RESOLVED**, bộ đếm SLA dừng lại và hiển thị huy hiệu xanh **Đạt SLA**. Người dùng sẽ nhận được email/thông báo để vào kiểm tra máy tính và chấm điểm sao dịch vụ cho bạn.

---

## 10. QUẢN TRỊ VÒNG ĐỜI TÀI SẢN CNTT (ITAM: BÀN GIAO, BẢO HÀNH, SỬA CHỮA)

### Mục đích:
Quản lý toàn bộ danh mục máy tính để bàn, laptop, màn hình, máy in, thiết bị mạng; ghi nhận tình trạng bàn giao cho nhân viên nào và theo dõi bảo hành.

### Các bước thao tác bấm chuột:
- **Bước 10.1:** Trên thanh menu bên trái, nhấp chuột vào mục **"Quản Lý Tài Sản"** (hoặc truy cập `http://localhost:3000/assets`).
- **Bước 10.2 - Thêm tài sản mới nhập kho:**
  - Nhấp nút **"+ Thêm Tài Sản Mới"**.
  - Điền: Mã tài sản (Ví dụ: `AST-00105`), Tên thiết bị (`Laptop Dell Inspiron 15`), Loại thiết bị (`Laptop`), Số Serial (`SN-882910J`), Cấu hình (`Core i5, 16GB RAM, 512GB SSD`).
  - Bấm **"Lưu Tài Sản"**.
- **Bước 10.3 - Bàn giao cho nhân viên:**
  - Tại dòng của thiết bị trong danh sách, bấm nút **"Bàn Giao" (Assign)**.
  - Chọn nhân viên nhận máy (Ví dụ: *Nguyễn Thị Lan - Kế toán*) > Bấm **"Xác Nhận Bàn Giao"**.
- **Bước 10.4 - Chuyển đi sửa chữa / Bảo hành:**
  - Khi máy tính bị hỏng nặng cần gửi hãng: Nhấp nút **"Đổi Trạng Thái"** > Chọn `REPAIR` (Đang sửa chữa) > Nhập tên trung tâm bảo hành > Bấm **Lưu**.
- **Bước 10.5 - Xuất danh sách tài sản ra Excel:**
  - Nhấp nút màu xanh lá **"Xuất Excel"** ở góc phải trên cùng để tải toàn bộ bảng kê tài sản về máy tính.
