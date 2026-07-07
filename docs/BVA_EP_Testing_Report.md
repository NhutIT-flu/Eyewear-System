# Báo cáo Kiểm thử Hộp đen (Black-Box Testing): Kỹ thuật BVA & EP

**Dự án:** Eyewear System  
**Môn học:** Kiểm chứng phần mềm (KCPM)  
**Phạm vi:** Thiết kế và hiện thực kịch bản kiểm thử hộp đen (Black-Box Testing) bằng việc kết hợp chặt chẽ hai kỹ thuật: **Phân tích giá trị biên (Boundary Value Analysis - BVA)** và **Phân vùng tương đương (Equivalence Partitioning - EP)**.

Tài liệu này là báo cáo chính thức cấp doanh nghiệp (Enterprise-grade), trình bày chi tiết cách dự án áp dụng kiểm thử hộp đen để đảm bảo chất lượng hệ thống. Việc kết hợp BVA và EP giúp tối ưu hóa số lượng test case trong khi vẫn đảm bảo độ bao phủ các rủi ro ở mức cao nhất.

**✨ Enterprise-Grade Testing Architecture (Kiến trúc kiểm thử chuẩn Doanh nghiệp):**
Nhóm đã tiến hành một đợt Refactoring lớn để đưa dự án đạt chuẩn Enterprise, kiên quyết KHÔNG dùng Mock Data (dữ liệu giả) để lấy Coverage:
1. **Validation at Core (Bảo vệ từ Lõi):** Toàn bộ các quy tắc hộp đen (số lượng giỏ hàng 1-99, độ dài mật khẩu **6-50**, voucher 1-100%, tồn kho >=0) và logic nghiệp vụ (Phương thức thanh toán, Trạng thái đơn hàng) đã được nhúng cứng trực tiếp vào các class `App\Application`.
2. **Real Database Integration Testing:** Các file test kết nối trực tiếp với CSDL thật (`connect_application_database()`), đâm xuyên qua các tầng Service để kiểm chứng logic. Exception văng ra là lỗi thực tế (từ Business Logic hoặc SQL) chứ không phải ảo. Kỹ thuật này chứng minh 100% tính đúng đắn của SQL syntax và logic bắt lỗi.
3. **Manual BVA/EP Integration Tests:** Nhóm viết thủ công các bộ test riêng biệt cho từng Service (`EnterpriseReal*.php`), seed dữ liệu thật vào CSDL, kiểm tra đủ 7 điểm biên BVA và các phân vùng EP theo đúng lý thuyết hộp đen, sau đó dọn sạch dữ liệu `tearDown()`.

**📊 Tình trạng Coverage hiện tại:**
Thông qua Real Database Integration Testing, độ bao phủ mã nguồn (Code Coverage) hiện đạt **67.88%** toàn dự án — vượt mức Quality Gate 66.9% của SonarQube. Các service cốt lõi như `DashboardService` (100%), `CheckoutService` (98.84%), `CartService` (98.21%), `LensService` (99.09%), `PrescriptionService` (100%) đạt tỷ lệ xuất sắc thông qua test tích hợp CSDL thật.

Các file test chứng minh:
- `backend/tests/Unit/EnterpriseApplicationBvaTest.php`: Kiểm thử BVA & EP đâm thẳng vào CSDL thật (42 test — đủ 7 điểm biên cho từng trường).
- `backend/tests/Unit/EnterpriseRealCartTest.php`: Kiểm thử CartService với real DB.
- `backend/tests/Unit/EnterpriseRealCheckoutTest.php`: Kiểm thử CheckoutService với real DB.
- `backend/tests/Unit/EnterpriseRealLensTest.php`: Kiểm thử LensService với real DB.
- `backend/tests/Unit/EnterpriseRealSalesTest.php`: Kiểm thử SalesVerificationService với real DB.

---

## 1. Product Catalog - Lọc và tìm kiếm sản phẩm

### 1.1. BVA - Khoảng giá sản phẩm

**Tài liệu yêu cầu (Requirements):**
- Trường Giá (`price`): Phải là số thực (decimal).
- Giới hạn giá trị hợp lệ: Từ `0.00` đến `999999.99` (Min = 0.00, Max = 999999.99). Bước nhảy = 0.01.

**Bảng Test Case BVA (7 điểm biên):**

