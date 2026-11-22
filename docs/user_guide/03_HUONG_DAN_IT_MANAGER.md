# SỔ TAY HƯỚNG DẪN THAO TÁC TỪNG BƯỚC: QUẢN LÝ IT (IT MANAGER / LEADER)
## Phân Hệ Giám Sát KPI, Điều Phối Hàng Đợi, Phê Duyệt Dịch Vụ & Quản Trị Dự Án Agile

---

## MỤC LỤC CHI TIẾT
1. [Đăng Nhập & Giám Sát Bảng Điều Khiển Quản Lý (Manager Dashboard & KPI)](#1-đăng-nhập--giám-sát-bảng-điều-khiển-quản-lý-manager-dashboard--kpi)
2. [Hàng Đợi Chờ Phân Công & Chỉ Định Kỹ Thuật Viên (Unassigned Queue)](#2-hàng-đợi-chờ-phân-công--chỉ-định-kỹ-thuật-viên-unassigned-queue)
3. [Trung Tâm Phê Duyệt Yêu Cầu Dịch Vụ & Thay Đổi (Approval Workflow Center)](#3-trung-tâm-phê-duyệt-yêu-cầu-dịch-vụ--thay-đổi-approval-workflow-center)
4. [Thẩm Quyền Đặc Quyền ITIL: Điều Chỉnh Mức Độ Ưu Tiên Phiếu (Priority Adjustment)](#4-thẩm-quyền-đặc-quyền-itil-điều-chỉnh-mức-độ-ưu-tiên-phiếu-priority-adjustment)
5. [Khởi Tạo & Cấu Hình Dự Án Kỹ Thuật Mới (Create Agile / Jira Project)](#5-khởi-tạo--cấu-hình-dự-án-kỹ-thuật-mới-create-agile--jira-project)
6. [Lập Kế Hoạch & Quản Lý Phân Cấp Công Việc (Backlog Hierarchy & Sprints)](#6-lập-kế-hoạch--quản-lý-phân-cấp-công-việc-backlog-hierarchy--sprints)
7. [Quản Trị Ước Lượng Thời Gian & Tiến Độ Thực Hiện (Effort Tracking & Work Items)](#7-quản-trị-ước-lượng-thời-gian--tiến-độ-thực-hiện-effort-tracking--work-items)
8. [Xuất Dữ Liệu Báo Cáo Ra File Excel Chuẩn UTF-8 BOM](#8-xuất-dữ-liệu-báo-cáo-ra-file-excel-chuẩn-utf-8-bom)

---

## 1. ĐĂNG NHẬP & GIÁM SÁT BẢNG ĐIỀU KHIỂN QUẢN LÝ (MANAGER DASHBOARD & KPI)

### Mục đích:
Giám sát toàn diện chất lượng dịch vụ CNTT theo thời gian thực: Tỷ lệ tuân thủ cam kết SLA, phiếu quá hạn, khối lượng công việc đang xử lý của từng nhóm kỹ thuật và từng nhân viên để phân bổ nhân lực hợp lý.

### Các bước thao tác bấm chuột:

#### Bước 1.1: Đăng nhập tài khoản Quản lý IT
- Mở trình duyệt web truy cập: `http://localhost:3000/login`.
- Bấm chuột vào ô **"Tên đăng nhập"**: Nhập `manager`.
- Bấm chuột vào ô **"Mật khẩu"**: Nhập `123456`.
- Bấm chuột vào nút màu xanh **"ĐĂNG NHẬP HỆ THỐNG"**.
- **Kết quả:** Hệ thống tự động chuyển bạn thẳng đến màn hình **Bảng Điều Khiển Quản Trị Vận Hành** tại `http://localhost:3000/manager/dashboard`.

```text
GIAO DIỆN BẢNG ĐIỀU KHIỂN QUẢN LÝ IT (MANAGER DASHBOARD & KPI)
+----------------------------------------------------------------------------------------------------+
| 📈 QUẢN LÝ VẬN HÀNH & HIỆU SUẤT           [ 📥 Hàng Đợi Chờ Phân Công (3) ]  [ + Tạo Ticket Mới ]  |
| Giám sát năng suất kỹ thuật viên, phân bổ tài nguyên nhóm và mức độ tuân thủ SLA                   |
+----------------------------------------------------------------------------------------------------+
|  [ TỔNG TIẾP NHẬN ]  [ CHỜ PHÂN CÔNG ]  [ ĐANG XỬ LÝ ]  [ QUÁ HẠN SLA ]  [ ĐÃ ĐÓNG ]  [ TỶ LỆ SLA ]|
|       142 Phiếu            3 Phiếu          28 Phiếu        2 Phiếu 🔴      112 Phiếu     96.8% 🟢  |
+----------------------------------------------------------------------------------------------------+
| KHỐI LƯỢNG THEO NHÓM IT (4 Nhóm)                  | KHỐI LƯỢNG THEO NHÂN VIÊN                      |
| Nhóm IT          | Tổng | Active | Overdue | Chưa | Kỹ thuật viên   | Đang xử lý | Quá hạn | Đạt SLA |
|------------------+------+--------+---------+------|-----------------+------------+---------+---------|
| Mạng & Hạ Tầng   |  35  |   8    |    1    |  2   | Lê Quốc Bảo     |     4      |    0    |  100%   |
| Phần Cứng & Trạm |  48  |  12    |    1    |  1   | Nguyễn Văn Cường|     6      |    1 🔴 |   88%   |
| Phần Mềm & ERP   |  42  |   6    |    0    |  0   | Trần Minh Trí   |     2      |    0    |  100%   |
| An Toàn Thông Tin|  17  |   2    |    0    |  0   | Hoàng Tuấn Anh  |     1      |    0    |  100%   |
+----------------------------------------------------------------------------------------------------+
```

#### Bước 1.2: Đọc hiểu và phân tích chỉ số vận hành
1. **Thẻ KPI trên đỉnh trang:**
   - **Chờ phân công (Warning - Vàng):** Số phiếu mới người dùng gửi vào chưa có người nhận. Bấm ngay nút **"Hàng Đợi Chờ Phân Công"** để giải phóng hàng đợi.
   - **Quá hạn SLA (Danger - Đỏ):** Cảnh báo sự cố trễ hạn cam kết. Nhấp chuột vào thẻ để lọc danh sách các phiếu vi phạm và đôn đốc nhân viên.
   - **Tỷ lệ đạt SLA (Success - Xanh lá):** Đạt trên **95%** là đạt chuẩn chất lượng ITIL doanh nghiệp.
2. **Bảng Khối Lượng Theo Nhóm IT:** Cột *"Chưa gán"* có số màu cam cảnh báo nhóm cần Manager phân bổ người phụ trách.
3. **Bảng Khối Lượng Theo Nhân Viên:** Giúp Manager nhận diện ngay kỹ thuật viên nào đang bị quá tải (ôm nhiều hơn 5 phiếu đang xử lý) để tránh giao thêm việc.

---

## 2. HÀNG ĐỢI CHỜ PHÂN CÔNG & CHỈ ĐỊNH KỸ THUẬT VIÊN (UNASSIGNED QUEUE)

### Mục đích:
Tiếp nhận các yêu cầu sự cố chưa được tiếp nhận và giao đúng người đúng chuyên môn, hoặc tự nhận xử lý nếu là sự cố trọng yếu.

### Các bước thao tác bấm chuột:

#### Bước 2.1: Mở danh sách hàng đợi chưa phân công
- Tại thanh Topbar hoặc nút màu cam trên Manager Dashboard, bấm vào nút **"Hàng đợi Chờ Phân Công"** (URL: `http://localhost:3000/tickets/unassigned`).
- Màn hình hiển thị danh sách toàn bộ phiếu đang ở trạng thái `NEW` hoặc chưa gán người phụ trách.

```text
GIAO DIỆN HÀNG ĐỢI CHỜ PHÂN CÔNG (UNASSIGNED TICKETS)
+----------------------------------------------------------------------------------------------------+
| 📥 HÀNG ĐỢI CHỜ PHÂN CÔNG                                                [ 3 TICKET CHỜ ] [ Về DB ]|
+----------------------------------------------------------------------------------------------------+
| MÃ PHIẾU  | TIÊU ĐỀ SỰ CỐ            | NGƯỜI GỬI      | ƯU TIÊN | NHÓM PHỤ TRÁCH | HẠN SLA | THAO TÁC  |
|-----------+--------------------------+----------------+---------+----------------+---------+-----------|
| IT-000041 | Mất kết nối Switch Tầng 3| Đỗ Mai Phương  | URGENT  | Mạng & Hạ Tầng | 01:15:00| [Nhận][⚙️]|
| IT-000042 | Cài đặt ứng dụng PowerBI | Nguyễn Thị Lan | NORMAL  | Phần Mềm & ERP | 23:45:00| [Nhận][⚙️]|
| IT-000043 | Hỏng bàn phím cơ Dell    | Trần Văn Nam   | LOW     | Phần Cứng      | 47:10:00| [Nhận][⚙️]|
+----------------------------------------------------------------------------------------------------+
```

#### Bước 2.2: Lựa chọn hình thức phân công

**Cách A: Manager tự nhận xử lý ngay (Take Ticket):**
- Bấm chuột vào nút màu xanh **"Nhận"** (Icon `bi-hand-index-thumb`) tại dòng phiếu tương ứng.
- Trình duyệt bật hộp thoại xác nhận: *"Bạn muốn tự tiếp nhận xử lý yêu cầu này?"*.
- Bấm **OK**. Hệ thống tự động gán tên Manager vào phiếu và chuyển trạng thái sang `IN_PROGRESS`.

**Cách B: Phân công cho kỹ thuật viên cấp dưới:**
1. Bấm chuột vào nút icon bánh răng vàng **[ ⚙️ ]** (Xem & Phân công cho người khác) tại cuối dòng phiếu.
2. Hệ thống đưa bạn vào trang chi tiết phiếu (`/tickets/{id}`).
3. Nhìn sang cột bên phải (Cột Thao Tác Kỹ Thuật), bấm nút **"Phân công / Đổi người phụ trách"** (Icon `bi-person-gear`).
4. Cửa sổ Modal hiện lên:

```text
+-------------------------------------------------------------------------+
| PHÂN CÔNG KỸ THUẬT VIÊN PHỤ TRÁCH                                   [X] |
|                                                                         |
| ℹ️ Theo quy định SLA: Việc chuyển giao người xử lý KHÔNG làm reset SLA  |
|                                                                         |
| Chọn nhân viên IT:                                                      |
| [ Lê Quốc Bảo (it_helpdesk@company.local) - Đang xử lý: 2 phiếu     ▼ ] |
|                                                                         |
| Lý do phân công / điều chuyển:                                          |
| [ Giao kỹ sư chuyên trách hạ tầng mạng tầng 3 kiểm tra switch        ]  |
|                                                                         |
|                         [ Đóng ]    [ XÁC NHẬN PHÂN CÔNG ]              |
+-------------------------------------------------------------------------+
```

5. Bấm vào menu thả xuống **"Chọn nhân viên IT"**: Chọn kỹ thuật viên mong muốn.
6. Bấm vào ô **"Lý do phân công / điều chuyển"**: Nhập lý do (Ví dụ: *Phân công đúng chuyên môn cáp mạng*).
7. Bấm nút màu xanh **"Xác nhận phân công"**.
8. **Kết quả:** Phiếu lập tức xuất hiện trong hàng đợi cá nhân của nhân viên đó kèm thông báo chuông.

---

## 3. TRUNG TÂM PHÊ DUYỆT YÊU CẦU DỊCH VỤ & THAY ĐỔI (APPROVAL WORKFLOW CENTER)

### Mục đích:
Xét duyệt các phiếu yêu cầu cấp phát thiết bị tài sản có giá trị (Laptop mới, màn hình đồ họa) hoặc các đề xuất thay đổi hạ tầng hệ thống (RFC) nhằm kiểm soát ngân sách và an toàn vận hành.

### Các bước thao tác bấm chuột:

#### Bước 3.1: Truy cập Trung tâm Phê duyệt
- Nhấp chuột vào menu **"Phê Duyệt"** trên thanh Topbar hoặc truy cập: `http://localhost:3000/approvals`.
- Bấm vào tab **"Chờ duyệt"** (màu xanh dương) để xem các đơn đang chờ Manager xử lý.

```text
GIAO DIỆN TRUNG TÂM PHÊ DUYỆT (APPROVAL CENTER)
+----------------------------------------------------------------------------------------------------+
| 🛡️ TRUNG TÂM PHÊ DUYỆT                [ Chờ duyệt (2) ] [ Đã phê duyệt ] [ Đã từ chối ] [ Tất cả ] |
+----------------------------------------------------------------------------------------------------+
| MÃ ĐỐI TƯỢNG| LOẠI & TIÊU ĐỀ                   | NGƯỜI GỬI    | BƯỚC DUYỆT | TRẠNG THÁI | THAO TÁC     |
|-------------+----------------------------------+--------------+------------+------------+--------------|
| IT-000038   | [SERVICE_REQ] Cấp Laptop Dell XPS| Đỗ Mai Phương| Manager    | 🟡Chờ duyệt| [ Xét duyệt ]|
| RFC-000005  | [CHANGE_RFC] Nâng cấp RAM Server | Lê Quốc Bảo  | CAB Lead   | 🟡Chờ duyệt| [ Xét duyệt ]|
+----------------------------------------------------------------------------------------------------+
```

#### Bước 3.2: Thao tác xét duyệt đơn
1. Tại dòng của đơn cần duyệt, bấm nút màu xanh **"Xét duyệt"** (Icon `bi-pencil-square`).
2. Cửa sổ Modal Phê duyệt bật lên giữa màn hình:

```text
+-------------------------------------------------------------------------+
| PHÊ DUYỆT: IT-000038                                                [X] |
|                                                                         |
| Nội dung yêu cầu: Đăng ký cấp phát Laptop Dell XPS 15 cho thiết kế đồ họa|
| Người gửi:        Đỗ Mai Phương (Phòng Marketing)                       |
|                                                                         |
| Quyết định của bạn (*):                                                 |
|   (o) ✅ Phê Duyệt (Approve)                                             |
|   ( ) ❌ Từ Chối (Reject)                                               |
|   ( ) 🔄 Bổ Sung (Return)                                               |
|                                                                         |
| Ý kiến / Ghi chú phê duyệt:                                             |
| [ Đã kiểm tra hạn mức ngân sách phòng Marketing quý 3. Phê duyệt mua. ] |
|                                                                         |
|                         [ Đóng ]    [ XÁC NHẬN QUYẾT ĐỊNH ]             |
+-------------------------------------------------------------------------+
```

3. **Chọn một trong 3 quyết định:**
   - **(o) Phê Duyệt (Approve):** Tích chọn ô này nếu đồng ý cấp phát. Phiếu tự động chuyển tiếp đến bộ phận IT Kho vận để chuẩn bị bàn giao.
   - **( ) Từ Chối (Reject):** Tích chọn ô này nếu bác bỏ yêu cầu. Đơn sẽ bị dừng lại và thông báo từ chối cho người gửi.
   - **( ) Bổ Sung (Return):** Yêu cầu người dùng cung cấp thêm báo giá hoặc chữ ký giám đốc khối.
4. **Nhập "Ý kiến / Ghi chú phê duyệt":** Nhập căn cứ xét duyệt của bạn.
5. Bấm nút màu xanh **"Xác Nhận Quyết Định"**.
6. **Kết quả:** Trạng thái đơn đổi ngay lập tức thành `APPROVED` (màu xanh lá) hoặc `REJECTED` (màu đỏ).

---

## 4. THẨM QUYỀN ĐẶC QUYỀN ITIL: ĐIỀU CHỈNH MỨC ĐỘ ƯU TIÊN PHIẾU (PRIORITY ADJUSTMENT)

### Mục đích & Quy tắc quản trị (Rule 9):
Người dùng khi tạo phiếu thường có xu hướng chọn mức độ ưu tiên cao nhất (`URGENT`). Theo chuẩn ITIL, **chỉ có IT Manager / Leader mới có quyền can thiệp thay đổi mức độ ưu tiên** để phản ánh đúng mức độ ảnh hưởng thực tế đến hoạt động kinh doanh. Mọi thay đổi đều được ghi lại vào lịch sử kiểm toán (Audit Trail) và tự động tính toán lại hạn chót SLA.

### Các bước thao tác bấm chuột:

#### Bước 4.1: Mở phiếu cần điều chỉnh
- Truy cập chi tiết phiếu: `http://localhost:3000/tickets/{id}`.
- Quan sát khối SLA hiện tại ở cột phải: Mức độ ưu tiên hiện tại là `URGENT` (Hạn xử lý trong 2 giờ).

#### Bước 4.2: Kích hoạt chức năng điều chỉnh
- Tại cột bên phải, tìm thẻ **"Thao Tác Kỹ Thuật (Workflow)"**.
- Bấm chuột vào nút có viền vàng: **"Điều chỉnh mức ưu tiên (SLA)"** (Icon lá cờ `bi-flag-fill`).

```text
GIAO DIỆN MODAL ĐIỀU CHỈNH MỨC ƯU TIÊN (RULE 9 ITIL)
+-------------------------------------------------------------------------+
| 🚩 ĐIỀU CHỈNH MỨC ĐỘ ƯU TIÊN (PRIORITY)                             [X] |
|                                                                         |
| ⚠️ Thao tác dành cho IT Manager/Leader. Mọi thay đổi đều được ghi nhật ký|
|                                                                         |
| Mức độ ưu tiên hiện tại: [ KHẨN CẤP (URGENT)                          ] |
|                                                                         |
| Chọn mức độ ưu tiên mới (*):                                            |
| [ NORMAL (Bình thường - SLA: 24 Giờ)                                 ▼ ]|
|                                                                         |
| Lý do điều chỉnh mức ưu tiên (* bắt buộc nhập để kiểm toán):            |
| [ Sự cố hỏng chuột máy tính cá nhân không ảnh hưởng diện rộng, chuyển  |
|   về mức NORMAL theo đúng ma trận đánh giá tác động ITIL.             ] |
|                                                                         |
|                         [ Hủy ]    [ LƯU THAY ĐỔI PRIORITY ]            |
+-------------------------------------------------------------------------+
```

#### Bước 4.3: Nhập dữ liệu và lưu thay đổi
1. Bấm chuột vào menu thả xuống **"Chọn mức độ ưu tiên mới"**: Chọn một trong các mức:
   - `LOW`: SLA 48 giờ.
   - `NORMAL`: SLA 24 giờ.
   - `HIGH`: SLA 8 giờ.
   - `URGENT`: SLA 2 giờ.
2. Bấm chuột vào ô **"Lý do điều chỉnh mức ưu tiên"**: Nhập lý do tường minh (Bắt buộc theo chuẩn kiểm toán nội bộ).
3. Bấm nút màu vàng **"LƯU THAY ĐỔI PRIORITY"**.
4. **Kết quả hiển thị:**
   - Huy hiệu Priority trên phiếu lập tức đổi màu.
   - Đồng hồ đếm ngược SLA (`sla_due_at`) tự động được hệ thống **tính toán lại tức thì** theo chính sách của Priority mới.
   - Tab **"Lịch sử kiểm toán" (History)** ghi nhận một dòng vết mới: *"Manager đã điều chỉnh Priority từ URGENT thành NORMAL kèm lý do chi tiết"*.

---

## 5. KHỞI TẠO & CẤU HÌNH DỰ ÁN KỸ THUẬT MỚI (CREATE AGILE / JIRA PROJECT)

### Mục đích:
Khởi tạo không gian làm việc số cho các dự án triển khai CNTT, nâng cấp hạ tầng, hoặc phát triển phần mềm theo phương pháp Agile Scrum / Kanban.

### Các bước thao tác bấm chuột:

#### Bước 5.1: Mở form tạo dự án
- Nhấp chuột vào menu **"Dự Án" (Projects)** trên thanh Topbar hoặc truy cập: `http://localhost:3000/projects`.
- Bấm chuột vào nút màu xanh **"+ Tạo Dự Án Mới"** ở góc trên bên phải trang web (URL: `http://localhost:3000/projects/create`).

```text
GIAO DIỆN TẠO DỰ ÁN MỚI (CREATE PROJECT)
+-------------------------------------------------------------------------+
| 🚀 TẠO DỰ ÁN MỚI                                                        |
| Thiết lập không gian làm việc cho nhóm phát triển hoặc quản trị dịch vụ |
+-------------------------------------------------------------------------+
| TÊN DỰ ÁN (*)                                | MÃ DỰ ÁN (KEY) (*)       |
| [ Nâng Cấp Hạ Tầng Mạng 10Gbps             ] | [ NET                  ] |
|                                                                         |
| LOẠI DỰ ÁN (PROJECT TYPE)                    | TRƯỞNG DỰ ÁN (LEAD) (*)  |
| [ 🚀 Software Development (Scrum/Kanban)   ▼]| [ Quản Lý IT (manager) ▼]|
|                                                                         |
| MÔ TẢ DỰ ÁN                                                             |
| [ Triển khai hệ thống switch core và cáp quang nội bộ toàn bộ tòa nhà ] |
|                                                                         |
| ICON ĐẠI DIỆN                                | MÀU NHẬN DIỆN THƯƠNG HIỆU|
| [ 💻 Laptop (Hạ tầng công nghệ)            ▼]| [ 🟦 Xanh Dương Jira   ▼]|
|                                                                         |
|                         [ Hủy ]    [ TẠO DỰ ÁN NGAY ]                   |
+-------------------------------------------------------------------------+
```

#### Bước 5.2: Điền thông tin cấu hình
1. **TÊN DỰ ÁN:** Nhập tên rõ ràng (Ví dụ: `Nâng Cấp Hạ Tầng Mạng 10Gbps`).
2. **MÃ DỰ ÁN (KEY):** Khi bạn gõ tên dự án, hệ thống **tự động sinh mã Key** viết hoa không dấu (Ví dụ: `NET`). Bạn có thể sửa lại theo ý muốn (Tối đa 8 ký tự, ví dụ: `ERP`, `SEC`, `PORTAL`). Mã Key này sẽ là tiền tố đánh số cho mọi công việc (Ví dụ: `NET-1`, `NET-2`).
3. **LOẠI DỰ ÁN:** Chọn loại phù hợp:
   - *Software Development (Scrum / Kanban)*: Dành cho lập trình phần mềm.
   - *IT Service Desk & Support*: Dành cho bảo trì, hỗ trợ dịch vụ.
   - *Business & Operations*: Dành cho các dự án nghiệp vụ.
4. **TRƯỞNG DỰ ÁN (LEAD):** Chọn tài khoản phụ trách dự án.
5. **ICON & MÀU SẮC:** Chọn Icon và màu sắc để phân biệt trên thanh Waffle menu.
6. Bấm nút màu xanh **"TẠO DỰ ÁN NGAY"**.
7. **Kết quả:** Dự án được tạo thành công và tự động chuyển hướng đến màn hình **Bảng Kanban Board** của dự án.

---

## 6. LẬP KẾ HOẠCH & QUẢN LÝ PHÂN CẤP CÔNG VIỆC (BACKLOG HIERARCHY & SPRINTS)

### Mục đích & Quy tắc phân cấp (Strict Hierarchy):
Hệ thống tuân thủ chặt chẽ nguyên lý phân cấp công việc chuẩn Agile ALM:
- 📦 **Backlog Item / Story (Cấp 1 - Cấp Cha):** Đại diện cho một tính năng lớn hoặc hạng mục công việc tổng thể do Manager khởi tạo.
- 📌 **Task / 🐞 Bug (Cấp 2 - Cấp Con):** Công việc chi tiết hoặc lỗi kỹ thuật phát sinh trực thuộc một Backlog Item.
- 🧪 **Test Case (Cấp 3 - Cấp Con Cuối Cùng):** Kịch bản kiểm thử trực thuộc Task hoặc Bug.

### Các bước thao tác bấm chuột:

#### Bước 6.1: Mở trang Quản lý Backlog
- Tại trang dự án, nhấp vào menu bên trái chọn **"Backlog"** hoặc truy cập: `http://localhost:3000/projects/{id}/backlog`.
- Màn hình chia làm 2 khu vực: **Sprint Hiện Tại (Active Sprint)** ở phía trên và **Danh Sách Backlog (Product Backlog)** ở phía dưới.

```text
GIAO DIỆN QUẢN LÝ BACKLOG & PHÂN CẤP CÔNG VIỆC
+----------------------------------------------------------------------------------------------------+
| 📑 QUẢN LÝ BACKLOG & SPRINTS           [ Xem Bảng Board ]  [ + Tạo Backlog ]  [ + Tạo Task ]       |
| [ 🔍 Tìm kiếm tiêu đề, mã key...           ]  Phân trang: 20 mục / trang                           |
+----------------------------------------------------------------------------------------------------+
| 🟢 SPRINT HIỆN TẠI (ACTIVE SPRINT - 4 issues)                                [ Bắt đầu Sprint ]   |
|   | 📌 NET-101 | Cấu hình VLAN 10 và VLAN 20 trên Switch Core   | Lê Quốc Bảo   | IN_PROGRESS      |
|   | 🐞 NET-102 | Xung đột địa chỉ IP Gateway Dãy Bàn Kế Toán   | Nguyễn Cường  | TO_DO            |
+----------------------------------------------------------------------------------------------------+
| 📦 PRODUCT BACKLOG (Danh mục công việc chung - 8 hạng mục)                                         |
|   [▼] 📦 NET-100: Hạng Mục Thi Công Trục Cáp Quang Tòa Nhà (Backlog Cha)                           |
|       ├── 📌 NET-103: Kéo dây cáp quang OM3 từ Server Tầng trệt lên Tầng 3      | [Gán người]      |
|       ├── 📌 NET-104: Hàn đầu nối sợi quang và đo độ suy hao suy biến quang     | [Gán người]      |
|       └── 🧪 NET-105: Kiểm thử thông mạch tín hiệu Ping đạt chuẩn 10Gbps        | [Chạy Test]      |
+----------------------------------------------------------------------------------------------------+
```

#### Bước 6.2: Tạo một Hạng mục Backlog Cấp Cha mới
1. Bấm chuột vào nút màu xanh lá **"+ Tạo Backlog"** (Icon `bi-plus-circle`) trên thanh công cụ.
2. Cửa sổ Modal hiện lên:
   - **Tiêu đề tóm tắt (Summary):** Nhập tên hạng mục lớn (Ví dụ: *Triển khai Cụm Máy Chủ Ảo Hóa VMware vSphere 8*).
   - **Mô tả chi tiết:** Ghi rõ phạm vi công việc.
   - **Độ ưu tiên (Priority):** Chọn `HIGH` hoặc `MEDIUM`.
3. Bấm **"Lưu Backlog"**. Backlog cha mới xuất hiện ngay trong danh sách.

#### Bước 6.3: Tạo Task / Bug Con trực thuộc Backlog Cha
1. Bấm chuột vào nút màu xanh dương **"+ Tạo Task"** (Icon `bi-plus-lg`).
2. Cửa sổ Modal hiện lên:
   - **Loại công việc (Issue Type):** Chọn `TASK` hoặc `BUG`.
   - **Mục cha (Parent Item):** Bấm vào danh sách xổ xuống > Chọn Backlog cha vừa tạo ở Bước 6.2 (Ví dụ: chọn `NET-100`).
   - **Người thực hiện (Assignee):** Chọn kỹ thuật viên phụ trách việc con này.
   - **Ước lượng thời gian (Original Estimate):** Nhập số giờ dự kiến (Ví dụ: `8.0` giờ).
3. Bấm **"Lưu Task"**. Hệ thống tự động liên kết và thụt lề cấp bậc trực quan dưới Backlog cha.

---

## 7. QUẢN TRỊ ƯỚC LƯỢNG THỜI GIAN & TIẾN ĐỘ THỰC HIỆN (EFFORT TRACKING & WORK ITEMS)

### Mục đích:
Theo dõi chi tiết mức độ tiêu hao nguồn lực (Man-hours): So sánh giữa thời gian ước tính ban đầu (Original Estimate), thời gian thực tế kỹ thuật viên đã hoàn thành (Completed Hours), và thời gian còn lại (Remaining Hours) để đảm bảo dự án không bị vỡ kế hoạch (Over-budget).

### Các bước thao tác bấm chuột:

#### Bước 7.1: Mở trang chi tiết Work Item
- Bấm vào mã công việc (Ví dụ: `NET-103`) tại Backlog hoặc trên Bảng Kanban Board.
- Màn hình chuyển vào trang chi tiết Work Item ALM chuyên nghiệp chia làm 3 cột:
  - **Cột 1 (Trái):** Nội dung, Tiêu chí nghiệm thu (Acceptance Criteria), Các bước tái hiện lỗi, và Thảo luận tương tác trực tiếp (@mention).
  - **Cột 2 (Giữa):** Lập kế hoạch (Planning), Độ ưu tiên, Ngày bắt đầu/kết thúc, và **Ước Lượng Effort (Hours)**.
  - **Cột 3 (Phải):** Cây phân cấp liên quan (Hierarchy Parent & Children).

```text
GIAO DIỆN CHI TIẾT WORK ITEM & EFFORT ESTIMATION
+----------------------------------------------------------------------------------------------------+
| 📌 TASK NET-103: Kéo dây cáp quang OM3 từ Server Tầng trệt lên Tầng 3     [ 💾 Save & Close ] [ 🔄 ]|
+----------------------------------------------------------------------------------------------------+
| CỘT 1: NỘI DUNG & THẢO LUẬN        | CỘT 2: PLANNING & EFFORT (HOURS)     | CỘT 3: RELATED WORK     |
|                                    | Priority: [ 🔴 1 - Highest        ▼] |                         |
| TIÊU ĐỀ:                           | Type:     [ 📌 Task               ▼] | MỤC CHA (PARENT ITEM):  |
| [ Kéo dây cáp quang OM3 tầng trệt] | Component:[ INFRASTRUCTURE        ▼] | [ 📦 NET-100: Hạng mục ]|
|                                    | Story Pts:[ 5                    ]   |                         |
| MÔ TẢ CHI TIẾT:                    | Start Date: [ 26/09/2026 ]           | DANH SÁCH CON:          |
| [ Đi cáp theo máng trục kỹ thuật ] | Due Date:   [ 28/09/2026 ]           | - NET-105 (Test ping)   |
|                                    |                                      |                         |
| THẢO LUẬN (DISCUSSION):            | --- EFFORT (HOURS) ---               | [ + Thêm việc con ]     |
| [ Nhập bình luận @nguoidung...   ] | Original Estimate: [ 16.0 ] h        |                         |
| [ Gửi thảo luận ]                  | Remaining:         [  6.0 ] h        |                         |
|                                    | Completed:         [ 10.0 ] h        |                         |
+----------------------------------------------------------------------------------------------------+
```

#### Bước 7.2: Cập nhật thông số Effort và Tiến độ
1. Di chuột sang **Cột 2 (Giữa)**, cuộn xuống phần **EFFORT (HOURS)**:
   - **Original Estimate:** Nhập tổng số giờ định mức dự kiến (Ví dụ: `16.0` h).
   - **Completed:** Nhập số giờ nhân viên đã làm thực tế (Ví dụ: `10.0` h).
   - **Remaining:** Nhập số giờ còn lại cần để hoàn tất (Ví dụ: `6.0` h).
2. Kiểm tra lại ngày bắt đầu (**Start Date**) và hạn chót (**Due Date**).
3. Bấm nút màu xanh dương **"Save & Close"** (Icon đĩa mềm `bi-floppy-fill`) ở góc trên bên phải.
4. **Kết quả:** Hệ thống cập nhật dữ liệu tức thì, thanh tiến độ % hoàn thành của toàn bộ Backlog cha tự động tính toán lại tỷ lệ hoàn thành tương ứng.

---

## 8. XUẤT DỮ LIỆU BÁO CÁO RA FILE EXCEL CHUẨN UTF-8 BOM

### Mục đích:
Trích xuất dữ liệu danh sách phiếu hỗ trợ, tiến độ dự án, hoặc danh mục tài sản thành tệp bảng tính Excel (`.csv` / `.xlsx`) phục vụ cho các buổi họp giao ban định kỳ với Ban Giám Đốc. Dữ liệu được mã hóa chuẩn **UTF-8 with BOM**, đảm bảo mở trực tiếp trên Microsoft Excel tiếng Việt không bị lỗi font chữ.

### Các bước thao tác bấm chuột:

#### Bước 8.1: Xuất Excel danh sách Phiếu Hỗ Trợ (Tickets)
1. Truy cập trang danh sách phiếu: `http://localhost:3000/tickets`.
2. Sử dụng thanh bộ lọc ở đầu bảng nếu muốn lọc theo ngày, theo nhóm IT hoặc theo trạng thái.
3. Bấm chuột vào nút màu xanh lá cây **"Xuất Excel"** (Icon `bi-file-earmark-excel-fill`).

```text
GIAO DIỆN NÚT XUẤT EXCEL TẠI LƯỚI DỮ LIỆU
+----------------------------------------------------------------------------------------------------+
| 📋 TẤT CẢ PHIẾU HỖ TRỢ (TICKETS)              [ 🔍 Tìm kiếm... ]  [ 📥 XUẤT EXCEL ] [ + Tạo Mới ]  |
+----------------------------------------------------------------------------------------------------+
```

4. **Kết quả:** Trình duyệt tự động tải xuống tệp có tên dạng: `tickets_export_2026-09-26_113000.csv`.
5. Mở file bằng Microsoft Excel: Bảng tính hiển thị chuẩn xác các cột Mã phiếu, Tiêu đề, Người yêu cầu, Nhóm phụ trách, Mức độ ưu tiên, Thời hạn SLA, Trạng thái và Điểm đánh giá CSAT.

#### Bước 8.2: Xuất Excel danh sách Công Việc Dự Án (Project Issues)
1. Truy cập vào dự án > Chọn menu **"Work Items" (Issues)** hoặc truy cập: `http://localhost:3000/projects/{id}/issues`.
2. Nhìn lên góc trên bên phải thanh công cụ, bấm nút màu xanh lá **"Xuất Excel"**.
3. **Kết quả:** Tải về tệp bảng tính chứa đầy đủ mã key (`NET-101`), loại công việc (Story, Task, Bug), người phụ trách, Story Points, Original Estimate, và số giờ còn lại.

#### Bước 8.3: Xuất Excel Nhật Ký Kiểm Toán (Audit Logs)
1. Truy cập: `http://localhost:3000/admin/audit-logs`.
2. Bấm nút **"Xuất Excel"**. File tải về chứa toàn bộ dấu vết truy cập, người thực hiện, địa chỉ IP, hành động và giá trị thay đổi trước/sau của toàn bộ hệ thống.

---

> [!TIP]
> **Mẹo Thực Chiến Dành Cho IT Manager:**
> - Vào đầu mỗi buổi sáng (8h30), hãy mở màn hình **Manager Dashboard** và kiểm tra 2 chỉ số quan trọng nhất: Thẻ màu vàng **"Chờ phân công"** và thẻ màu đỏ **"Quá hạn SLA"**.
> - Nếu thấy nhóm nào có chỉ số *"Active"* vượt quá 15 phiếu, hãy lập tức điều phối chuyển giao bớt phiếu cho các nhóm khác đang rảnh rỗi bằng tính năng **Transfer Group**.
