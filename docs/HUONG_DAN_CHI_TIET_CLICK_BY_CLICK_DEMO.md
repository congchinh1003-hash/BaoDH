# HƯỚNG DẪN CHI TIẾT TỪNG BƯỚC DEMO HỆ THỐNG TASKFLOW ITSM & AGILE
## (TÀI LIỆU CẨM NANG NGHIỆP VỤ & CẦM TAY CHỈ VIỆC DÀNH CHO NGƯỜI MỚI BẮT ĐẦU)

---

## 📖 GIỚI THIỆU TỔNG QUAN

Tài liệu này được thiết kế theo phương pháp **"Cầm tay chỉ việc kết hợp giải thích nghiệp vụ"**. Mỗi bước đều làm rõ 4 phần:
1. 🎯 **Mục đích của bước:** Trả lời câu hỏi *"Tôi đang làm bước này để đạt được điều gì?"*
2. 💼 **Ý nghĩa nghiệp vụ thực tế:** Trả lời câu hỏi *"Tại sao doanh nghiệp lại cần bước này? Chuẩn quản lý quốc tế (ITIL / Agile / ISO 27001) áp dụng ra sao?"*
3. 🖱️ **Thao tác chi tiết (Click-by-click):** Hướng dẫn chính xác từng nút bấm, màu sắc, vị trí rê chuột, nội dung cần điền.
4. 👁️ **Kết quả quan sát được:** Màn hình thay đổi thế nào, dữ liệu hiển thị ra sao để người demo biết mình đã làm đúng.

---

## ⚙️ I. CHUẨN BỊ MÔI TRƯỜNG TRƯỚC KHI DEMO

1. **Địa chỉ truy cập Web:** Mở trình duyệt Chrome / Edge / Cốc Cốc và truy cập:
   👉 **`http://localhost:3000`**
2. **Mật khẩu dùng chung cho TẤT CẢ tài khoản:**
   👉 **`123456`**
3. **Cách đổi giữa các vai trò người dùng trong lúc demo:**
   - Khi muốn đổi sang vai trò khác: Nhìn lên **góc trên cùng bên phải màn hình** ➔ Bấm vào **Vòng tròn Avatar có chữ cái đại diện** (chữ `P`, `L`, `B`, `H`, `A`) ➔ Chọn dòng màu đỏ dưới cùng **"Đăng xuất"** ➔ Hệ thống quay về màn hình đăng nhập để chọn vai trò tiếp theo.

---

## 👤 II. KỊCH BẢN 1: DÀNH CHO NGƯỜI DÙNG DOANH NGHIỆP (END USER)
*Đóng vai: Chị Đỗ Mai Phương (`user1`) - Nhân viên văn phòng phòng Kế toán*

---

### Bước 1: Đăng nhập vào không gian làm việc của nhân viên
- 🎯 **Mục đích:** Đăng nhập vào hệ thống để tiếp cận các dịch vụ công nghệ thông tin của công ty.
- 💼 **Ý nghĩa nghiệp vụ:** 
  - **Xác thực danh tính (Authentication):** Đảm bảo an toàn thông tin doanh nghiệp, xác định đúng nhân viên để hệ thống tự động gán phòng ban, chức vụ và quyền hạn tương ứng.
  - **Cơ chế 1-Click Demo:** Hệ thống tích hợp sẵn bảng 37 tài khoản thuộc nhiều phòng ban giúp người thuyết trình hoặc khách hàng thử nghiệm ngay lập tức mà không mất công tạo dữ liệu từ đầu.
- 🖱️ **Thao tác chi tiết:**
  1. Mở trình duyệt vào: `http://localhost:3000/login`.
  2. Dưới form đăng nhập, tại mục **"Đăng nhập nhanh theo vai trò"**, bạn thấy 4 ô vuông:
     - Ô 1: `Admin`
     - Ô 2: `Manager`
     - Ô 3: `IT Staff`
     - **Ô 4: `End User (Đỗ Mai Phương)`**
  3. **Bấm chuột vào Ô thứ 4: "End User"!** (Hệ thống tự động điền tài khoản `user1` và mật khẩu `123456`).
  4. Bấm nút màu xanh to **"Đăng nhập hệ thống"** (hoặc nhấn phím `Enter`).
- 👁️ **Kết quả quan sát được:**
  - Màn hình chuyển vào **Dashboard người dùng** (`http://localhost:3000/dashboard`).
  - Lời chào thân thiện: *"Chào buổi sáng/chiều, Đỗ Mai Phương! 👋"*.
  - Bảng 6 chỉ số cá nhân: *Tổng yêu cầu, Đang xử lý, Chờ bạn phản hồi, Chờ nghiệm thu, Đã hoàn thành, Quá SLA*. Phía dưới là danh sách các phiếu yêu cầu đang theo dõi.

---

