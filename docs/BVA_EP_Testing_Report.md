# Báo cáo kiểm thử phần mềm: BVA và EP

**Dự án:** Eyewear System  
**Môn học:** Kiểm chứng phần mềm (KCPM)  
**Phạm vi:** Thiết kế và hiện thực test case hộp đen bằng **Boundary Value Analysis (BVA)** và **Equivalence Partitioning (EP)**.

Tài liệu này mô tả các lớp kiểm thử BVA/EP đang được áp dụng trong dự án và được đồng bộ với các file test:

- `backend/tests/Unit/BoundaryValueAnalysisTest.php`: 29 test BVA
- `backend/tests/Unit/EquivalencePartitioningTest.php`: 19 test EP
- `backend/tests/Unit/ComprehensiveBvaEpTest.php`: 13 test BVA/EP tổng hợp theo nghiệp vụ

---

## 1. Product Catalog - Lọc và tìm kiếm sản phẩm

### 1.1. BVA - Khoảng giá sản phẩm

**Mục tiêu:** Kiểm thử bộ lọc `min_price`, `max_price`, `price_min`, `price_max`.  
**Miền hợp lệ:** `0.00 <= price <= 999999.99`.  
**Bước tiền tệ nhỏ nhất dùng trong test:** `0.01`.

| Test ID | Input | Điều kiện BVA | Kết quả mong đợi / Hành vi hiện tại |
| :--- | :--- | :--- | :--- |
| BVA-PRC-01 | `min_price = -1.00` | Dưới biên dưới | Hiện tại filter vẫn đưa vào SQL; đây là rủi ro cần validate ở tầng request/service. |
| BVA-PRC-02 | `min_price = -0.01` | Ngay dưới biên dưới | Hiện tại filter vẫn đưa vào SQL; dùng để phát hiện thiếu chặn giá âm. |
| BVA-PRC-03 | `min_price = 0.00` | Tại biên dưới | Chấp nhận, sinh điều kiện `p.base_price >= ?`. |
| BVA-PRC-04 | `min_price = 0.01` | Ngay trên biên dưới | Chấp nhận. |
| BVA-PRC-05 | `max_price = 999998.99` | Dưới biên trên | Chấp nhận. |
| BVA-PRC-06 | `max_price = 999999.98` | Ngay dưới biên trên | Chấp nhận. |
| BVA-PRC-07 | `max_price = 999999.99` | Tại biên trên | Chấp nhận. |
| BVA-PRC-08 | `max_price = 1000000.00` | Ngay trên biên trên | Hiện tại filter vẫn đưa vào SQL; cần validate nếu DB giới hạn `DECIMAL(8,2)`. |
| BVA-PRC-09 | `max_price = 1000000.99` | Trên biên trên | Hiện tại filter vẫn đưa vào SQL; đây là rủi ro vượt miền nghiệp vụ. |
| BVA-PRC-10 | `min_price = 100`, `max_price = 50` | Tổ hợp sai `min > max` | Hiện tại vẫn sinh cả 2 điều kiện; nghiệp vụ nên trả rỗng hoặc báo lỗi. |
| BVA-PRC-11 | `min_price = 100`, `max_price = 100` | `min = max` | Chấp nhận, lọc đúng sản phẩm có giá bằng 100. |
| BVA-PRC-12 | `price_min = "0.01"`, `price_max = "999999.99"` | Alias input | Chấp nhận alias và sinh điều kiện khoảng giá. |
| BVA-PRC-13 | `min_price = "0"` | Chuỗi số tại biên | Chấp nhận, ép kiểu thành `0.0`. |
| BVA-PRC-14 | `max_price = " 999999.99 "` | Chuỗi số có khoảng trắng | PHP coi là numeric, filter chấp nhận. |
| BVA-PRC-15 | `min_price = ""` | Rỗng | Bỏ qua, không sinh điều kiện giá. |
| BVA-PRC-16 | `min_price = null` | Null | Bỏ qua, không sinh điều kiện giá. |
| BVA-PRC-17 | `min_price = "abc"` | Sai kiểu số | Bỏ qua, không sinh điều kiện giá. |