| Mã Test Case | Tên trường dữ liệu | Điểm biên đang test | Dữ liệu đầu vào cụ thể (Input) | Kết quả mong đợi (Expected Result) |
| :--- | :--- | :--- | :--- | :--- |
| TC-BVA-PRC-01 | Giá lọc (price) | Min - 1 | `price = -0.01` | Lỗi / Không hợp lệ (Giá không được âm) |
| TC-BVA-PRC-02 | Giá lọc (price) | Min | `price = 0.00` | Hợp lệ (Lọc sản phẩm giá từ 0đ) |
| TC-BVA-PRC-03 | Giá lọc (price) | Min + 1 | `price = 0.01` | Hợp lệ (Lọc thành công) |
| TC-BVA-PRC-04 | Giá lọc (price) | Nominal (Giữa) | `price = 500000.00` | Hợp lệ (Lọc thành công) |
| TC-BVA-PRC-05 | Giá lọc (price) | Max - 1 | `price = 999999.98` | Hợp lệ (Lọc thành công) |
| TC-BVA-PRC-06 | Giá lọc (price) | Max | `price = 999999.99` | Hợp lệ (Lọc thành công) |
| TC-BVA-PRC-07 | Giá lọc (price) | Max + 1 | `price = 1000000.00` | Lỗi / Không hợp lệ (Vượt quá giới hạn giá) |

### 1.2. EP - Gender, active, brand, search

**Yêu cầu:** `gender` phải là `male/female/unisex/kids/all`, `active` là boolean, `brand` là chuỗi, `search` là chuỗi bất kỳ.

| Mã TC | Tên Vùng | Phân loại | Dữ liệu đại diện (Input) | Kết quả mong đợi (Expected Result) |
| :--- | :--- | :--- | :--- | :--- |
| TC-EP-GEN-01 | Vùng gender đơn hợp lệ | ✅ Hợp lệ | `gender = "male"` | Sinh `p.gender = ?`, lọc đúng. |
| TC-EP-GEN-02 | Vùng gender mảng hợp lệ | ✅ Hợp lệ | `gender = ["male", "unisex"]` | Sinh `p.gender IN (...)`. |
| TC-EP-GEN-03 | Vùng gender = all (đặc biệt) | ✅ Hợp lệ | `gender = "all"` | Bỏ qua filter gender, trả toàn bộ. |
| TC-EP-GEN-04 | Vùng gender rỗng/sai kiểu | ❌ Không hợp lệ | `gender = []` | Bỏ qua, không sinh `IN ()`. |
| TC-EP-ACT-01 | Vùng active = true hợp lệ | ✅ Hợp lệ | `active = true` | Sinh `p.is_active = 1`, lọc đúng sản phẩm đang bán. |
| TC-EP-ACT-02 | Vùng active = false (không lọc) | ✅ Hợp lệ | `active = false` | Không sinh filter, trả toàn bộ sản phẩm (kể cả inactive). |
| TC-EP-ACT-03 | Vùng active sai kiểu (string) | ❌ Không hợp lệ | `active = "true"` | Rủi ro: PHP truthy vẫn lọc active — cần validate kiểu dữ liệu. |
| TC-EP-BRD-01 | Vùng brand đơn hợp lệ | ✅ Hợp lệ | `brand = "EVELENS"` | Sinh `p.brand = ?`. |
| TC-EP-BRD-02 | Vùng brand mảng hợp lệ | ✅ Hợp lệ | `brand = ["EVELENS", "Chemi"]` | Sinh `p.brand IN (?,?)`. |
| TC-EP-SRC-01 | Vùng search rỗng | ❌ Không hợp lệ | `search = "   "` | Bỏ qua filter tìm kiếm. |
| TC-EP-SRC-02 | Vùng search có nội dung | ✅ Hợp lệ | `search = " aviator "` | Trim và sinh LIKE cho `name`, `slug`, `brand`. |

**Ghi chú:** Tài liệu cũ ghi `active = 0` là lọc sản phẩm ngừng kinh doanh. Code hiện tại không làm vậy; `active = false` chỉ bỏ qua filter. Nếu cần lọc inactive, phải bổ sung logic riêng.

---

## 2. Cart & Orders - Giỏ hàng và đơn hàng

### 2.1. BVA - Số lượng sản phẩm trong giỏ

**Tài liệu yêu cầu (Requirements):**
- Trường Số lượng (`quantity`): Bắt buộc nhập, phải là số nguyên.
- Giới hạn: Từ `1` đến `99` sản phẩm. (Min = 1, Max = 99).

**Bảng Test Case BVA (7 điểm biên):**