### Bước 2: Kiểm tra danh mục thiết bị, máy móc công ty cấp phát cho mình
- 🎯 **Mục đích:** Nhân viên tra cứu danh sách các trang thiết bị máy móc (laptop, màn hình) mà công ty đã giao cho mình sử dụng.
- 💼 **Ý nghĩa nghiệp vụ (Quản lý Tài sản CNTT - ITAM Lifecycle):**
  - **Minh bạch tài sản:** Ràng buộc trách nhiệm cá nhân đối với tài sản được công ty trang bị (tránh việc thất lạc, làm mất mà không rõ trách nhiệm).
  - **Hỗ trợ kỹ thuật nhanh:** Khi máy tính gặp sự cố phần cứng, hệ thống đã lưu sẵn số Serial, cấu hình máy và thời hạn bảo hành để bộ phận IT lập tức đối chiếu mà không cần hỏi lại người dùng.
- 🖱️ **Thao tác chi tiết (Có 2 cách bấm):**
  - **Cách 1 (Chuẩn giao diện):** Nhìn lên **góc trên cùng bên phải màn hình** ➔ Bấm vào **Vòng tròn Avatar chữ cái (chữ P)** ➔ Bấm chọn dòng thứ 4: **"Thiết bị của tôi"** (icon laptop).
  - **Cách 2:** Nhìn lên thanh menu trên cùng ➔ Rê chuột vào chữ **"Công việc"** ➔ Bấm chọn **"Thiết bị / Tài sản của tôi"**.
  *(Đường dẫn trực tiếp: `http://localhost:3000/my-assets`)*
- 👁️ **Kết quả quan sát được:**
  - Bảng hiển thị danh sách thiết bị bàn giao cho chị Phương:
    - *Laptop Dell Latitude 5420 i5* (Mã tài sản: `AST-LAP-001`, Serial: `DELL5420X101`).
    - *Màn hình Dell UltraSharp 24 inch* (Mã tài sản: `AST-MON-001`, Serial: `DELLU24X001`).
    - Tình trạng: Màu xanh lá `Đang sử dụng (IN_USE)`.

---

### Bước 3: Tra cứu Cẩm nang kỹ thuật & Hỏi Trợ lý AI Copilot
- 🎯 **Mục đích:** Tìm bài hướng dẫn tự sửa các lỗi máy tính thông thường hoặc nhờ AI hướng dẫn tại chỗ.
- 💼 **Ý nghĩa nghiệp vụ (Cổng thông tin tự phục vụ - Self-Service Knowledge Base):**
  - **Chiến lược "Shift-Left" trong ITIL:** Khuyến khích nhân viên tự xử lý các lỗi cơ bản (như quên pass wifi, kẹt giấy máy in, lỗi gõ Unikey).
  - **Tiết kiệm tài nguyên:** Giúp giải phóng hơn 40% khối lượng công việc cho đội IT, người dùng không phải mất thời gian chờ kỹ thuật viên chạy tới bàn làm việc.
- 🖱️ **Thao tác chi tiết:**
  1. Nhìn lên menu trên cùng ➔ Rê chuột vào chữ **"Tiện ích"** ➔ Bấm chọn **"Cơ Sở Tri Thức (KB)"** (`http://localhost:3000/kb`).
  2. Gõ chữ `"hóa đơn"` hoặc `"mạng"` vào thanh tìm kiếm ➔ Bấm vào bài viết hướng dẫn mở ra đọc.
  3. Kéo xuống cuối bài viết ➔ Bấm nút màu xanh **"Hữu ích"** (icon bàn tay Like) để chấm điểm bài viết hữu ích.
- 👁️ **Kết quả quan sát được:**
  - Số lượng lượt đánh giá hữu ích tăng lên (+1 like), ghi nhận đóng góp chất lượng tài liệu.

---

### Bước 4: Gửi phiếu yêu cầu hỗ trợ sự cố mới (Create Incident Ticket)
- 🎯 **Mục đích:** Báo cáo một sự cố phần mềm khẩn cấp đến bộ phận IT để được cử người hỗ trợ.
- 💼 **Ý nghĩa nghiệp vụ (Quản lý Sự cố ITIL - Incident Management & SLA):**
  - **Chuẩn hóa tiếp nhận:** Thay vì gọi điện thoại hay nhắn tin riêng dễ trôi việc và không có bằng chứng, việc gửi ticket tạo ra một mã số theo dõi duy nhất (`#TICK-2026-xxx`).
  - **Kích hoạt cam kết dịch vụ (SLA):** Hệ thống bắt đầu tính giờ cam kết phản hồi. Cấp độ càng khẩn cấp thì thời hạn IT phải xử lý xong càng ngắn, đảm bảo hoạt động kinh doanh không bị gián đoạn.
- 🖱️ **Thao tác chi tiết:**
  1. Ngay tại trang Dashboard, bấm nút màu xanh to ở góc trên bên phải: **"Gửi Yêu Cầu Hỗ Trợ Mới"** (hoặc nút **"+ Tạo mới"** trên thanh topbar, URL: `http://localhost:3000/tickets/create`).
  2. Điền thông tin vào form:
     - **Danh mục Dịch vụ:** Bấm chọn *"Hỗ trợ Phần mềm Kế toán / Hóa đơn"*.
     - **Mức độ khẩn cấp (Priority):** Bấm chọn ô màu cam `HIGH` hoặc màu đỏ `URGENT`.
     - **Tiêu đề tóm tắt:** Nhập: `Lỗi không ký số được trên hóa đơn điện tử VAT`.
     - **Mô tả chi tiết:** Nhập: `Khi bấm ký xuất hóa đơn thì hệ thống báo không tìm thấy Token USB. Nhờ IT hỗ trợ gấp vì sắp tới hạn nộp thuế.`
     - **Đính kèm ảnh lỗi (nếu có):** Bấm chọn tệp ảnh bất kỳ trên máy để demo tính năng gửi ảnh chụp màn hình lỗi.
  3. Kéo xuống cuối trang ➔ Bấm nút màu xanh **"Gửi Phiếu Hỗ Trợ"**.