### 1.2. EP - Gender, active, brand, search

| Test ID | Partition | Đại diện | Kết quả mong đợi / Hành vi hiện tại |
| :--- | :--- | :--- | :--- |
| EP-GEN-01 | Gender đơn hợp lệ | `male`, `Men` | Sinh `p.gender = ?`, chuẩn hóa chuỗi đơn về chữ thường. |
| EP-GEN-02 | Gender nhiều giá trị | `["male", "unisex"]`, `["men", "women", "unisex"]` | Sinh `p.gender IN (...)`. |
| EP-GEN-03 | Gender đặc biệt: tất cả | `all` | Bỏ qua filter gender. |
| EP-GEN-04 | Gender rỗng/sai kiểu | `[]` | Bỏ qua, không sinh `IN ()`. |
| EP-ACT-01 | Active hợp lệ | `true` | Sinh `p.is_active = 1`. |
| EP-ACT-02 | Inactive/không lọc active | `false` | Hành vi hiện tại: không sinh filter `p.is_active`. |
| EP-ACT-03 | Truthy string | `"true"` | Hành vi hiện tại: vẫn sinh `p.is_active = 1` do PHP coi là truthy. |
| EP-BRD-01 | Brand đơn | `EVELENS` | Sinh `p.brand = ?`. |
| EP-BRD-02 | Brand nhiều giá trị | `["EVELENS", "Chemi"]` | Sinh `p.brand IN (?,?)`. |
| EP-SRC-01 | Search rỗng | `"   "` | Bỏ qua filter tìm kiếm. |
| EP-SRC-02 | Search có nội dung | `" aviator "` | Trim chuỗi và sinh LIKE cho `name`, `model_name`, `slug`, `brand`. |

**Ghi chú:** Tài liệu cũ ghi `active = 0` là lọc sản phẩm ngừng kinh doanh. Code hiện tại không làm vậy; `active = false` chỉ bỏ qua filter. Nếu cần lọc inactive, phải bổ sung logic riêng.

---

## 2. Cart & Orders - Giỏ hàng và đơn hàng

### 2.1. BVA - Số lượng sản phẩm trong giỏ

**Miền hợp lệ:** số nguyên `1 <= quantity <= 99`.

| Test ID | Input | Điều kiện BVA | Kết quả mong đợi |
| :--- | :--- | :--- | :--- |
| BVA-CRT-01 | `quantity = 0` | Dưới biên dưới | Không hợp lệ. |
| BVA-CRT-02 | `quantity = 1` | Tại biên dưới | Hợp lệ. |
| BVA-CRT-03 | `quantity = 99` | Tại biên trên | Hợp lệ. |
| BVA-CRT-04 | `quantity = 100` | Trên biên trên | Không hợp lệ. |
| BVA-CRT-05 | `quantity = 0.99` | Ngay dưới biên nhưng số lẻ | Không hợp lệ vì quantity phải là số nguyên. |
| BVA-CRT-06 | `quantity = 1.5` | Trong miền số học nhưng sai kiểu | Không hợp lệ vì quantity phải là số nguyên. |
| BVA-CRT-07 | `quantity = 99.01` | Ngay trên biên trên, số lẻ | Không hợp lệ. |

### 2.2. EP - Phương thức thanh toán

| Test ID | Partition | Đại diện | Kết quả mong đợi |
| :--- | :--- | :--- | :--- |
| EP-PAY-01 | COD hợp lệ | `cod` | Chấp nhận. |
| EP-PAY-02 | Chuyển khoản hợp lệ | `bank_transfer` | Chấp nhận. |
| EP-PAY-03 | Không hỗ trợ | `crypto` | Từ chối. |
| EP-PAY-04 | Rỗng | `""` | Từ chối. |

---

## 3. Vouchers - Mã giảm giá

### 3.1. BVA - Phần trăm giảm giá

**Miền hợp lệ:** `1 <= discount_percent <= 100`.