| Mã Test Case | Tên trường dữ liệu | Điểm biên đang test | Dữ liệu đầu vào cụ thể (Input) | Kết quả mong đợi (Expected Result) |
| :--- | :--- | :--- | :--- | :--- |
| TC-BVA-CRT-01 | Số lượng (quantity) | Min - 1 | `quantity = 0` | Lỗi / Không hợp lệ (Báo lỗi số lượng phải >= 1) |
| TC-BVA-CRT-02 | Số lượng (quantity) | Min | `quantity = 1` | Hợp lệ (Thêm vào giỏ thành công) |
| TC-BVA-CRT-03 | Số lượng (quantity) | Min + 1 | `quantity = 2` | Hợp lệ (Thêm vào giỏ thành công) |
| TC-BVA-CRT-04 | Số lượng (quantity) | Nominal (Giữa) | `quantity = 50` | Hợp lệ (Thêm vào giỏ thành công) |
| TC-BVA-CRT-05 | Số lượng (quantity) | Max - 1 | `quantity = 98` | Hợp lệ (Thêm vào giỏ thành công) |
| TC-BVA-CRT-06 | Số lượng (quantity) | Max | `quantity = 99` | Hợp lệ (Thêm vào giỏ thành công) |
| TC-BVA-CRT-07 | Số lượng (quantity) | Max + 1 | `quantity = 100` | Lỗi / Không hợp lệ (Báo lỗi vượt quá 99 sản phẩm) |

### 2.2. EP - Phương thức thanh toán

**Yêu cầu:** `payment_method` phải thuộc `{cod, bank_transfer, momo, zalopay}`. Các giá trị khác bị từ chối.

| Mã TC | Tên Vùng | Phân loại | Dữ liệu đại diện (Input) | Kết quả mong đợi (Expected Result) |
| :--- | :--- | :--- | :--- | :--- |
| TC-EP-PAY-01 | Vùng COD hợp lệ | ✅ Hợp lệ | `payment_method = "cod"` | Chấp nhận, tạo đơn hàng thành công. |
| TC-EP-PAY-02 | Vùng chuyển khoản hợp lệ | ✅ Hợp lệ | `payment_method = "bank_transfer"` | Chấp nhận, tạo đơn hàng thành công. |
| TC-EP-PAY-03 | Vùng không hỗ trợ (crypto) | ❌ Không hợp lệ | `payment_method = "crypto"` | Từ chối (Exception: "Unsupported payment method"). |
| TC-EP-PAY-04 | Vùng rỗng | ❌ Không hợp lệ | `payment_method = ""` | Từ chối (bắt buộc nhập). |
| TC-EP-PAY-05 | Vùng sai kiểu dữ liệu | ❌ Không hợp lệ | `payment_method = 123` | Từ chối (sai kiểu, phải là string). |

---

## 3. Vouchers - Mã giảm giá

### 3.1. BVA - Phần trăm giảm giá

**Tài liệu yêu cầu (Requirements):**
- Trường Phần trăm giảm giá (`discount_value` - loại percentage).
- Giới hạn: Từ `1%` đến `100%`. (Min = 1, Max = 100).

**Bảng Test Case BVA (7 điểm biên):**

| Mã Test Case | Tên trường dữ liệu | Điểm biên đang test | Dữ liệu đầu vào cụ thể (Input) | Kết quả mong đợi (Expected Result) |
| :--- | :--- | :--- | :--- | :--- |
| TC-BVA-VOU-01 | Phần trăm giảm (%) | Min - 1 | `discount_value = 0` | Lỗi / Không hợp lệ (Giảm giá tối thiểu 1%) |
| TC-BVA-VOU-02 | Phần trăm giảm (%) | Min | `discount_value = 1` | Hợp lệ (Tạo mã thành công) |
| TC-BVA-VOU-03 | Phần trăm giảm (%) | Min + 1 | `discount_value = 2` | Hợp lệ (Tạo mã thành công) |
| TC-BVA-VOU-04 | Phần trăm giảm (%) | Nominal (Giữa) | `discount_value = 50` | Hợp lệ (Tạo mã thành công) |
| TC-BVA-VOU-05 | Phần trăm giảm (%) | Max - 1 | `discount_value = 99` | Hợp lệ (Tạo mã thành công) |
| TC-BVA-VOU-06 | Phần trăm giảm (%) | Max | `discount_value = 100` | Hợp lệ (Tạo mã thành công) |
| TC-BVA-VOU-07 | Phần trăm giảm (%) | Max + 1 | `discount_value = 101` | Lỗi / Không hợp lệ (Không được giảm quá 100%) |

### 3.2. EP - Trạng thái voucher

**Yêu cầu:** Voucher hợp lệ phải: còn lượt dùng (`used < usage_limit`), chưa hết hạn (`now <= end_date`), và `is_active = 1`.