- 👁️ **Kết quả quan sát được:**
  - Thông báo màu xanh báo tạo thành công. Màn hình chuyển vào trang chi tiết phiếu (`http://localhost:3000/tickets/{id}`).
  - Mã phiếu mới xuất hiện (ví dụ: `#TICK-2026-083`), trạng thái màu xanh dương `MỚI (NEW)`.
  - Đồng hồ đếm ngược SLA bắt đầu chạy tích tắc từng phút!

---

## 🛠️ III. KỊCH BẢN 2: DÀNH CHO KỸ THUẬT VIÊN IT (IT STAFF)
*Đóng vai: Anh Lê Quốc Bảo (`it_helpdesk`) - Kỹ thuật viên IT Support Helpdesk*

---

### Bước 1: Chuyển đổi sang tài khoản Kỹ thuật viên IT
- 🎯 **Mục đích:** Đăng nhập vào không gian làm việc chuyên môn của đội kỹ thuật.
- 💼 **Ý nghĩa nghiệp vụ:** Phân quyền theo vai trò (RBAC). Kỹ thuật viên có quyền xem hàng đợi sự cố toàn công ty, quyền cập nhật trạng thái phiếu và thao tác máy chủ, những quyền mà người dùng thông thường không có.
- 🖱️ **Thao tác chi tiết:**
  1. Nhìn lên góc trên cùng bên phải ➔ Bấm vào **Vòng tròn Avatar** ➔ Chọn **"Đăng xuất"**.
  2. Tại màn hình đăng nhập ➔ Bấm vào ô nhanh thứ 3: **"IT Staff (Lê Quốc Bảo)"** ➔ Bấm **"Đăng nhập hệ thống"**.
- 👁️ **Kết quả quan sát được:** Màn hình chuyển vào giao diện làm việc của nhân viên IT.

---

### Bước 2: Vào Hàng đợi công việc và Tiếp nhận xử lý sự cố (Take Ticket)
- 🎯 **Mục đích:** Kiểm tra các sự cố mới được gửi đến và nhận phiếu về cho cá nhân xử lý.
- 💼 **Ý nghĩa nghiệp vụ (Đo lường thời gian phản hồi ban đầu - Response SLA):**
  - Khi kỹ thuật viên bấm "Nhận việc", hệ thống ghi nhận thời điểm IT bắt đầu can thiệp.
  - Đồng hồ SLA phản hồi dừng lại và đánh giá là "Đạt cam kết phản hồi". Trạng thái chuyển sang `IN_PROGRESS` để người dùng và quản lý biết phiếu đã có người thụ lý, tránh trường hợp đùn đẩy hoặc trùng lặp công việc.
- 🖱️ **Thao tác chi tiết:**
  1. Nhìn lên menu trên cùng ➔ Rê chuột vào **"Công việc"** ➔ Bấm chọn **"Hàng đợi công việc"** (`http://localhost:3000/staff/work-queue`).
  2. Bạn sẽ thấy phiếu vừa gửi ở Kịch bản 1 (`Lỗi không ký số được trên hóa đơn điện tử VAT`) nằm ngay trên đầu danh sách.
  3. Bấm vào **Tiêu đề phiếu** để mở trang chi tiết xử lý.
  4. Nhìn sang cột bên phải màn hình ➔ Bấm nút màu xanh **"Tiếp nhận" (Take Ticket)**.
- 👁️ **Kết quả quan sát được:**
  - Nhãn trạng thái đổi từ `NEW` sang màu xanh dương `ĐANG XỬ LÝ (IN_PROGRESS)`.
  - Mục Người thực hiện (Assignee) hiện tên: *Lê Quốc Bảo (Helpdesk)*.

---

### Bước 3: Thao tác kỹ thuật chuyên sâu trên phiếu (Checklist, Worklog, Link Asset)
- 🎯 **Mục đích:** Thực hiện quy trình chuẩn đoán, ghi nhận thời gian làm việc thực tế và liên kết thiết bị.
- 💼 **Ý nghĩa nghiệp vụ (Chuẩn hóa quy trình kỹ thuật & Đo lường chi phí):**
  - **Danh mục kiểm tra (Checklist):** Đảm bảo kỹ thuật viên tuân thủ đúng quy trình vận hành chuẩn (SOP), không bỏ sót các bước an toàn cơ bản.
  - **Ghi nhận thời gian (Worklog):** Cơ sở đo lường chi phí nhân công và năng suất lao động thực tế của kỹ sư IT phục vụ tính lương thưởng/KPI.
  - **Liên kết tài sản (Link Asset):** Gắn sự cố vào "bệnh án" của máy tính. Nếu 1 chiếc máy bị hỏng quá 5 lần trong tháng, hệ thống sẽ cảnh báo để IT Manager đề xuất thanh lý hoặc đổi mới.