| Test ID | Input | Điều kiện BVA | Kết quả mong đợi |
| :--- | :--- | :--- | :--- |
| BVA-VOU-01 | `discount = 0` | Dưới biên dưới | Không hợp lệ. |
| BVA-VOU-02 | `discount = 0.99` | Ngay dưới biên dưới | Không hợp lệ. |
| BVA-VOU-03 | `discount = 1` | Tại biên dưới | Hợp lệ. |
| BVA-VOU-04 | `discount = 1.01` | Ngay trên biên dưới | Hợp lệ. |
| BVA-VOU-05 | `discount = 99.99` | Ngay dưới biên trên | Hợp lệ. |
| BVA-VOU-06 | `discount = 100` | Tại biên trên | Hợp lệ. |
| BVA-VOU-07 | `discount = 100.01` | Ngay trên biên trên | Không hợp lệ. |
| BVA-VOU-08 | `discount = 101` | Trên biên trên | Không hợp lệ. |

### 3.2. EP - Trạng thái voucher

| Test ID | Partition | Điều kiện đại diện | Kết quả mong đợi |
| :--- | :--- | :--- | :--- |
| EP-VOU-01 | Hợp lệ | `used < usage_limit` và chưa hết hạn | Áp dụng thành công. |
| EP-VOU-02 | Hết lượt | `used >= usage_limit` | Từ chối. |
| EP-VOU-03 | Hết hạn | `now > end_date` | Từ chối. |

---

## 4. Inventory - Quản lý tồn kho

### 4.1. BVA - Số lượng tồn kho

**Miền hợp lệ:** `stock >= 0`.

| Test ID | Input | Điều kiện BVA | Kết quả mong đợi |
| :--- | :--- | :--- | :--- |
| BVA-INV-01 | `stock = -1` | Dưới biên dưới | Không hợp lệ, không cho âm kho. |
| BVA-INV-02 | `stock = 0` | Tại biên dưới | Hợp lệ, sản phẩm có thể hết hàng. |
| BVA-INV-03 | `stock = 1` | Ngay trên biên dưới | Hợp lệ, sản phẩm còn hàng. |

---

## 5. Auth & Users - Xác thực và người dùng

### 5.1. BVA - Độ dài mật khẩu

**Miền hợp lệ:** `8 <= password_length <= 50`.

| Test ID | Input | Điều kiện BVA | Kết quả mong đợi |
| :--- | :--- | :--- | :--- |
| BVA-PWD-01 | `length = 7` | Dưới biên dưới | Không hợp lệ. |
| BVA-PWD-02 | `length = 8` | Tại biên dưới | Hợp lệ. |
| BVA-PWD-03 | `length = 50` | Tại biên trên | Hợp lệ. |
| BVA-PWD-04 | `length = 51` | Trên biên trên | Không hợp lệ. |

### 5.2. EP - Định dạng email

| Test ID | Partition | Đại diện | Kết quả mong đợi |
| :--- | :--- | :--- | :--- |
| EP-EML-01 | Email hợp lệ | `user@example.com` | Chấp nhận. |
| EP-EML-02 | Thiếu `@` | `userexample.com` | Từ chối. |
| EP-EML-03 | Thiếu tên miền hợp lệ | `user@.com` | Từ chối. |
| EP-EML-04 | Rỗng | `""` | Từ chối. |

---

## 6. Prescriptions - Đơn kính thuốc

### 6.1. BVA - Độ SPH

**Miền hợp lệ:** `-20.00 <= sph <= 10.00`.  
**Bước đại diện dùng trong test:** `0.25`.

