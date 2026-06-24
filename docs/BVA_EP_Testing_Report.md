# Báo Cáo Kiểm Thử Phần Mềm: Kỹ thuật BVA và EP
**Dự án:** Eyewear System
**Môn học:** Kiểm chứng phần mềm (KCPM)

Tài liệu này trình bày bảng thiết kế Test Case hộp đen (Black-box Testing) sử dụng **Phân Tích Giá Trị Biên (BVA)** và **Phân Vùng Tương Đương (EP)** phủ toàn bộ các Module/Domain của dự án Eyewear System.

---

## 1. Lọc & Tìm Kiếm Sản Phẩm (Product Catalog)
**Mục tiêu:** Lọc theo khoảng giá.
**Giới hạn:** `min_price` và `max_price` nằm trong `[0.0, 999999.99]`.

### 1.1. BVA - Khoảng Giá
| Test ID | Đầu vào (Input) | Điều kiện BVA | Kết quả mong đợi |
| :--- | :--- | :--- | :--- |
| **BVA-PRC-01** | `min = -1.0` | Dưới biên dưới (min - 1) | API báo lỗi `400` hoặc tự động loại bỏ giá âm. |
| **BVA-PRC-02** | `min = 0.0` | Tại biên dưới (min) | Trả về sản phẩm (bao gồm cả hàng 0đ/quà tặng). |
| **BVA-PRC-03** | `max = 999999.99`| Tại biên trên (max) | Trả về các sản phẩm đắt nhất hệ thống. |
| **BVA-PRC-04** | `max = 1000000` | Trên biên trên (max + 1)| Vượt hạn mức Decimal, báo lỗi hoặc chặn. |
| **BVA-PRC-05** | `min=100`, `max=50`| `min > max` (Tổ hợp) | Trả về danh sách rỗng (Không hợp lệ logic). |

### 1.2. EP - Trạng Thái Sản Phẩm (`active`)
| Test ID | Phân Vùng (Partition) | Đại diện | Kết quả mong đợi |
| :--- | :--- | :--- | :--- |
| **EP-ACT-01**| Đang kinh doanh (Hợp lệ) | `1` | Chỉ lấy các sản phẩm có `active = 1`. |
| **EP-ACT-02**| Ngừng kinh doanh (Hợp lệ)| `0` | Chỉ lấy các sản phẩm có `active = 0` (Dành cho Admin).|

---

## 2. Quản Lý Giỏ Hàng & Đơn Hàng (Cart & Orders)
**Mục tiêu:** Thêm vào giỏ và đặt hàng.
**Giới hạn Số Lượng (Qty):** Mỗi sản phẩm mua từ `1` đến `99` cái.

### 2.1. BVA - Số Lượng Thêm Vào Giỏ (`quantity`)
| Test ID | Đầu vào (Input) | Điều kiện BVA | Kết quả mong đợi |
| :--- | :--- | :--- | :--- |
| **BVA-CRT-01** | `qty = 0` | Dưới biên (min - 1) | Lỗi `400`: Số lượng phải lớn hơn 0. |
| **BVA-CRT-02** | `qty = 1` | Tại biên (min) | Thêm thành công 1 sản phẩm vào giỏ. |
| **BVA-CRT-03** | `qty = 99` | Tại biên (max) | Thêm thành công 99 sản phẩm vào giỏ. |
| **BVA-CRT-04** | `qty = 100`| Trên biên (max + 1) | Lỗi `400`: Vượt quá giới hạn mua tối đa mỗi lần. |

### 2.2. EP - Phương Thức Thanh Toán (`payment_method`)
| Test ID | Phân Vùng (Partition) | Đại diện | Kết quả mong đợi |
| :--- | :--- | :--- | :--- |
| **EP-PAY-01**| Phương thức COD | `"cod"` | Đơn hàng chờ xử lý (`pending`). |
| **EP-PAY-02**| Phương thức Chuyển khoản| `"bank_transfer"`| Đơn hàng chờ xử lý, chờ xác nhận bill từ kế toán. |
| **EP-PAY-03**| Phương thức không hỗ trợ| `"crypto"` | Lỗi `422`: Phương thức không tồn tại. |

---

## 3. Hệ Thống Mã Giảm Giá (Vouchers)
**Mục tiêu:** Áp dụng chiết khấu (%).
**Giới hạn Phần Trăm (`discount_percent`):** Từ `1%` đến `100%`.

### 3.1. BVA - Giá trị giảm giá
| Test ID | Đầu vào (Input) | Điều kiện BVA | Kết quả mong đợi |
| :--- | :--- | :--- | :--- |
| **BVA-VOU-01** | `discount = 0` | Dưới biên (min - 1) | Lỗi `400`: % giảm giá không hợp lệ. |
| **BVA-VOU-02** | `discount = 1` | Tại biên (min) | Giảm 1% trên tổng hoá đơn. |
| **BVA-VOU-03** | `discount = 100`| Tại biên (max) | Giảm 100% (Hoá đơn thành 0đ). |
| **BVA-VOU-04** | `discount = 101`| Trên biên (max + 1) | Lỗi `400`: Mức giảm không được quá 100%. |

### 3.2. EP - Logic Hợp Lệ Của Voucher
| Test ID | Phân Vùng (Partition) | Điều Kiện | Kết quả mong đợi |
| :--- | :--- | :--- | :--- |
| **EP-VOU-01**| Hợp lệ hoàn toàn | `usage_limit > used` & `now() < end_date` | Áp mã thành công. |
| **EP-VOU-02**| Lỗi: Hết lượt | `usage_limit = used` | Từ chối áp mã (Đã hết số lượng). |
| **EP-VOU-03**| Lỗi: Hết hạn | `now() > end_date` | Từ chối áp mã (Đã quá hạn). |