- 🖱️ **Thao tác chi tiết (Ngay tại trang chi tiết phiếu):**
  1. **Tích Checklist:** Ở khung *"Đầu Việc Kỹ Thuật (Checklist)"*, bấm chuột vào các ô vuông:
     - [x] Đã cắm lại cổng USB Token.
     - [x] Khởi động lại service ký số.
  2. **Ghi thời gian làm:** Tìm khung *"Ghi nhận thời gian (Worklog)"* ➔ Nhập số phút: `30` ➔ Bấm **"Lưu thời gian"**.
  3. **Liên kết tài sản:** Ở mục *"Tài sản liên quan"* ➔ Bấm chọn máy tính Dell của chị Phương ➔ Bấm **"Liên kết"**.
- 👁️ **Kết quả quan sát được:**
  - Thanh tiến độ Checklist nhảy lên 100% hoàn thành.
  - Nhật ký ghi nhận thêm dòng: *Lê Quốc Bảo đã làm việc 30 phút*.

---

### Bước 4: Mở Web Terminal JumpServer PAM kiểm tra máy chủ an toàn
- 🎯 **Mục đích:** Mở cửa sổ dòng lệnh Linux kết nối vào máy chủ hóa đơn để kiểm tra tiến trình mà không cần phần mềm bên ngoài.
- 💼 **Ý nghĩa nghiệp vụ (Quản lý truy cập đặc quyền - Privileged Access Management / Bastion PAM):**
  - **Bảo mật tối cao:** Kỹ thuật viên kết nối và gõ lệnh trực tiếp trên nền web mà **không hề biết mật khẩu root**. Điều này ngăn ngừa tuyệt đối nguy cơ lộ thông tin máy chủ ra ngoài.
  - **Giám sát & Chống chối bỏ:** Mọi câu lệnh gõ trong terminal đều được hệ thống ghi hình và lưu vết kiểm toán, tuân thủ nghiêm ngặt chuẩn an ninh ngân hàng và ISO 27001.
- 🖱️ **Thao tác chi tiết:**
  1. Nhìn lên menu trên cùng ➔ Rê chuột vào **"Tiện ích"** ➔ Bấm chọn **"JumpServer Bastion PAM"** (`http://localhost:3000/jump`).
  2. Màn hình hiện danh sách các máy chủ hạ tầng.
  3. Tại dòng máy chủ `App Server - Billing Service` ➔ Bấm nút màu xanh **"Mở Web Terminal"**.
  4. Một cửa sổ dòng lệnh màu đen hiện ra ngay trên web. Bạn gõ thử:
     - Gõ lệnh: `uptime` ➔ Nhấn Enter (để xem thời gian hoạt động của máy chủ).
     - Gõ lệnh: `ps aux` ➔ Nhấn Enter (để kiểm tra các tiến trình đang chạy).
  5. Đóng tab terminal và quay lại tab chi tiết phiếu hỗ trợ.
- 👁️ **Kết quả quan sát được:** Kỹ sư đã thao tác trực tiếp trên máy chủ thành công và phiên làm việc đã được hệ thống ghi vết an toàn.

---

### Bước 5: Giải quyết sự cố và bàn giao kết quả (Resolve Ticket)
- 🎯 **Mục đích:** Ghi nhận giải pháp xử lý thành công và thông báo cho người dùng vào nghiệm thu.
- 💼 **Ý nghĩa nghiệp vụ (Đóng cam kết SLA Resolution & Lưu trữ KEDB):**
  - Đồng hồ SLA Resolution dừng lại, chốt thời gian giải quyết sự cố thực tế để tính KPI cho nhân viên IT.
  - Nội dung giải pháp được lưu vào kho tri thức để lần sau gặp lỗi tương tự có thể áp dụng lại ngay.
- 🖱️ **Thao tác chi tiết:**
  1. Quay lại trang chi tiết phiếu hỗ trợ.
  2. Tìm nút màu xanh lá cây **"Giải quyết sự cố" (Resolve Ticket)** ➔ Bấm vào.
  3. Nhập nội dung biên bản giải pháp: `Đã cập nhật lại Driver Token USB và khởi động lại dịch vụ ký số. Đã ký thử hóa đơn số 00124 thành công.`
  4. Bấm nút **"Xác nhận giải quyết"**.
- 👁️ **Kết quả quan sát được:**
  - Nhãn trạng thái chuyển sang màu xanh lá **`ĐÃ GIẢI QUYẾT (RESOLVED)`**.
  - Hệ thống tự động gửi thông báo đến góc chuông của chị Đỗ Mai Phương.

---

### Bước 6: Làm việc trên Phân hệ Quản lý Dự án Agile (TaskFlow / Jira)
- 🎯 **Mục đích:** Kỹ thuật viên tham gia vào dự án phát triển phần mềm, cập nhật trạng thái công việc trên bảng Kanban và kiểm thử Test Case.
- 💼 **Ý nghĩa nghiệp vụ (Quản lý dự án Agile/Scrum & Đảm bảo chất lượng QA/QC):**
  - **Trực quan hóa luồng công việc (Kanban Flow):** Giúp cả nhóm phát triển và các bên liên quan nhìn thấy ngay tiến độ dự án, phát hiện nút thắt cổ chai (bottleneck) để xử lý kịp thời.
  - **Kiểm thử liên tục (Continuous Testing):** Cập nhật trạng thái Test Case giúp đảm bảo chức năng phần mềm đã được kiểm định chất lượng trước khi phát hành cho khách hàng.
