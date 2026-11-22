# SỔ TAY HƯỚNG DẪN THAO TÁC TỪNG BƯỚC: NGƯỜI DÙNG CUỐI (END-USER)
## Dành Cho Cán Bộ Nhân Viên Các Phòng Ban (Kế toán, Kinh doanh, Nhân sự, Marketing...)

---

## MỤC LỤC CHI TIẾT
1. [Đăng Nhập Hệ Thống & Cập Nhật Hồ Sơ Chỗ Ngồi (Profile)](#1-đăng-nhập-hệ-thống--cập-nhật-hồ-sơ-chỗ-ngồi-profile)
2. [Hỏi Đáp Sự Cố IT Tức Thì Với AI Chatbot Popup (Góc Trái Dưới)](#2-hỏi-đáp-sự-cố-it-tức-thì-với-ai-chatbot-popup-góc-trái-dưới)
3. [Tra Cứu & Đọc Hướng Dẫn Tự Sửa Lỗi Trên Cơ Sở Tri Thức (KB)](#3-tra-cứu--đọc-hướng-dẫn-tự-sửa-lỗi-trên-cơ-sở-tri-thức-kb)
4. [Tạo Phiếu Hỗ Trợ Kỹ Thuật Nhanh (Form Báo Hỏng Tiêu Chuẩn)](#4-tạo-phiếu-hỗ-trợ-kỹ-thuật-nhanh-form-báo-hỏng-tiêu-chuẩn)
5. [Đăng Ký Cấp Thiết Bị & Phần Mềm Qua Danh Mục Dịch Vụ (Service Catalog)](#5-đăng-ký-cấp-thiết-bị--phần-mềm-qua-danh-mục-dịch-vụ-service-catalog)
6. [Xem Trực Tiếp File Đính Kèm (Word, Excel, PDF, Hình Ảnh) Không Cần Tải](#6-xem-trực-tiếp-file-đính-kèm-word-excel-pdf-hình-ảnh-không-cần-tải)
7. [Theo Dõi Đồng Hồ SLA, Trao Đổi Phản Hồi & Đính Kèm Ảnh Mới](#7-theo-dõi-đồng-hồ-sla-trao-đổi-phản-hồi--đính-kèm-ảnh-mới)
8. [Xác Nhận Hoàn Thành Đóng Phiếu & Chấm Điểm Sao Dịch Vụ (CSAT)](#8-xác-nhận-hoàn-thành-đóng-phiếu--chấm-điểm-sao-dịch-vụ-csat)
9. [Mở Lại Phiếu (Reopen Ticket) Khi Sự Cố Tái Diễn](#9-mở-lại-phiếu-reopen-ticket-khi-sự-cố-tái-diễn)

---

## 1. ĐĂNG NHẬP HỆ THỐNG & CẬP NHẬT HỒ SƠ CHỖ NGỒI (PROFILE)

### Mục đích:
Đăng nhập vào hệ thống và khai báo trước **Phòng ban, Chỗ ngồi, Số điện thoại nội bộ**. Khi bạn tạo phiếu hỗ trợ, hệ thống sẽ **tự động điền sẵn** thông tin này giúp bạn không phải mất công nhập lại.

### Các bước thao tác bấm chuột:

#### Bước 1.1: Truy cập trang đăng nhập
- Mở trình duyệt web (Google Chrome, Microsoft Edge, Cốc Cốc, Firefox).
- Nhập địa chỉ: `http://localhost:3000/login` rồi bấm phím **Enter**.

```text
+-------------------------------------------------------------------------+
|                  TASKFLOW IT SERVICE DESK & ASSET                       |
|                       Đăng Nhập Vào Hệ Thống                            |
|                                                                         |
|   Tên đăng nhập: [ user1                                            ]   |
|   Mật khẩu:      [ **********                                       ]   |
|                                                                         |
|                  [        ĐĂNG NHẬP HỆ THỐNG        ]                   |
|                                                                         |
|   Tài khoản mẫu: user1 / ketoan_lan / kinhdoanh_nam (Mật khẩu: 123456)  |
+-------------------------------------------------------------------------+
```

- Bấm chuột vào ô **"Tên đăng nhập"**: Nhập tên tài khoản (Ví dụ: `user1` hoặc `ketoan_lan`).
- Bấm chuột vào ô **"Mật khẩu"**: Nhập mật khẩu (Mặc định: `123456`).
- Bấm chuột vào nút màu xanh **"ĐĂNG NHẬP HỆ THỐNG"**.
- **Kết quả:** Hệ thống đăng nhập thành công và chuyển hướng đến trang Bàn Làm Việc của bạn.

#### Bước 1.2: Cập nhật thông tin Hồ sơ cá nhân (Profile)
- Nhìn lên góc trên cùng bên phải màn hình, nhấp chuột vào **Tên của bạn** (có vòng tròn Avatar chữ cái đầu).
- Một menu xổ xuống hiện ra > Bấm chuột vào dòng **"Hồ sơ của tôi" (Profile)** hoặc truy cập link: `http://localhost:3000/profile`.

```text
+-------------------------------------------------------------------------+
| HỒ SƠ NGƯỜI DÙNG: ĐỖ MAI PHƯƠNG                                         |
|                                                                         |
| Họ và tên:         [ Đỗ Mai Phương                                ]     |
| Email công ty:     [ phuong.dm@company.local                      ]     |
| Phòng ban:         [ Marketing                                  ▼ ]     |
| Vị trí chỗ ngồi:   [ Bàn 12, Dãy C, Tầng 3                        ]     |
| Số điện thoại:     [ 0988123456 (Số máy lẻ: 104)                  ]     |
|                                                                         |
|                    [    LƯU THAY ĐỔI HỒ SƠ    ]                         |
+-------------------------------------------------------------------------+
```

- Bấm chuột vào ô **"Vị trí chỗ ngồi"**: Nhập vị trí cụ thể của bạn (Ví dụ: *Bàn 04, Lầu 1, Phòng Kế Toán*).
- Bấm chuột vào ô **"Số điện thoại"**: Nhập số điện thoại di động hoặc số máy lẻ nội bộ.
- Bấm chuột vào nút màu xanh **"LƯU THAY ĐỔI HỒ SƠ"**.
- **Kết quả:** Xuất hiện thông báo xanh *"Đã cập nhật hồ sơ cá nhân thành công!"*.

---

## 2. HỎI ĐÁP SỰ CỐ IT TỨC THÌ VỚI AI CHATBOT POPUP (GÓC TRÁI DƯỚI)

### Mục đích:
Hỏi đáp trực tiếp với Trợ lý AI để tự xử lý nhanh các vấn đề máy tính văn phòng (Mất mạng, kẹt máy in, lỗi Outlook, đổi mật khẩu, dọn ổ C) mà không cần chờ kỹ thuật viên.

### Các bước thao tác bấm chuột:

```text
MÀN HÌNH HỆ THỐNG (BẤT KỲ TRANG NÀO HOẶC TRANG CƠ SỞ TRI THỨC)
+-------------------------------------------------------------------------+
|                                                                         |
|   +---------------------------------------------------------------+     |
|   | 🤖 AI Trợ Lý Tri Thức & Kỹ Thuật IT               [↺] [⤢] [✕] |     |
|   | • Trực tuyến 24/7 • Sẵn sàng hỗ trợ bạn                       |     |
|   +---------------------------------------------------------------+     |
|   | [AI]: Xin chào Đỗ Mai Phương! 👋                              |     |
|   |       Hãy đặt câu hỏi về kỹ thuật hoặc chọn câu hỏi nhanh:    |     |
|   |                                                               |     |
|   | Gợi ý nhanh:                                                  |     |
|   | [ Bấm: 🔐 Đổi mật khẩu ]  [ Bấm: 🌐 Kết nối VPN ]             |     |
|   | [ Bấm: 🖨️ Máy in mạng  ]  [ Bấm: ✉️ Sửa lỗi Outlook ]         |     |
|   | [ Bấm: 💾 Dọn dẹp ổ C  ]  [ Bấm: 🎫 Cách tạo ticket ]         |     |
|   +---------------------------------------------------------------+     |
|   | [ Nhập câu hỏi kỹ thuật của bạn vào đây...         ] [ Gửi ✈ ]|     |
|   +---------------------------------------------------------------+     |
|                                                                         |
| [🤖] AI Trợ Lý Tri Thức [★ IT]                                          |
| ↖ (BẤM VÀO ĐÂY ĐỂ BẬT / TẮT CỬA SỔ CHAT - NẰM Ở GÓC TRÁI DƯỚI)           |
+-------------------------------------------------------------------------+
```

#### Bước 2.1: Mở cửa sổ Chatbot
- Nhìn xuống **góc trái dưới màn hình**, bạn sẽ thấy nút tròn màu xanh tím **"AI Trợ Lý Tri Thức"** có chấm xanh lá nhấp nháy.
- Nhấp chuột trái vào nút này: Cửa sổ Chatbot sẽ lập tức bật lên.

#### Bước 2.2: Chọn câu hỏi nhanh hoặc nhập câu hỏi
- **Cách 1 - Bấm câu hỏi mẫu:** Nhấp chuột vào một trong các nút gợi ý có sẵn:
  - Bấm vào nút `🔐 Đổi mật khẩu Windows`
  - Bấm vào nút `🌐 Kết nối VPN từ xa`
  - Bấm vào nút `🖨️ Kết nối máy in văn phòng`
  - Bấm vào nút `✉️ Lỗi Outlook Disconnected`
  - Bấm vào nút `💾 Dọn dẹp ổ đĩa C`
  - Bấm vào nút `🎫 Cách tạo phiếu hỗ trợ IT`
- **Cách 2 - Gõ câu hỏi tùy ý:**
  - Nhấp chuột vào ô nhập chữ ở đáy cửa sổ: *"Hỏi về sự cố IT, mật khẩu, mạng, máy in, hệ thống..."*.
  - Gõ câu hỏi của bạn (Ví dụ: *"Máy in tầng 2 bị kẹt giấy xử lý thế nào?"* hoặc *"Làm sao kết nối WiFi công ty?"*).
  - Bấm nút **Gửi (biểu tượng máy bay giấy)** hoặc nhấn phím **Enter**.

#### Bước 2.3: Nhận giải pháp và liên kết bài viết
- Trợ lý AI sẽ trả lời sau 1 giây với định dạng rõ ràng (Bước 1, Bước 2, Bước 3).
- Nếu trong hệ thống có bài viết liên quan, AI sẽ hiển thị đường link màu xanh `[📖 Xem bài viết hướng dẫn chi tiết]`. Bạn nhấp chuột vào link để mở bài viết.
- **Phóng to cửa sổ:** Bấm nút biểu tượng phóng to `[⤢]` ở góc phải thanh tiêu đề chat để mở rộng kích thước dễ đọc.
- **Thu nhỏ cửa sổ:** Bấm nút dấu gạch ngang `[-]` hoặc nhấp lại nút tròn góc trái dưới để ẩn cửa sổ chat.

---

## 3. TRA CỨU & ĐỌC HƯỚNG DẪN TỰ SỬA LỖI TRÊN CƠ SỞ TRI THỨC (KB)

### Mục đích:
Tìm kiếm thư viện bài viết hướng dẫn sử dụng phần mềm, cài đặt thiết bị và các quy định CNTT của công ty.

### Các bước thao tác bấm chuột:
- **Bước 3.1:** Trên thanh menu bên trái, nhấp chuột vào mục **"Cơ Sở Tri Thức"** (hoặc truy cập `http://localhost:3000/kb`).
- **Bước 3.2 - Tìm kiếm:**
  - Nhấp chuột vào ô tìm kiếm lớn ở giữa trang: *"Ví dụ: Đổi mật khẩu, lỗi in, vpn, wifi..."*.
  - Gõ từ khóa cần tìm (Ví dụ: `vpn`) rồi bấm nút màu cam **"Tìm Kiếm"**.
- **Bước 3.3 - Chọn theo danh mục:**
  - Nhấp chuột vào một trong các thẻ danh mục:
    - Thẻ *Mạng & Kết Nối Từ Xa*
    - Thẻ *Máy In & Thiết Bị Ngoại Vi*
    - Thẻ *Tài Khoản & Đăng Nhập*
    - Thẻ *Phần Mềm Văn Phòng & Email*
- **Bước 3.4 - Mở đọc bài viết:**
  - Nhấp chuột vào tiêu đề bài viết (Ví dụ: *"Hướng dẫn Cài đặt và Kết nối VPN làm việc từ xa"*).
  - Đọc nội dung hướng dẫn từng bước và hình ảnh minh họa.
- **Bước 3.5 - Đánh giá bài viết:**
  - Cuối bài viết, nhấp nút màu xanh **"👍 Bài viết hữu ích"** để ghi nhận chất lượng cho tác giả.

---

## 4. TẠO PHIẾU HỖ TRỢ KỸ THUẬT NHANH (FORM BÁO HỎNG TIÊU CHUẨN)

### Mục đích:
Báo cáo các sự cố hỏng hóc phát sinh đột xuất (Mất mạng, hỏng chuột, lỗi máy in, màn hình không lên...) để kỹ thuật viên IT tiếp nhận xử lý.

### Các bước thao tác bấm chuột:

#### Bước 4.1: Mở form tạo phiếu
- Có 2 vị trí bấm:
  - **Vị trí 1:** Bấm nút màu xanh **"+ Tạo Phiếu Mới"** trên thanh Menu bên trái.
  - **Vị trí 2:** Bấm nút màu xanh **"+ Tạo Phiếu"** ở góc phải trên thanh Topbar.
- Hệ thống mở ra màn hình tạo phiếu: `http://localhost:3000/tickets/create`.

#### Bước 4.2: Kiểm tra thông tin liên hệ (Đã tự động điền)
- Quan sát thẻ **"Vị trí & Liên hệ"**:
  - Tên của bạn: Đã điền sẵn.
  - Phòng ban: Đã điền sẵn.
  - Chỗ ngồi: Đã điền sẵn từ Profile.
  - *Nếu bạn đang ngồi ở một vị trí khác (Ví dụ sang phòng họp tầng 4), bạn chỉ cần nhấp chuột vào ô và sửa lại.*

#### Bước 4.3: Chọn phân loại sự cố
- Bấm chuột vào danh sách chọn **"Danh mục sự cố" (Category)**:
  - Chọn `Mạng, WiFi & Kết Nối Internet` (nếu mất mạng, lỗi VPN)
  - Chọn `Thiết Bị Ngoại Vi & Máy In` (nếu máy in, chuột, bàn phím hỏng)
  - Chọn `Tài Khoản & Đăng Nhập` (nếu lỗi mật khẩu, khóa tài khoản)
  - Chọn `Phần Mềm & Ứng Dụng Văn Phòng` (nếu lỗi Word, Excel, Outlook, ERP)

#### Bước 4.4: Chọn mức độ ưu tiên
- Nhấp chọn một trong 3 nút:
  - ⚪ **Tiêu chuẩn (Normal):** Máy tính vẫn dùng được việc khác, không vội.
  - 🟡 **Cao (High):** Đang làm việc gấp cần xử lý trong buổi sáng/buổi chiều.
  - 🔴 **Khẩn cấp (Urgent):** Sự cố nghiêm trọng ảnh hưởng cả phòng hoặc máy sếp lớn.

#### Bước 4.5: Nhập Tiêu đề & Mô tả chi tiết
- Bấm vào ô **"Tiêu đề phiếu"**: Gõ ngắn gọn (Ví dụ: *"Máy in Canon tầng 3 báo lỗi kẹt giấy không in được"*).
- Bấm vào ô **"Mô tả chi tiết"**: Gõ rõ hiện tượng (Ví dụ: *"Lúc 09h15 em bấm in tài liệu thì máy báo đèn cam nhấp nháy, màn hình hiện Paper Jam. Đã mở nắp kiểm tra nhưng không thấy giấy kẹt."*).

#### Bước 4.6: Đính kèm hình ảnh chụp lỗi
- Nhấp chuột vào nút **"Chọn tệp"** tại mục Tệp đính kèm.
- Cửa sổ máy tính hiện lên > Tìm đến file ảnh chụp lỗi màn hình (PNG, JPG) hoặc file văn bản lỗi > Bấm **Open**.

#### Bước 4.7: Gửi phiếu
- Nhấp chuột vào nút màu xanh **"GỬI YÊU CẦU HỖ TRỢ"** ở đáy trang.
- **Kết quả:** Hệ thống lập tức sinh mã phiếu (Ví dụ: `#IT-000045`), khởi động đồng hồ đo thời gian cam kết SLA và chuyển bạn đến trang chi tiết phiếu.

---

## 5. ĐĂNG KÝ CẤP THIẾT BỊ & PHẦN MỀM QUA DANH MỤC DỊCH VỤ (SERVICE CATALOG)

### Mục đích:
Đăng ký các nhu cầu nghiệp vụ mới cần được sếp phê duyệt trước khi IT bàn giao (Cấp Laptop mới, cấp màn hình phụ, mua phần mềm bản quyền, xin quyền truy cập thư mục mật).

### Các bước thao tác bấm chuột:
- **Bước 5.1:** Trên thanh menu bên trái, nhấp chuột vào mục **"Danh Mục Dịch Vụ"** (hoặc truy cập `http://localhost:3000/catalog`).
- **Bước 5.2 - Lựa chọn gói dịch vụ:**
  - Nhìn vào các nhóm thẻ dịch vụ hiển thị trên màn hình:
    - Gói 1: *Cấp phát máy tính xách tay (Laptop tiêu chuẩn)*
    - Gói 2: *Cấp màn hình làm việc phụ 24 inch*
    - Gói 3: *Cài đặt phần mềm thiết kế Adobe / AutoCAD*
    - Gói 4: *Mở quyền truy cập thư mục dùng chung Server NAS*
  - Nhấp chuột vào nút **"Đăng Ký Dịch Vụ"** tại gói bạn cần.
- **Bước 5.3 - Nhập thông tin đề xuất:**
  - Nhập lý do công việc (Ví dụ: *"Nhân viên mới thử việc cần máy tính làm việc"* hoặc *"Cần màn hình phụ để đối chiếu số liệu bảng cân đối kế toán"*).
  - Bấm nút **"Gửi Yêu Cầu Đăng Ký"**.
- **Kết quả:** Yêu cầu chuyển sang trạng thái `PENDING_APPROVAL` gửi thẳng đến tài khoản của Trưởng phòng/Quản lý IT để xét duyệt. Bạn có thể theo dõi xem sếp đã duyệt chưa tại danh sách phiếu của mình.

---

## 6. XEM TRỰC TIẾP FILE ĐÍNH KÈM (WORD, EXCEL, PDF, HÌNH ẢNH) KHÔNG CẦN TẢI

### Mục đích:
Hệ thống cho phép bạn **xem trước toàn bộ nội dung file Word, bảng tính Excel, tệp PDF và ảnh** ngay trong cửa sổ popup của trình duyệt web chỉ bằng 1 cú nhấp chuột.

### Các bước thao tác bấm chuột:

```text
GIAO DIỆN TRANG CHI TIẾT TICKET -> MỤC TỆP ĐÍNH KÈM
+-------------------------------------------------------------------------+
| 📎 Tệp đính kèm (2)         Hỗ trợ xem trực tiếp Word, Excel, PDF, Text  |
|                                                                         |
| +----------------------------------+ +--------------------------------+ |
| | [📄 Word]                        | | [📊 Excel]                     | |
| | Bien_ban_nghiem_thu.docx         | | Bang_ke_thiet_bi_2026.xlsx     | |
| | 34.5 KB • Lê Quốc Bảo            | | 18.2 KB • Nguyễn Thị Lan       | |
| | -------------------------------- | | ------------------------------ | |
| | [ 👁️ Xem trực tiếp ]  [ 💾 Tải về]| | [ 👁️ Xem trực tiếp ] [ 💾 Tải]| |
| |       ↖ (BẤM VÀO ĐÂY)            | |                                | |
| +----------------------------------+ +--------------------------------+ |
+-------------------------------------------------------------------------+
```

#### Bước 6.1: Nhấp nút xem trực tiếp
- Tại trang chi tiết phiếu (`/tickets/{id}`), cuộn chuột xuống phần **"Tệp đính kèm"**.
- Bạn sẽ thấy các thẻ tệp có icon màu sắc phân biệt (Word màu xanh dương, Excel màu xanh lá, PDF màu đỏ, Ảnh màu hồng).
- Nhấp chuột trái vào nút **"👁️ Xem trực tiếp"** màu xanh trên thẻ file.

#### Bước 6.2: Trải nghiệm xem file trong cửa sổ Modal
Cửa sổ xem trước phóng to ra giữa màn hình:
- **Nếu là tệp Word (`.docx`):** Màn hình hiển thị nguyên vẹn trang giấy A4 Word có đầy đủ tiêu đề in đậm, bảng biểu, danh sách chấm đầu dòng.
- **Nếu là tệp Excel (`.xlsx`):** Màn hình hiển thị bảng tính Excel có kẻ ô dòng cột rõ ràng. Dưới đáy cửa sổ có các tab `[Trang tính 1]`, `[Trang tính 2]` để bạn nhấp chuyển Sheet.
- **Nếu là tệp PDF (`.pdf`):** Hiển thị tài liệu PDF với thanh cuộn trang và nút in.
- **Nếu là Hình ảnh (`.png, .jpg`):** Có thanh công cụ trên cùng:
  - Bấm nút `[ 🔍+ ]` để phóng to ảnh.
  - Bấm nút `[ 🔍- ]` để thu nhỏ ảnh.
  - Bấm nút `[ ↻ ]` để xoay ảnh 90 độ nếu ảnh bị chụp ngược.
- **Nếu là tệp Văn bản (`.txt, .log`):** Có nút **"Sao chép" (Copy)** để copy toàn bộ nội dung văn bản vào máy.

#### Bước 6.3: Thao tác bổ sung
- Muốn mở file ra một tab riêng biệt của trình duyệt: Bấm nút **"Tab mới"** ở góc phải trên.
- Muốn lưu tệp gốc về máy tính: Bấm nút **"Tải về"**.
- Muốn đóng cửa sổ xem: Bấm dấu **[✕]** hoặc bấm ra ngoài màn hình.

---

## 7. THEO DÕI ĐỒNG HỒ SLA, TRAO ĐỔI PHẢN HỒI & ĐÍNH KÈM ẢNH MỚI

### Mục đích:
Cùng kỹ thuật viên trao đổi các bước xử lý và kiểm soát thời gian kỹ thuật viên thực hiện có đúng cam kết hay không.

### Các bước thao tác bấm chuột:

#### Bước 7.1: Quan sát đồng hồ SLA đếm ngược
- Mở phiếu hỗ trợ của bạn: `http://localhost:3000/tickets/{id}`.
- Nhìn vào hộp trạng thái SLA bên phải màn hình:
  - 🟢 **Huy hiệu màu xanh lá:** Đang trong hạn an toàn (Ví dụ: `Còn 01:45:20`). Thời gian sẽ nhảy lùi từng giây theo thời gian thực.
  - 🟡 **Huy hiệu màu vàng:** Kỹ thuật viên sắp hết hạn cam kết.
  - 🔴 **Huy hiệu màu đỏ:** Đã quá hạn cam kết SLA.
  - ⏸️ **Huy hiệu màu cam "Tạm dừng":** Kỹ thuật viên đang chờ bạn phản hồi hoặc đang đợi đối tác chuyển linh kiện thay thế đến.

#### Bước 7.2: Nhắn tin trao đổi với Kỹ thuật viên
- Cuộn xuống phần tab **"Bình luận" (Comments)**.
- Nhấp chuột vào ô văn bản: *"Nhập câu trả lời hoặc trao đổi thêm..."*.
- Gõ nội dung (Ví dụ: *"Em đã cắm lại dây nguồn theo hướng dẫn của anh nhưng đèn vẫn không sáng."*).
- **Đính kèm thêm ảnh mới:** Bấm nút **"Chọn tệp"** bên dưới ô nhập để đính kèm thêm ảnh chụp mới.
- Nhấp nút màu xanh **"Gửi Phản Hồi"**.
- **Kết quả:** Bình luận của bạn xuất hiện ngay trên dòng thời gian và kỹ thuật viên sẽ nhận được thông báo chuông tức thì.

---

## 8. XÁC NHẬN HOÀN THÀNH ĐÓNG PHIẾU & CHẤM ĐIỂM SAO DỊCH VỤ (CSAT)

### Mục đích:
Khi kỹ thuật viên đã xử lý xong và chuyển trạng thái phiếu sang `RESOLVED`, bạn cần xác nhận nghiệm thu và chấm điểm sao đánh giá dịch vụ.

### Các bước thao tác bấm chuột:

```text
MÀN HÌNH XÁC NHẬN HOÀN THÀNH & ĐÁNH GIÁ DỊCH VỤ
+-------------------------------------------------------------------------+
| Phiếu hỗ trợ đã được Kỹ thuật viên xử lý xong!                          |
| Trạng thái hiện tại: [ ĐÃ GIẢI QUYẾT (RESOLVED) ]                       |
| Giải pháp của IT: Đã thay cáp mạng mới và bấm lại hạt mạng RJ45         |
|                                                                         |
| Bạn có hài lòng với kết quả xử lý này không?                            |
|                                                                         |
|            [  🔒 XÁC NHẬN HOÀN THÀNH & ĐÓNG PHIẾU  ]                    |
|            ↖ (BẤM VÀO ĐÂY NẾU MÁY TÍNH ĐÃ CHẠY TỐT)                     |
+-------------------------------------------------------------------------+
| CỬA SỔ ĐÁNH GIÁ CHẤT LƯỢNG (CSAT RATING):                               |
|                                                                         |
| Hãy đánh giá mức độ hài lòng của bạn:                                   |
|               ⭐   ⭐   ⭐   ⭐   ⭐                                   |
|             (Rất không hài lòng  ->  Rất hài lòng 5 sao)                |
|                                                                         |
| Nhận xét của bạn:                                                       |
| [ Kỹ thuật viên Lê Quốc Bảo hỗ trợ rất nhanh và nhiệt tình. Cảm ơn IT!] |
|                                                                         |
|                     [    GỬI ĐÁNH GIÁ DỊCH VỤ    ]                      |
+-------------------------------------------------------------------------+
```

#### Bước 8.1: Bấm xác nhận hoàn thành
- Kiểm tra lại máy tính/phần mềm. Nếu đã hoạt động bình thường:
- Bấm nút màu xanh lá cây **"🔒 Xác Nhận Hoàn Thành & Đóng Phiếu"** ở đầu trang chi tiết phiếu.

#### Bước 8.2: Chấm sao đánh giá
- Cửa sổ đánh giá hiện lên:
  - Nhấp chuột vào ngôi sao thứ 5 để chọn **5 Sao (Rất hài lòng)**.
  - Bấm vào ô nhận xét: Gõ lời cảm ơn hoặc góp ý (nếu có).
  - Bấm nút **"GỬI ĐÁNH GIÁ DỊCH VỤ"**.
- **Kết quả:** Trạng thái phiếu chính thức đổi sang **CLOSED** (Đã đóng), điểm số được cộng vào thành tích KPI của kỹ thuật viên.

---

## 9. MỞ LẠI PHIẾU (REOPEN TICKET) KHI SỰ CỐ TÁI DIỄN

### Mục đích:
Nếu kỹ thuật viên báo đã xong nhưng khi bạn kiểm tra lại lỗi vẫn còn y nguyên, bạn có quyền mở lại phiếu để yêu cầu xử lý tiếp mà không cần phải tạo phiếu mới.

### Các bước thao tác bấm chuột:
- **Bước 9.1:** Tại trang chi tiết phiếu đang ở trạng thái `RESOLVED`:
- **Bước 9.2:** Nhấp chuột vào nút màu cam **"🔄 Mở Lại Phiếu" (Reopen Ticket)** (nằm cạnh nút Đóng phiếu).
- **Bước 9.3:** Hộp thoại hiện ra yêu cầu nhập lý do:
  - Bấm vào ô nhập: Gõ rõ lý do (Ví dụ: *"Máy in vẫn báo đèn cam kẹt giấy, em khởi động lại máy in 2 lần vẫn không in được."*).
- **Bước 9.4:** Bấm nút **"Xác Nhận Mở Lại"**.
- **Kết quả:** Trạng thái phiếu tự động chuyển thành **REOPENED**, đồng hồ SLA kích hoạt lại và phiếu được đẩy ngược lại hàng đợi ưu tiên của kỹ thuật viên phụ trách.