| Test ID | Input | Điều kiện BVA | Kết quả mong đợi |
| :--- | :--- | :--- | :--- |
| BVA-SPH-01 | `sph = -20.50` | Xa dưới biên dưới | Không hợp lệ. |
| BVA-SPH-02 | `sph = -20.25` | Ngay dưới biên dưới | Không hợp lệ. |
| BVA-SPH-03 | `sph = -20.00` | Tại biên dưới | Hợp lệ. |
| BVA-SPH-04 | `sph = -19.75` | Ngay trên biên dưới | Hợp lệ. |
| BVA-SPH-05 | `sph = 9.75` | Ngay dưới biên trên | Hợp lệ. |
| BVA-SPH-06 | `sph = 10.00` | Tại biên trên | Hợp lệ. |
| BVA-SPH-07 | `sph = 10.25` | Ngay trên biên trên | Không hợp lệ. |
| BVA-SPH-08 | `sph = 10.50` | Xa trên biên trên | Không hợp lệ. |

---

## 7. Support Tickets - Khiếu nại và hỗ trợ

### 7.1. BVA - Độ dài nội dung ticket

**Miền hợp lệ:** `10 <= content_length <= 1000`.

| Test ID | Input | Điều kiện BVA | Kết quả mong đợi |
| :--- | :--- | :--- | :--- |
| BVA-TCK-01 | `length = 9` | Dưới biên dưới | Không hợp lệ. |
| BVA-TCK-02 | `length = 10` | Tại biên dưới | Hợp lệ. |
| BVA-TCK-03 | `length = 1000` | Tại biên trên | Hợp lệ. |
| BVA-TCK-04 | `length = 1001` | Trên biên trên | Không hợp lệ. |

### 7.2. EP - Độ ưu tiên ticket

| Test ID | Partition | Đại diện | Kết quả mong đợi |
| :--- | :--- | :--- | :--- |
| EP-TCK-01 | Ưu tiên thấp hợp lệ | `low` | Chấp nhận. |
| EP-TCK-02 | Ưu tiên bình thường hợp lệ | `normal` | Chấp nhận. |
| EP-TCK-03 | Ưu tiên cao hợp lệ | `high` | Chấp nhận. |
| EP-TCK-04 | Giá trị không hỗ trợ | `urgent` | Từ chối. |
| EP-TCK-05 | Rỗng | `""` | Từ chối. |

---

## 8. Operations & Workflow - Trạng thái vận hành

### 8.1. EP - State transition của đơn hàng

**Luồng chuẩn:** `pending -> paid -> processing -> shipped -> completed`.

| Test ID | Partition | Transition | Kết quả mong đợi |
| :--- | :--- | :--- | :--- |
| EP-WFL-01 | Hợp lệ: thanh toán | `pending -> paid` | Chấp nhận. |
| EP-WFL-02 | Hợp lệ: hủy đơn | `pending -> cancelled` | Chấp nhận. |
| EP-WFL-03 | Hợp lệ: sang sản xuất | `paid -> processing` | Chấp nhận. |
| EP-WFL-04 | Hợp lệ: giao hàng | `processing -> shipped` | Chấp nhận. |
| EP-WFL-05 | Hợp lệ: hoàn tất | `shipped -> completed` | Chấp nhận. |
| EP-WFL-06 | Không hợp lệ: đi lùi | `processing -> paid` | Từ chối. |
| EP-WFL-07 | Không hợp lệ: nhảy cóc | `pending -> shipped` | Từ chối. |
| EP-WFL-08 | Không hợp lệ: trạng thái không tồn tại | `unknown -> paid` | Từ chối. |

---

## 9. Liên kết với test hiện thực

| File test | Số test | Nội dung chính |
| :--- | ---: | :--- |
| `BoundaryValueAnalysisTest.php` | 29 | BVA cho giá, alias giá, kiểu dữ liệu giá, quantity, discount, password, SPH, ticket content, stock. |
| `EquivalencePartitioningTest.php` | 19 | EP cho gender, active, brand, search, payment, voucher, email, state transition, ticket priority. |
| `ComprehensiveBvaEpTest.php` | 13 | Test tổng hợp theo 8 domain nghiệp vụ, dùng để minh họa BVA/EP ở mức hộp đen. |

Tổng số test BVA/EP trực tiếp trong 2 file chính: **48 test**.  
Tổng số test BVA/EP nếu tính thêm file tổng hợp: **61 test**.

---

## 10. Nhận xét và khuyến nghị

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