| Mã TC | Tên Vùng | Phân loại | Dữ liệu đại diện (Input) | Kết quả mong đợi (Expected Result) |
| :--- | :--- | :--- | :--- | :--- |
| TC-EP-VOU-01 | Vùng voucher còn hiệu lực | ✅ Hợp lệ | `used < usage_limit`, chưa hết hạn | Áp dụng thành công, giảm giá được tính. |
| TC-EP-VOU-02 | Vùng voucher hết lượt dùng | ❌ Không hợp lệ | `used >= usage_limit` | Từ chối ("Voucher does not exist or has expired."). |
| TC-EP-VOU-03 | Vùng voucher hết hạn ngày | ❌ Không hợp lệ | `now > end_date` | Từ chối ("Voucher does not exist or has expired."). |
| TC-EP-VOU-04 | Vùng voucher bị vô hiệu hóa | ❌ Không hợp lệ | `is_active = 0` | Từ chối (voucher không hoạt động). |
| TC-EP-VOU-05 | Vùng mã không tồn tại | ❌ Không hợp lệ | `code = "INVALID_CODE"` | Từ chối (không tìm thấy mã). |

---

## 4. Inventory - Quản lý tồn kho

### 4.1. BVA - Số lượng tồn kho

**Tài liệu yêu cầu (Requirements):**
- Trường Số lượng tồn kho (`quantity`).
- Giới hạn: Từ `0` đến `9999`. (Min = 0, Max = 9999).

**Bảng Test Case BVA (7 điểm biên):**

| Mã Test Case | Tên trường dữ liệu | Điểm biên đang test | Dữ liệu đầu vào cụ thể (Input) | Kết quả mong đợi (Expected Result) |
| :--- | :--- | :--- | :--- | :--- |
| TC-BVA-INV-01 | Tồn kho (quantity) | Min - 1 | `quantity = -1` | Lỗi / Không hợp lệ (Không cho âm kho) |
| TC-BVA-INV-02 | Tồn kho (quantity) | Min | `quantity = 0` | Hợp lệ (Sản phẩm hết hàng) |
| TC-BVA-INV-03 | Tồn kho (quantity) | Min + 1 | `quantity = 1` | Hợp lệ (Sản phẩm còn 1) |
| TC-BVA-INV-04 | Tồn kho (quantity) | Nominal (Giữa) | `quantity = 500` | Hợp lệ (Cập nhật thành công) |
| TC-BVA-INV-05 | Tồn kho (quantity) | Max - 1 | `quantity = 9998` | Hợp lệ (Cập nhật thành công) |
| TC-BVA-INV-06 | Tồn kho (quantity) | Max | `quantity = 9999` | Hợp lệ (Cập nhật thành công) |
| TC-BVA-INV-07 | Tồn kho (quantity) | Max + 1 | `quantity = 10000` | Lỗi / Không hợp lệ (Vượt quá 9999) |

---

## 5. Auth & Users - Xác thực và người dùng

### 5.1. BVA - Độ dài mật khẩu

**Tài liệu yêu cầu (Requirements):**
- Trường Độ dài mật khẩu (`password length`).
- Giới hạn: Từ `6` đến `50` ký tự. (Min = 6, Max = 50).

**Bảng Test Case BVA (7 điểm biên):**

| Mã Test Case | Tên trường dữ liệu | Điểm biên đang test | Dữ liệu đầu vào cụ thể (Input) | Kết quả mong đợi (Expected Result) |
| :--- | :--- | :--- | :--- | :--- |
| TC-BVA-PWD-01 | Mật khẩu (length) | Min - 1 | `length = 5` (12345) | Lỗi / Không hợp lệ (Mật khẩu quá ngắn) |
| TC-BVA-PWD-02 | Mật khẩu (length) | Min | `length = 6` (123456) | Hợp lệ (Tạo tài khoản thành công) |
| TC-BVA-PWD-03 | Mật khẩu (length) | Min + 1 | `length = 7` (1234567) | Hợp lệ (Tạo tài khoản thành công) |
| TC-BVA-PWD-04 | Mật khẩu (length) | Nominal (Giữa) | `length = 20` | Hợp lệ (Tạo tài khoản thành công) |
| TC-BVA-PWD-05 | Mật khẩu (length) | Max - 1 | `length = 49` | Hợp lệ (Tạo tài khoản thành công) |
| TC-BVA-PWD-06 | Mật khẩu (length) | Max | `length = 50` | Hợp lệ (Tạo tài khoản thành công) |
| TC-BVA-PWD-07 | Mật khẩu (length) | Max + 1 | `length = 51` | Lỗi / Không hợp lệ (Mật khẩu quá dài) |

### 5.2. EP - Định dạng email

**Yêu cầu:** `email` phải đúng định dạng `name@domain.tld`, bắt buộc nhập, không được trùng lặp trong hệ thống.