- 🖱️ **Thao tác chi tiết:**
  1. Nhìn lên menu trên cùng ➔ Rê chuột vào **"Dự án"** ➔ Bấm vào tên dự án (ví dụ: `Dự án bank demo` hoặc `Dự án AI`, URL: `http://localhost:3000/projects/10/board`).
  2. **Kéo thả thẻ Kanban:**
     - Tìm một thẻ công việc bất kỳ ở cột `TO DO`.
     - Giữ chuột trái vào thẻ ➔ **Kéo thả sang cột `IN PROGRESS`**, sau đó kéo tiếp sang cột `DONE`.
  3. **Cập nhật Ca kiểm thử (Test Case):**
     - Nhìn sang menu bên trái dự án ➔ Bấm vào mục **"Test Cases"** (`http://localhost:3000/projects/10/testcases`).
     - Tại ca kiểm thử đầu tiên, bấm vào ô chọn trạng thái ➔ Đổi từ `UNTESTED` sang `PASSED` (Thành công màu xanh).
- 👁️ **Kết quả quan sát được:**
  - Thẻ công việc di chuyển mượt mà qua các cột, con số đếm trên đầu cột tự động nhảy số theo thời gian thực.
  - Test Case đổi màu xanh lá `PASSED`, thanh tiến độ kiểm thử của dự án tăng tỷ lệ hoàn thành.

---

## 👔 IV. KỊCH BẢN 3: DÀNH CHO TRƯỞNG PHÒNG IT (IT MANAGER)
*Đóng vai: Anh Trần Văn Hoàng (`manager`) - IT Leader / Trưởng phòng Vận hành CNTT*

---

### Bước 1: Chuyển đổi sang tài khoản Quản lý IT
- 🎯 **Mục đích:** Truy cập bảng điều khiển cấp quản lý để giám sát nguồn lực và điều phối hoạt động.
- 💼 **Ý nghĩa nghiệp vụ:** Phân quyền quản trị điều hành (Management Role). Trưởng phòng có góc nhìn bao quát toàn bộ hiệu năng vận hành và thẩm quyền xét duyệt ngân sách, công việc.
- 🖱️ **Thao tác chi tiết:**
  1. Bấm vào **Vòng tròn Avatar góc phải trên cùng** ➔ Chọn **"Đăng xuất"**.
  2. Tại màn hình đăng nhập ➔ Bấm vào ô nhanh thứ 2: **"Manager (Trần Văn Hoàng)"** ➔ Bấm **"Đăng nhập hệ thống"**.
- 👁️ **Kết quả quan sát được:** Màn hình chuyển vào không gian làm việc của Trưởng phòng IT.

---

### Bước 2: Giám sát toàn cảnh vận hành IT qua Dashboard Quản trị
- 🎯 **Mục đích:** Nắm bắt tức thì các chỉ số sức khỏe của dịch vụ CNTT trong toàn doanh nghiệp.
- 💼 **Ý nghĩa nghiệp vụ (Vận hành dựa trên dữ liệu - Data-Driven Operations):**
  - Giúp Trưởng phòng không cần gọi hỏi từng người mà vẫn nhìn thấy rõ: Hôm nay có bao nhiêu sự cố, có sự cố nào quá hạn SLA không, bộ phận nào đang gặp nhiều vấn đề nhất.
  - Kịp thời điều chuyển kỹ sư hỗ trợ cho các điểm nóng, ngăn chặn nguy cơ đình trệ hoạt động kinh doanh.
- 🖱️ **Thao tác chi tiết:**
  1. Nhìn lên menu ➔ Rê chuột vào **"Bảng điều khiển"** ➔ Bấm chọn **"Vận Hành IT (Operations)"** (`http://localhost:3000/manager/dashboard`).
- 👁️ **Kết quả quan sát được:**
  - Biểu đồ tỷ lệ tuân thủ cam kết SLA toàn công ty (đạt > 95%).
  - Thống kê tỷ lệ phân bổ sự cố theo phòng ban (Kế toán, Kinh doanh, Nhân sự...).
  - Điểm hài lòng khách hàng (CSAT) trung bình đạt 4.8 / 5.0 sao.

---

### Bước 3: Điều phối các phiếu chưa có người nhận (Unassigned Tickets)
- 🎯 **Mục đích:** Phân bổ các sự cố mới gửi đến cho đúng chuyên viên kỹ thuật phụ trách.
- 💼 **Ý nghĩa nghiệp vụ (Điều phối nguồn lực - Workload Balancing):**
  - Tránh tình trạng phiếu bị bỏ quên do không ai nhận việc.
  - Giao việc đúng người đúng chuyên môn (mạng cho Network, phần mềm cho App Support, máy tính cho Helpdesk) giúp rút ngắn thời gian xử lý tối đa.
