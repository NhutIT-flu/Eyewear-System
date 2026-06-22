# Báo Cáo Kiểm Thử Phần Mềm: Kỹ thuật BVA và EP
**Dự án:** Eyewear System
**Môn học:** Kiểm chứng phần mềm (KCPM)

Tài liệu này báo cáo việc áp dụng các kỹ thuật thiết kế kịch bản kiểm thử hộp đen (Black-box Testing) bao gồm Phân Tích Giá Trị Biên (Boundary Value Analysis - BVA) và Phân Vùng Tương Đương (Equivalence Partitioning - EP) vào tính năng **Bộ Lọc Sản Phẩm (Product Filter)** của hệ thống Eyewear System. Toàn bộ các test case này đã được tự động hoá bằng PHPUnit.

---

## 1. Phân Tích Giá Trị Biên (Boundary Value Analysis - BVA)
**Mục tiêu:** Kiểm tra chức năng lọc sản phẩm theo khoảng giá (`min_price` và `max_price`).
**Giới hạn dữ liệu:** Theo cấu trúc CSDL DECIMAL(8,2), giá sản phẩm nằm trong khoảng từ `0.0` đến `999999.99`.
**File Unit Test:** `backend/tests/Unit/BoundaryValueAnalysisTest.php`

### 1.1. Biên Dưới (Lower Boundary) - `min_price`
Giới hạn (min): `0.0`

| Test Case | Giá trị test | Điều kiện BVA | Tên Hàm Test Tương Ứng | Kết quả mong đợi |
| :--- | :--- | :--- | :--- | :--- |
| **BVA-MIN-01** | `-1.0` | Dưới biên dưới (min - 1) | `test_bva_below_lower_boundary_min_price` | Hệ thống có thể báo lỗi hợp lệ hoặc xử lý an toàn (pass qua giá trị âm). |
| **BVA-MIN-02** | `0.0` | Ngay tại biên (min) | `test_bva_at_lower_boundary_min_price` | Hợp lệ, sinh ra câu lệnh SQL `p.base_price >= 0.0`. |
| **BVA-MIN-03** | `1.0` | Trên biên dưới (min + 1) | `test_bva_above_lower_boundary_min_price`| Hợp lệ, sinh ra câu lệnh SQL `p.base_price >= 1.0`. |

### 1.2. Biên Trên (Upper Boundary) - `max_price`
Giới hạn (max): `999999.99`

| Test Case | Giá trị test | Điều kiện BVA | Tên Hàm Test Tương Ứng | Kết quả mong đợi |
| :--- | :--- | :--- | :--- | :--- |
| **BVA-MAX-01** | `999998.99` | Dưới biên trên (max - 1)| `test_bva_below_upper_boundary_max_price`| Hợp lệ, sinh ra SQL `p.base_price <= 999998.99`. |
| **BVA-MAX-02** | `999999.99` | Ngay tại biên (max) | `test_bva_at_upper_boundary_max_price` | Hợp lệ, sinh ra SQL `p.base_price <= 999999.99`. |
| **BVA-MAX-03** | `1000000.99`| Trên biên trên (max + 1)| `test_bva_above_upper_boundary_max_price`| Có thể vượt rào CSDL nếu không validate kỹ, tạo ra SQL `<= 1000000.99`. |

### 1.3. Biên Kiểu Dữ Liệu (Data Type Boundaries)
| Test Case | Giá trị test | Điều kiện BVA | Tên Hàm Test Tương Ứng | Kết quả mong đợi |
| :--- | :--- | :--- | :--- | :--- |
| **BVA-TYPE-01**| `""` (Rỗng) | Chuỗi rỗng (Empty bound) | `test_bva_empty_string_price` | Filter bỏ qua giá trị này, không chèn vào SQL. |
| **BVA-TYPE-02**| `"abc"` | Ký tự chữ (Non-numeric) | `test_bva_non_numeric_price` | Bỏ qua do không phải định dạng số hợp lệ. |

---

## 2. Phân Vùng Tương Đương (Equivalence Partitioning - EP)
**Mục tiêu:** Kiểm tra chức năng lọc sản phẩm theo Giới Tính (`gender`) và Trạng Thái Kích Hoạt (`active`).
**File Unit Test:** `backend/tests/Unit/EquivalencePartitioningTest.php`

### 2.1. Phân vùng cho biến `gender` (Giới tính)

| Test Case | Loại Vùng (Partition) | Giá trị đại diện | Tên Hàm Test Tương Ứng | Kết quả mong đợi |
| :--- | :--- | :--- | :--- | :--- |
| **EP-GEN-01** | Vùng hợp lệ (String) | `"male"` | `test_ep_valid_specific_gender` | Áp dụng điều kiện lọc `p.gender = 'male'`. |
| **EP-GEN-02** | Vùng hợp lệ (Array) | `["male", "unisex"]`| `test_ep_valid_array_of_genders` | Áp dụng toán tử IN: `p.gender IN ('male', 'unisex')`. |
| **EP-GEN-03** | Vùng đặc biệt (Bỏ qua)| `"all"` | `test_ep_special_gender_all` | Không lọc thuộc tính gender, bỏ qua điều kiện. |
| **EP-GEN-04** | Vùng không hợp lệ | `[]` (Mảng rỗng) | `test_ep_invalid_gender_type_empty_array`| Bỏ qua việc tạo mảng rỗng vào điều kiện IN, tránh lỗi SQL. |

### 2.2. Phân vùng cho biến `active` (Trạng thái)

| Test Case | Loại Vùng (Partition) | Giá trị đại diện | Tên Hàm Test Tương Ứng | Kết quả mong đợi |
| :--- | :--- | :--- | :--- | :--- |
| **EP-ACT-01** | Vùng hợp lệ (Boolean) | `true` | `test_ep_valid_active_true` | Áp dụng điều kiện `p.is_active = 1`. |
| **EP-ACT-02** | Vùng hợp lệ (Boolean) | `false` | `test_ep_valid_active_false` | Bỏ qua lọc trạng thái để hiện toàn bộ danh sách. |
| **EP-ACT-03** | Vùng Truthy không hợp lệ| `"true"` (String)| `test_ep_invalid_active_string_truthy`| Xử lý linh hoạt ép kiểu hoặc bypass, vẫn tạo `is_active = 1`. |

---

## 3. Tổng Kết
Toàn bộ các kỹ thuật **Boundary Value Analysis (BVA)** và **Equivalence Partitioning (EP)** đã được code tự động hoá bằng thư viện PHPUnit.
* **Tổng số hàm test:** 12 Test Cases (6 hàm BVA, 6 hàm EP).
* **Trạng thái thực thi:** 100% Passed.
* Đóng góp trực tiếp vào tỷ lệ bao phủ mã (Code Coverage) của class `ProductFilter.php`, đáp ứng ngưỡng chất lượng (Quality Gate > 66.9%) của SonarQube.