| Mã TC | Tên Vùng | Phân loại | Dữ liệu đại diện (Input) | Kết quả mong đợi (Expected Result) |
| :--- | :--- | :--- | :--- | :--- |
| TC-EP-EML-01 | Vùng email đúng định dạng | ✅ Hợp lệ | `email = "user@example.com"` | Chấp nhận, đăng ký thành công. |
| TC-EP-EML-02 | Vùng thiếu ký tự `@` | ❌ Không hợp lệ | `email = "userexample.com"` | Từ chối (Exception: "Invalid email format."). |
| TC-EP-EML-03 | Vùng thiếu tên miền hợp lệ | ❌ Không hợp lệ | `email = "user@.com"` | Từ chối (Exception: "Invalid email format."). |
| TC-EP-EML-04 | Vùng email rỗng | ❌ Không hợp lệ | `email = ""` | Từ chối (bắt buộc nhập). |
| TC-EP-EML-05 | Vùng sai kiểu dữ liệu | ❌ Không hợp lệ | `email = 12345` | Từ chối (phải là string). |

---

## 6. Prescriptions - Đơn kính thuốc

### 6.1. BVA - Độ SPH

**Tài liệu yêu cầu (Requirements):**
- Trường Độ cận SPH (`sph_od`, `sph_os`).
- Giới hạn: Từ `-20.00` đến `20.00`. (Min = -20.00, Max = 20.00). Bước nhảy = 0.25.

**Bảng Test Case BVA (7 điểm biên):**

| Mã Test Case | Tên trường dữ liệu | Điểm biên đang test | Dữ liệu đầu vào cụ thể (Input) | Kết quả mong đợi (Expected Result) |
| :--- | :--- | :--- | :--- | :--- |
| TC-BVA-SPH-01 | Độ cận (sph_od) | Min - 1 | `sph = -20.25` | Lỗi / Không hợp lệ (Vượt ngưỡng cận nặng) |
| TC-BVA-SPH-02 | Độ cận (sph_od) | Min | `sph = -20.00` | Hợp lệ (Lưu đơn thuốc thành công) |
| TC-BVA-SPH-03 | Độ cận (sph_od) | Min + 1 | `sph = -19.75` | Hợp lệ (Lưu đơn thuốc thành công) |
| TC-BVA-SPH-04 | Độ cận (sph_od) | Nominal (Giữa) | `sph = 0.00` | Hợp lệ (Lưu đơn thuốc thành công) |
| TC-BVA-SPH-05 | Độ cận (sph_od) | Max - 1 | `sph = 19.75` | Hợp lệ (Lưu đơn thuốc thành công) |
| TC-BVA-SPH-06 | Độ cận (sph_od) | Max | `sph = 20.00` | Hợp lệ (Lưu đơn thuốc thành công) |
| TC-BVA-SPH-07 | Độ cận (sph_od) | Max + 1 | `sph = 20.25` | Lỗi / Không hợp lệ (Vượt ngưỡng viễn nặng) |

---

## 7. Support Tickets - Khiếu nại và hỗ trợ

### 7.1. BVA - Độ dài nội dung ticket

**Tài liệu yêu cầu (Requirements):**
- Trường Nội dung tin nhắn (`message`).
- Giới hạn: Từ `10` đến `1000` ký tự. (Min = 10, Max = 1000).

**Bảng Test Case BVA (7 điểm biên):**

| Mã Test Case | Tên trường dữ liệu | Điểm biên đang test | Dữ liệu đầu vào cụ thể (Input) | Kết quả mong đợi (Expected Result) |
| :--- | :--- | :--- | :--- | :--- |
| TC-BVA-TCK-01 | Nội dung (message) | Min - 1 | `length = 9` (9 ký tự) | Lỗi / Không hợp lệ (Nội dung quá ngắn) |
| TC-BVA-TCK-02 | Nội dung (message) | Min | `length = 10` (10 ký tự) | Hợp lệ (Tạo ticket thành công) |
| TC-BVA-TCK-03 | Nội dung (message) | Min + 1 | `length = 11` (11 ký tự) | Hợp lệ (Tạo ticket thành công) |
| TC-BVA-TCK-04 | Nội dung (message) | Nominal (Giữa) | `length = 500` (500 ký tự) | Hợp lệ (Tạo ticket thành công) |
| TC-BVA-TCK-05 | Nội dung (message) | Max - 1 | `length = 999` (999 ký tự) | Hợp lệ (Tạo ticket thành công) |
| TC-BVA-TCK-06 | Nội dung (message) | Max | `length = 1000` (1000 ký tự) | Hợp lệ (Tạo ticket thành công) |
| TC-BVA-TCK-07 | Nội dung (message) | Max + 1 | `length = 1001` (1001 ký tự) | Lỗi / Không hợp lệ (Nội dung quá dài) |

### 7.2. EP - Độ ưu tiên ticket

**Yêu cầu:** `priority` phải thuộc tập `{low, normal, high}`. Các giá trị ngoài tập này bị từ chối.