- 🖱️ **Thao tác chi tiết:**
  1. Nhìn lên menu ➔ Rê chuột vào **"Bộ lọc & Phiếu"** ➔ Chọn **"Phiếu chưa phân công"** (`http://localhost:3000/tickets/unassigned`).
  2. Bấm vào một phiếu chưa có ai làm ➔ Tại ô *"Người thực hiện (Assignee)"* ➔ Chọn giao cho một kỹ thuật viên (ví dụ: `it_helpdesk`) ➔ Bấm **"Cập nhật"**.
- 👁️ **Kết quả quan sát được:** Phiếu biến mất khỏi danh sách chưa gán và chuyển ngay vào hàng đợi cá nhân của kỹ thuật viên đó kèm chuông thông báo giao việc.

---

### Bước 4: Trung tâm Phê duyệt Cấp phát & Duyệt Thay đổi hệ thống (Approvals & CAB)
- 🎯 **Mục đích:** Xem xét phê duyệt các đề xuất mua sắm tài sản và phê duyệt kế hoạch nâng cấp hạ tầng.
- 💼 **Ý nghĩa nghiệp vụ (Kiểm soát chi phí & Quản lý Thay đổi ITIL - Change Management):**
  - **Kiểm soát mua sắm:** Đảm bảo chỉ cấp phát thiết bị mới khi đúng nhu cầu và có xác nhận của cấp quản lý, tránh lãng phí ngân sách công ty.
  - **Hội đồng xét duyệt thay đổi (CAB - Change Advisory Board):** Trước khi kỹ sư nâng cấp máy chủ core lúc nửa đêm, bắt buộc phải trình bày kế hoạch rủi ro và phương án hoàn nguyên (Rollback). Trưởng phòng duyệt thì mới được phép thực hiện, tránh rủi ro sập hệ thống vào sáng hôm sau.
- 🖱️ **Thao tác chi tiết:**
  1. **Duyệt cấp thiết bị mới:**
     - Nhìn lên menu ➔ Rê chuột vào **"Công việc"** ➔ Bấm chọn **"Phê duyệt chờ tôi"** (`http://localhost:3000/approvals`).
     - Mở yêu cầu cấp màn hình mới ➔ Bấm nút màu xanh **"Chấp thuận" (Approve)**.
  2. **Duyệt Thay đổi hệ thống (RFC):**
     - Rê chuột vào **"Bộ lọc & Phiếu"** ➔ Chọn **"Yêu cầu thay đổi (Change)"** (`http://localhost:3000/changes`).
     - Mở phiếu nâng cấp máy chủ ➔ Bấm nút **"Phê duyệt CAB"**.
- 👁️ **Kết quả quan sát được:** Yêu cầu chuyển trạng thái `APPROVED` (Đã duyệt), kích hoạt bước mua sắm hoặc triển khai kỹ thuật tiếp theo.

---

### Bước 5: Xem Báo cáo SLA & Bảng Xếp hạng KPI Năng suất Kỹ thuật viên
- 🎯 **Mục đích:** Đo lường hiệu quả làm việc thực tế của đội ngũ kỹ thuật viên và xuất dữ liệu báo cáo Ban Giám đốc.
- 💼 **Ý nghĩa nghiệp vụ (Đánh giá hiệu suất nhân sự & Báo cáo quản trị):**
  - Đánh giá khách quan, minh bạch dựa trên số liệu thực tế: Ai đóng được nhiều ticket nhất, ai tuân thủ SLA tốt nhất, ai được khách hàng khen ngợi nhiều nhất.
  - Phục vụ việc bình xét khen thưởng cuối quý và báo cáo lên Tổng giám đốc.
- 🖱️ **Thao tác chi tiết:**
  1. Nhìn lên menu ➔ Rê chuột vào **"Bảng điều khiển"** ➔ Bấm chọn **"Hiệu Suất Kỹ Thuật Viên"** (`http://localhost:3000/reports/staff`).
     - Xem bảng xếp hạng top nhân viên IT xuất sắc.
  2. Rê chuột vào **"Bảng điều khiển"** ➔ Bấm chọn **"Phân Tích SLA & Xu Hướng"** (`http://localhost:3000/reports`).
     - Xem biểu đồ thời gian xử lý sự cố trung bình (MTTR).
     - Bấm nút **"Xuất file CSV / Excel"** để tải file báo cáo tổng hợp về máy tính.
- 👁️ **Kết quả quan sát được:** File báo cáo Excel được tải về máy tính với đầy đủ số liệu thống kê chi tiết.

---

## 🛡️ V. KỊCH BẢN 4: DÀNH CHO QUẢN TRỊ VIÊN HỆ THỐNG (SYSTEM ADMIN)
*Đóng vai: Administrator (`admin`) - Quản trị viên tối cao của toàn bộ hệ sinh thái*

---

### Bước 1: Đăng nhập quyền Quản trị tối cao (System Administrator)
- 🎯 **Mục đích:** Tiếp cận các phân hệ cấu hình lõi, an ninh mạng và an toàn dữ liệu.
- 💼 **Ý nghĩa nghiệp vụ:** Vai trò cao nhất trong hệ thống, chỉ dành riêng cho Quản trị viên trưởng IT để thiết lập các chính sách an ninh, cấu hình phân quyền và giám sát tuân thủ.
- 🖱️ **Thao tác chi tiết:**
  1. Bấm vào **Vòng tròn Avatar góc phải trên cùng** ➔ Chọn **"Đăng xuất"**.
  2. Tại trang login ➔ Bấm vào ô nhanh thứ 1: **"Admin (Administrator Updated)"** ➔ Bấm **"Đăng nhập hệ thống"**.