---

## 4. Quản Lý Tồn Kho (Inventory)
**Giới hạn:** Số lượng kho không được âm (`>= 0`).

### 4.1. BVA - Cập Nhật Tồn Kho
| Test ID | Đầu vào (Input) | Điều kiện BVA | Kết quả mong đợi |
| :--- | :--- | :--- | :--- |
| **BVA-INV-01** | `stock = -1` | Dưới biên (min - 1) | Lỗi `400`: Kho không được âm. Giao dịch bị rollback. |
| **BVA-INV-02** | `stock = 0` | Tại biên (min) | Cập nhật kho thành công, tự chuyển trạng thái sản phẩm thành `Out of Stock`. |
| **BVA-INV-03** | `stock = 1` | Trên biên (min + 1) | Cập nhật kho thành công, sản phẩm ở trạng thái `In Stock`. |

---

## 5. Xác Thực & Người Dùng (Auth & Users)
**Giới hạn:** Độ dài mật khẩu (Password) từ `8` đến `50` ký tự.

### 5.1. BVA - Độ Dài Mật Khẩu
| Test ID | Đầu vào (Input) | Điều kiện BVA | Kết quả mong đợi |
| :--- | :--- | :--- | :--- |
| **BVA-PWD-01** | `length = 7` | Dưới biên (min - 1) | Báo lỗi mật khẩu quá ngắn. |
| **BVA-PWD-02** | `length = 8` | Tại biên (min) | Đăng ký thành công. |
| **BVA-PWD-03** | `length = 50`| Tại biên (max) | Đăng ký thành công. |
| **BVA-PWD-04** | `length = 51`| Trên biên (max + 1) | Báo lỗi mật khẩu quá dài. |

### 5.2. EP - Định Dạng Email
| Test ID | Phân Vùng (Partition) | Đại diện | Kết quả mong đợi |
| :--- | :--- | :--- | :--- |
| **EP-EML-01**| Email hợp lệ chuẩn | `user@example.com` | Hợp lệ, tiến hành đăng ký. |
| **EP-EML-02**| Mất ký tự `@` | `userexample.com` | Lỗi định dạng Email. |
| **EP-EML-03**| Thiếu tên miền | `user@.com` | Lỗi định dạng Email. |

---

## 6. Đơn Kính Thuốc (Prescriptions)
**Giới hạn:** Độ cận/viễn (`sph`) nằm trong `[-20.00, +10.00]`.

### 6.1. BVA - Độ SPH
| Test ID | Đầu vào (Input) | Điều kiện BVA | Kết quả mong đợi |
| :--- | :--- | :--- | :--- |
| **BVA-SPH-01** | `sph = -20.25`| Dưới biên (min - 1) | Lỗi: Nằm ngoài giới hạn sản xuất của phòng Lab. |
| **BVA-SPH-02** | `sph = -20.00`| Tại biên dưới (min) | Hợp lệ, tạo đơn kính độ cận cực sâu thành công. |
| **BVA-SPH-03** | `sph = +10.00`| Tại biên trên (max) | Hợp lệ, tạo đơn kính độ viễn nặng thành công. |
| **BVA-SPH-04** | `sph = +10.25`| Trên biên trên (max+1) | Lỗi: Vượt quá giới hạn viễn thị cho phép. |

---

## 7. Hệ Thống Khiếu Nại (Support Tickets)
**Giới hạn:** Nội dung Ticket phải từ `10` đến `1000` ký tự.

### 7.1. BVA - Độ Dài Nội Dung (Content)
| Test ID | Đầu vào (Input) | Điều kiện BVA | Kết quả mong đợi |
| :--- | :--- | :--- | :--- |
| **BVA-TCK-01** | Ký tự = `9` | Dưới biên (min - 1) | Lỗi: Nội dung khiếu nại quá ngắn (Không đủ chi tiết). |
| **BVA-TCK-02** | Ký tự = `10` | Tại biên (min) | Gửi khiếu nại thành công. |
| **BVA-TCK-03** | Ký tự = `1000`| Tại biên (max) | Gửi khiếu nại thành công (Dài nhất có thể). |
| **BVA-TCK-04** | Ký tự = `1001`| Trên biên (max + 1) | Lỗi: Nội dung vượt quá số lượng từ cho phép. |

---

## 8. Workflow Vận Hành (Operations & Workflow)
**Mục tiêu:** Quá trình Cắt kính (Production) và Giao hàng (Shipment).

### 8.1. EP - Luồng Trạng Thái Đơn Hàng (State Machine Transitions)
Theo thiết kế luồng: `pending` -> `paid` -> `processing` -> `shipped` -> `completed`.

| Test ID | Phân Vùng (Transitions) | Thay Đổi Trạng Thái | Kết quả mong đợi |
| :--- | :--- | :--- | :--- |
| **EP-WFL-01**| Luồng Hợp lệ (Tiến lên)| `paid` -> `processing` | Success: Chuyển sang khâu sản xuất thành công. |
| **EP-WFL-02**| Luồng Hợp lệ (Hủy) | `pending` -> `cancelled`| Success: Đơn hàng bị hủy hợp lệ. |
| **EP-WFL-03**| Luồng Lỗi (Lùi lại) | `processing` -> `paid` | Lỗi 400: Không cho phép đi ngược quy trình. |
| **EP-WFL-04**| Luồng Lỗi (Nhảy cóc) | `pending` -> `shipped` | Lỗi 400: Thiếu bước xác nhận và sản xuất. |

---

**Kết Luận:** 
Toàn bộ dự án đã được thiết kế test case chi tiết phủ 8 domains quan trọng nhất bằng BVA và EP. Tất cả được tích hợp tự động qua GitHub Actions và Unit Testing.