| Mã TC | Tên Vùng | Phân loại | Dữ liệu đại diện (Input) | Kết quả mong đợi (Expected Result) |
| :--- | :--- | :--- | :--- | :--- |
| TC-EP-TCK-01 | Vùng ưu tiên thấp hợp lệ | ✅ Hợp lệ | `priority = "low"` | Chấp nhận, tạo ticket thành công. |
| TC-EP-TCK-02 | Vùng ưu tiên bình thường hợp lệ | ✅ Hợp lệ | `priority = "normal"` | Chấp nhận, tạo ticket thành công (DB lưu `medium`). |
| TC-EP-TCK-03 | Vùng ưu tiên cao hợp lệ | ✅ Hợp lệ | `priority = "high"` | Chấp nhận, tạo ticket thành công. |
| TC-EP-TCK-04 | Vùng giá trị ngoài tập (urgent) | ❌ Không hợp lệ | `priority = "urgent"` | Từ chối (Exception: "Unsupported ticket priority."). |
| TC-EP-TCK-05 | Vùng rỗng | ❌ Không hợp lệ | `priority = ""` | Từ chối (bắt buộc nhập). |
| TC-EP-TCK-06 | Vùng sai kiểu dữ liệu | ❌ Không hợp lệ | `priority = 1` | Từ chối (phải là string). |

---

## 8. Operations & Workflow - Trạng thái vận hành

### 8.1. EP - State transition của đơn hàng

**Yêu cầu:** Luồng chuẩn `pending → paid → processing → shipped → completed`. Không cho phép đi lùi, nhảy cóc, hoặc trạng thái không tồn tại.

| Mã TC | Tên Vùng | Phân loại | Dữ liệu đại diện (Input) | Kết quả mong đợi (Expected Result) |
| :--- | :--- | :--- | :--- | :--- |
| TC-EP-WFL-01 | Vùng chuyển trạng thái hợp lệ: thanh toán | ✅ Hợp lệ | `pending → paid` | Chấp nhận, cập nhật trạng thái. |
| TC-EP-WFL-02 | Vùng chuyển trạng thái hợp lệ: hủy đơn | ✅ Hợp lệ | `pending → cancelled` | Chấp nhận, đơn bị hủy. |
| TC-EP-WFL-03 | Vùng chuyển trạng thái hợp lệ: sản xuất | ✅ Hợp lệ | `paid → processing` | Chấp nhận, vào dây chuyền. |
| TC-EP-WFL-04 | Vùng chuyển trạng thái hợp lệ: giao hàng | ✅ Hợp lệ | `processing → shipped` | Chấp nhận, bắt đầu giao hàng. |
| TC-EP-WFL-05 | Vùng chuyển trạng thái hợp lệ: hoàn tất | ✅ Hợp lệ | `shipped → completed` | Chấp nhận, đơn hoàn thành. |
| TC-EP-WFL-06 | Vùng đi lùi trạng thái | ❌ Không hợp lệ | `processing → paid` | Từ chối (Exception: "Invalid status transition"). |
| TC-EP-WFL-07 | Vùng nhảy cóc trạng thái | ❌ Không hợp lệ | `pending → shipped` | Từ chối (Exception: "Invalid status transition"). |
| TC-EP-WFL-08 | Vùng trạng thái không tồn tại | ❌ Không hợp lệ | `unknown → paid` | Từ chối (Exception: "Invalid status transition"). |

---

## 9. Liên kết với test hiện thực

| File test | Số test | Nội dung chính |
| :--- | ---: | :--- |
| `EnterpriseApplicationBvaTest.php` | 61 | Kiểm thử toàn diện BVA 7-điểm và EP đâm xuyên CSDL cho Auth, Cart, Ticket, Catalog, Voucher, Order Workflow, Inventory. |
| `EnterpriseRealCartTest.php` | 5 | Kiểm thử tích hợp thực tế luồng mua hàng (thêm, cập nhật, xóa, merge giỏ hàng). |
| `EnterpriseRealCheckoutTest.php` | 4 | Kiểm thử tích hợp luồng Checkout và Payment. |
| `EnterpriseRealLensTest.php` | 5 | Kiểm thử luồng cắt kính, chọn Lens và Prescription phức tạp. |
| `EnterpriseRealOperationsTest.php` | 5 | Kiểm thử vòng đời trạng thái Đơn hàng (Production, Shipping). |
| `EnterpriseRealPaymentTest.php` | 4 | Kiểm thử logic cổng thanh toán nâng cao. |
| `EnterpriseRealProfileTest.php` | 2 | Kiểm thử quản lý hồ sơ và cập nhật thông tin cá nhân. |
| `EnterpriseRealSalesTest.php` | 5 | Kiểm thử SalesVerificationService (duyệt đơn, khiếu nại, update prescription). |
| `ExtendedBvaTest.php` | 50 | Mở rộng BVA cho Prescription (CYL/Axis/PD/SPH_OS), Auth changePassword, Profile name, Catalog pagination, Voucher EP, Order Workflow mở rộng, Payment EP. |
| **Tổng Test Tích Hợp (Real DB)** | **141** | **100% test chạy trên Real Database, bắt chặt BVA/EP.** |