- 👁️ **Kết quả quan sát được:** Trên thanh menu trên cùng xuất hiện thêm menu đặc quyền: **"Cài đặt"** với đầy đủ các công cụ quản trị cấp cao.

---

### Bước 2: Thiết lập Ma trận Phân quyền Vai trò chi tiết (RBAC Permissions Matrix)
- 🎯 **Mục đích:** Phân định rõ ràng từng hành động nào vai trò nào được phép làm, hành động nào bị cấm.
- 💼 **Ý nghĩa nghiệp vụ (Nguyên tắc đặc quyền tối thiểu - Least Privilege & ISO 27001):**
  - Ngăn ngừa gian lận nội bộ: Nhân viên văn phòng không được phép xóa tài sản, kỹ thuật viên không được tự duyệt mua sắm thiết bị cho bản thân.
  - Bật/tắt linh hoạt các quyền hạn chi tiết (granular permissions) mà không cần can thiệp sửa mã nguồn (code).
- 🖱️ **Thao tác chi tiết:**
  1. Rê chuột vào menu **"Cài đặt"** ➔ Bấm chọn **"Ma trận phân quyền (RBAC)"** (`http://localhost:3000/admin/permissions`).
  2. Bảng ma trận 2 chiều hiện ra: Hàng ngang là các quyền hạn (Xem menu, Tạo dự án, Xóa tài sản, SSH máy chủ...); Cột dọc là 4 vai trò chính.
  3. Thử tích chọn hoặc bỏ tích một quyền hạn bất kỳ ➔ Kéo xuống bấm nút màu xanh **"Lưu cập nhật ma trận"**.
- 👁️ **Kết quả quan sát được:** Thông báo màu xanh báo lưu thành công, quyền hạn mới được áp dụng lập tức trên toàn hệ thống.

---

### Bước 3: Giám sát an ninh JumpServer PAM & Cưỡng bức ngắt phiên (Force Kick)
- 🎯 **Mục đích:** Quản trị viên theo dõi ai đang đăng nhập vào máy chủ nào và can thiệp ngắt kết nối ngay khi thấy nguy hiểm.
- 💼 **Ý nghĩa nghiệp vụ (Trung tâm giám sát an ninh mạng - SOC & Chống rò rỉ dữ liệu):**
  - Nếu phát hiện kỹ sư thao tác sai lệnh hoặc có dấu hiệu tài khoản bị chiếm đoạt từ bên ngoài, Admin có quyền lực tối cao "Kick" phiên ngay lập tức để bảo vệ an toàn cho cơ sở dữ liệu doanh nghiệp.
- 🖱️ **Thao tác chi tiết:**
  1. Rê chuột vào menu **"Cài đặt"** ➔ Bấm chọn **"Quản lý Jump Server"** (`http://localhost:3000/admin/jump-servers`).
  2. Kéo xuống mục **"Các phiên đang kết nối (Active Sessions)"**:
     - Admin sẽ thấy danh sách kỹ sư đang mở terminal kết nối máy chủ, kèm địa chỉ IP và thời gian bắt đầu.
  3. **Thao tác biểu diễn an ninh:** Bấm vào nút màu đỏ **"Kick Ngay" (Force Kick)** ➔ Xác nhận.
- 👁️ **Kết quả quan sát được:** Phiên kết nối đó bị ngắt lập tức, kỹ sư bị đẩy văng khỏi máy chủ ngay tại thời điểm bấm!

---

### Bước 4: Nhật ký Kiểm toán An toàn Thông tin (Audit Logs)
- 🎯 **Mục đích:** Tra cứu lịch sử mọi hành vi diễn ra trên toàn hệ thống để phục vụ công tác thanh kiểm tra.
- 💼 **Ý nghĩa nghiệp vụ (Tính bất khả chối bỏ - Non-Repudiation & Audit Compliance):**
  - Mọi thao tác quan trọng (đăng nhập, sửa dữ liệu, phân quyền, xóa phiếu) đều được ghi nhận tự động: Ai làm, làm gì, lúc mấy giờ, từ địa chỉ IP nào.
  - Khi có sự cố rò rỉ hoặc thất thoát thông tin, nhật ký kiểm toán là bằng chứng pháp lý rõ ràng nhất để xác định trách nhiệm.
- 🖱️ **Thao tác chi tiết:**
  1. Rê chuột vào menu **"Cài đặt"** ➔ Bấm chọn **"Nhật ký kiểm toán"** (`http://localhost:3000/admin/audit-logs`).
  2. Quan sát bảng nhật ký chi tiết các sự kiện vừa diễn ra trong suốt buổi demo.
  3. Bấm nút **"Xuất file CSV"** để tải file nhật ký kiểm toán về bàn giao cho bộ phận an ninh mạng.
- 👁️ **Kết quả quan sát được:** Bảng nhật ký ghi lại đầy đủ các bước thao tác từ đầu buổi demo đến hiện tại.

---

### Bước 5: Sao lưu Cơ sở dữ liệu 1-Click (Disaster Recovery & Backup)
- 🎯 **Mục đích:** Tạo bản sao lưu dự phòng toàn bộ hệ thống chỉ với một cú nhấp chuột và tải về máy tính an toàn.
- 💼 **Ý nghĩa nghiệp vụ (Kế hoạch duy trì kinh doanh liên tục - BCP & Phục hồi sau thảm họa - DR):**
  - Đảm bảo doanh nghiệp luôn có phương án dự phòng khi máy chủ gặp sự cố cháy nổ, hỏng ổ cứng hoặc bị tấn công mã độc tống tiền (Ransomware).
  - Khôi phục lại 100% dữ liệu nguyên vẹn chỉ trong vài phút, đưa hệ thống trở lại hoạt động bình thường mà không làm gián đoạn kinh doanh.
- 🖱️ **Thao tác chi tiết:**
  1. Rê chuột vào menu **"Cài đặt"** ➔ Bấm chọn **"Sao lưu & Phục hồi CSDL"** (`http://localhost:3000/admin/backups`).
  2. Bấm nút màu xanh **"+ Tạo bản sao lưu ngay"**.
  3. Khi file sao lưu được tạo xong ➔ Bấm nút **"Tải về"** để lưu file `.sql` về máy tính cá nhân.
- 👁️ **Kết quả quan sát được:** File backup SQL hoàn chỉnh được lưu an toàn trên máy, sẵn sàng bấm "Phục hồi" bất cứ lúc nào.

---

## 🌟 VI. BƯỚC ĐÓNG VÒNG LẶP: NGHIỆM THU VÀ CHẤM ĐIỂM CSAT
*Quay lại đóng vai: Chị Đỗ Mai Phương (`user1`)*

---

- 🎯 **Mục đích:** Người dùng kiểm tra kết quả hỗ trợ, chính thức đóng phiếu và đánh giá mức độ hài lòng.
- 💼 **Ý nghĩa nghiệp vụ (Khép kín vòng đời sự cố & Chỉ số hài lòng khách hàng - CSAT):**
  - **Sự cố chỉ thực sự kết thúc khi người dùng hài lòng:** Tránh trường hợp IT tự ý báo xong việc trong khi máy tính của người dùng vẫn chưa chạy được.
  - **Cải tiến dịch vụ liên tục (Continual Service Improvement):** Điểm số đánh giá 5 sao từ người dùng là thước đo chất lượng dịch vụ nội bộ cao nhất của phòng IT.
- 🖱️ **Thao tác chi tiết:**
  1. Đăng xuất Admin ➔ Đăng nhập lại tài khoản **End User** (`user1`).
  2. Nhìn lên góc chuông thông báo (cạnh ô tìm kiếm) ➔ Thấy chấm đỏ thông báo: *IT đã giải quyết xong phiếu của bạn*.
  3. Bấm vào chuông ➔ Bấm vào dòng thông báo để mở lại phiếu `#TICK-2026-083`.
  4. Đọc biên bản giải pháp của kỹ thuật viên.
  5. Bấm nút màu xanh **"Xác nhận đóng phiếu" (Confirm Close)**.
  6. Một khung đánh giá hiện lên ➔ **Bấm chọn 5 Sao ⭐⭐⭐⭐⭐** và nhập nhận xét: `Cảm ơn IT đã xử lý rất nhanh và tận tình!` ➔ Bấm **"Gửi đánh giá"**.
- 👁️ **Kết quả quan sát được:**
  - Phiếu chuyển sang màu xám **`ĐÃ ĐÓNG (CLOSED)`**.
  - Quy trình vận hành kết thúc hoàn hảo 100%, tỷ lệ SLA đạt chỉ tiêu xuất sắc!

---

## 📌 BẢNG TRA CỨU NHANH TÀI KHOẢN & VỊ TRÍ BẤM TRÊN MÀN HÌNH

| Cấp độ | Tài khoản mẫu | Vị trí bấm chọn tại Trang Login | Vị trí menu quan trọng trên màn hình |
|:---|:---:|:---|:---|
| **End User** | `user1`<br>*(Đỗ Mai Phương)* | Bấm ngay vào **Ô thứ 4: End User** | Bấm vào **Vòng tròn Avatar góc trên bên phải** ➔ Chọn **"Thiết bị của tôi"** *(hoặc menu Công việc ➔ Thiết bị / Tài sản của tôi)* |
| **IT Staff** | `it_helpdesk`<br>*(Lê Quốc Bảo)* | Bấm vào **Ô thứ 3: IT Staff** | Menu **Công việc ➔ Hàng đợi công việc** & Menu **Tiện ích ➔ JumpServer Bastion PAM** |
| **IT Manager** | `manager`<br>*(Trần Văn Hoàng)* | Bấm vào **Ô thứ 2: Manager** | Menu **Bảng điều khiển ➔ Vận Hành IT (Operations)** & Menu **Bộ lọc & Phiếu ➔ Phiếu chưa phân công** |
| **System Admin**| `admin`<br>*(Administrator)* | Bấm vào **Ô thứ 1: Admin** | Menu **Cài đặt** (Ma trận phân quyền RBAC, JumpServer, Sao lưu CSDL) |