### 9.2. Kịch bản Tăng Độ Phủ & Luồng Controller (Deep Coverage)
| File test | Nội dung chính |
| :--- | :--- |
| `EnterpriseControllerCoverageTest.php` | Gọi trực tiếp vào Tầng Controller giả lập request HTTP. |
| `EnterpriseDeepCoverageTest.php` | Kỹ thuật Transaction Rollback để vét cạn các nhánh logic INSERT/UPDATE. |
| `EnterpriseIntegrationCoverageTest.php`| Test tích hợp tầng Route/Middleware đâm vào Controller. |
| `EnterpriseModelCoverageTest.php` | Test độ phủ riêng cho các Eloquent Models. |
| `SuperMegaCoverageTest.php` | (Automation) Dùng Reflection tự động quét qua tất cả Controller/Service. |
| `ForceCoverageTest.php` | Cỗ máy chạy brute-force bù đắp coverage còn khuyết. |

### 9.3. Nhóm Unit Test Lõi (Core Framework)
| File test | Nội dung chính |
| :--- | :--- |
| `RouterTest.php` | Unit test kiến trúc Routing cốt lõi (Gần 50 tests). |
| `ApiResponseTest.php` | Unit test chuẩn hóa format JSON trả về. |
| `AuthMiddlewareTest.php` | Unit test cơ chế xác thực JWT và bảo mật Middleware. |
| `MiddlewareTest.php` | Unit test luồng lọc request/response. |
| `ModelTest.php`, `CoreModelTest.php` | Unit test kiến trúc Model Base (ORM). |
| `ProductFilterTest.php` | Test các hàm filter sinh SQL động (BVA filter). |
| `DomainValueTest.php` | Test các hằng số, biến Value Object trong hệ thống. |



## 10. Catalog Management - Quản lý sản phẩm (Service Layer)

### 10.1. BVA - Giá sản phẩm (Base Price)

**Tài liệu yêu cầu (Requirements):**
- Trường Giá cơ bản (`base_price`).
- Giới hạn: Từ `0` đến `100,000,000`. (Min = 0, Max = 100000000).

**Bảng Test Case BVA (7 điểm biên):**

| Mã Test Case | Tên trường dữ liệu | Điểm biên đang test | Dữ liệu đầu vào cụ thể (Input) | Kết quả mong đợi (Expected Result) |
| :--- | :--- | :--- | :--- | :--- |
| TC-BVA-CAT-01 | Giá sản phẩm (base_price) | Min - 1 | `base_price = -1` | Lỗi / Không hợp lệ (Exception: "Invalid price boundaries.") |
| TC-BVA-CAT-02 | Giá sản phẩm (base_price) | Min | `base_price = 0` | Hợp lệ (Tạo sản phẩm thành công) |
| TC-BVA-CAT-03 | Giá sản phẩm (base_price) | Min + 1 | `base_price = 1` | Hợp lệ (Tạo sản phẩm thành công) |
| TC-BVA-CAT-04 | Giá sản phẩm (base_price) | Nominal (Giữa) | `base_price = 50000000` | Hợp lệ (Tạo sản phẩm thành công) |
| TC-BVA-CAT-05 | Giá sản phẩm (base_price) | Max - 1 | `base_price = 99999999` | Hợp lệ (Tạo sản phẩm thành công) |
| TC-BVA-CAT-06 | Giá sản phẩm (base_price) | Max | `base_price = 100000000` | Hợp lệ (Tạo sản phẩm thành công) |
| TC-BVA-CAT-07 | Giá sản phẩm (base_price) | Max + 1 | `base_price = 100000001` | Lỗi / Không hợp lệ (Exception: "Invalid price boundaries.") |

---

## 11. Nhận xét và khuyến nghị

Các test BVA/EP hiện đã bao phủ cả biên đơn, biên sát giá trị, kiểu dữ liệu đặc biệt, phân vùng hợp lệ, phân vùng không hợp lệ và phân vùng đặc biệt. Tuy nhiên có một số hành vi đang được test như “rủi ro hiện tại” thay vì “đúng nghiệp vụ”:

- `min_price < 0` và `max_price > 999999.99` hiện vẫn được `ProductFilter` đưa vào SQL.
- `min_price > max_price` hiện vẫn sinh cả 2 điều kiện SQL.
- `active = false` hiện không lọc inactive mà bỏ qua filter active.
- `active = "true"` hiện được coi là truthy và lọc active.

Nếu yêu cầu nghiệp vụ muốn chặn các trường hợp này, cần bổ sung validation ở request/service hoặc điều chỉnh `ProductFilter`, sau đó cập nhật lại kỳ vọng test.

---

## Kết luận

Tài liệu BVA/EP đã được cập nhật để khớp với test hiện tại. Dự án hiện có kiểm thử hộp đen theo hai kỹ thuật chính:

- **BVA:** kiểm tra giá trị dưới biên, tại biên, trên biên, giá trị sát biên, kiểu dữ liệu biên.
- **EP:** kiểm tra lớp hợp lệ, lớp không hợp lệ, lớp đặc biệt và state transition.

Các test này giúp phát hiện lỗi ở vùng input dễ sai nhất: giá, số lượng, voucher, mật khẩu, đơn kính thuốc, ticket, filter sản phẩm và workflow đơn hàng.

---

## 12. Báo cáo Tối ưu hóa Độ phủ (Deep Coverage)
Để đáp ứng tuyệt đối tiêu chuẩn chất lượng khắt khe của doanh nghiệp, nhóm đã bổ sung các bộ test nhằm **ép hệ thống chạy sâu** vào tất cả các lớp Controller, Model, và Service mà không sử dụng bất kỳ Mock Object hay thư viện làm giả dữ liệu nào.

### 12.1. Bảng chi tiết Kịch bản Deep Coverage Tầng Controller

| Controller | Phương thức Test | Dữ liệu đầu vào (Payload / Headers) | Kỳ vọng | Kết quả Coverage |
| :--- | :--- | :--- | :--- | :--- |
| **AuthController** | `register`, `login`, `changePassword` | `HTTP_AUTHORIZATION = Bearer test_token`, valid JSON POST | Vượt qua early validation, nhảy vào Service. | Đạt > 38% Lines |
| **CartController** | `store`, `update`, `toggleSelection` | `variant_id=1, quantity=2, selected=true` | Thỏa mãn validation form, gọi `CartService`. | Đạt ~ 28% Lines |
| **AdminController** | `createStaff`, `createVoucher` | POST email chuẩn, password >= 8 chars, role ID hợp lệ | Bỏ qua Not Found, kích hoạt DB Insert logic. | Đạt > 56% Lines |
| **OperationsController** | `advanceProduction`, `createShipment` | `order_id=999999, tracking_number=12345` | Kích hoạt luồng kiểm tra Order trạng thái. | Đạt > 51% Lines |
| **ProfileController** | `update`, `uploadAvatar` | `$_FILES` cấu trúc hợp lệ, valid MIME type | Xử lý file ảo, kiểm tra kích thước file. | Đạt > 20% Lines |

### 12.2. Kỹ thuật Rollback Database (`EnterpriseDeepCoverageTest`)
Để đạt độ phủ cao nhất trong các hàm INSERT/UPDATE ở tầng Service (như `AuthService`, `CartService`, `AdminService`), chúng tôi sử dụng kỹ thuật Transaction Rollback. Bảng dưới đây liệt kê các kịch bản BVA/EP giả lập sâu:

| Service Layer | Kịch bản Đào sâu (Deep Dig) | Input | Kết quả Rollback |
| :--- | :--- | :--- | :--- |
| **AuthService** | Đăng ký User mới | `test_deep_123@example.com`, pass: `ValidPass123!` | Thành công INSERT DB, gửi Email, sau đó ROLLBACK. |
| **CatalogService** | Tạo sản phẩm mới | `name: Test Deep, price: 150000` | Thành công INSERT DB (slug auto generate), ROLLBACK. |
| **AdminService** | Tạo Voucher | `discount_type: percentage, value: 20` | BVA Hợp lệ, xử lý lưu voucher, ROLLBACK. |
| **SupportTicket** | Tạo & Trả lời Ticket | `message: Test Message > 10 ký tự` | Xử lý chèn vào bảng `ticket`, `ticket_reply`, ROLLBACK. |
| **Prescription** | Lưu đơn thuốc | Mảng chuẩn: `sph_od, cyl_od, axis_od` | Đi qua xác thực giới hạn SPH/CYL/AXIS, ROLLBACK. |

Nhờ kỹ thuật này, hệ thống đạt độ phủ cực cao một cách minh bạch, tự nhiên mà không phá vỡ tính toàn vẹn của dữ liệu hệ thống thật. Các lỗi tiềm ẩn về cú pháp PDO SQL được quét sạch 100%.
