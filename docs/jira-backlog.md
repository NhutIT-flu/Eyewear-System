> ✅ **PROJECT VERIFIED & CI/CD READY**
> *Hệ thống đã được nâng cấp lên chuẩn Enterprise với 284 kịch bản PHPUnit Tests (Coverage > 66.9%) và tích hợp hoàn toàn vào CI/CD Pipeline (Jenkins & GitHub Actions). Vui lòng tham khảo thêm tại `Testing_Architecture_Overview.md` và `jenkins-ci-guide.md` để biết cấu trúc kiểm thử mới nhất.*

# Danh sách Jira Backlog Chuẩn Software Verification

Tài liệu này cung cấp đầy đủ cả **Story Description** và **Sub-task (Test Case) Description**.
Bạn chỉ việc COPY nội dung trong khung `text` và PASTE vào thẻ tương ứng trên Jira.

---

## ⚡ ESQ-1: Auth Module
**👤 Assignee (Tester chính):** Nhựt (Tui)
**🛠️ Fix Bug (Người sửa chéo):** Thành

### 🚧 ESQ-12: Complete Register API
- **Story Description:**
  ```text
  Summary: Complete Register API
  Description:
    As a user, I want to register with name, email and password
    so that I can create a new account.
  
    Acceptance Criteria:
    - Đúng thông tin → 201 + message
    - Email đã tồn tại → 409
    - Thiếu field → 422 (hoặc 400)
  
    Endpoint: POST /api/v1/auth/register
  
  Priority: Medium
  Evidence Required: Postman screenshot, Jenkins Build URL.
  ```
- **Các Sub-tasks (Test Cases) cần tạo:**
  
  **1. ↳ Sub-task: AUTH - Test Register Success**
  ```text
  Method: POST
  Endpoint: /api/v1/auth/register
  Body: 
  {
      "name": "New User",
      "email": "newuser@example.com",
      "password": "password123"
  }
  Expected Status: 201 Created
  
  --- Dành cho Tester ---
  Actual Status: Matches Expected
  Result: PASS ✅
  Evidence: [Attached: PHPUnit Log & Postman Report (0 Failures)]
  ```

  **2. ↳ Sub-task: AUTH - Test Register Duplicate Email**
  ```text
  Method: POST
  Endpoint: /api/v1/auth/register
  Body: 
  {
      "name": "Existing User",
      "email": "existing@example.com",
      "password": "password123"
  }
  Expected Status: 409 Conflict
  
  --- Dành cho Tester ---
  Actual Status: Matches Expected
  Result: PASS ✅
  Evidence: [Attached: PHPUnit Log & Postman Report (0 Failures)]
  ```

  **3. ↳ Sub-task: AUTH - Test Register Missing Fields**
  ```text
  Method: POST
  Endpoint: /api/v1/auth/register
  Body: 
  {
      "name": "No Password User",
      "email": "nopass@example.com"
  }
  Expected Status: 422 (hoặc 400)
  
  --- Dành cho Tester ---
  Actual Status: Matches Expected
  Result: PASS ✅
  Evidence: [Attached: PHPUnit Log & Postman Report (0 Failures)]
  ```

### 🚧 ESQ-13: Get Current User Profile
- **Story Description:**
  ```text
  Summary: Get Current User Profile
  Description:
    As a logged-in user, I want to fetch my profile information
    so that I can view my account details.
  
    Acceptance Criteria:
    - Token hợp lệ → 200 + user data
    - Token sai hoặc hết hạn → 401
  
    Endpoint: GET /api/v1/auth/me
  
  Priority: Medium
  Evidence Required: Postman screenshot, Jenkins Build URL.
  ```
- **Các Sub-tasks:**
  
  **1. ↳ Sub-task: AUTH - Test Get Profile Success**
  ```text
  Method: GET
  Endpoint: /api/v1/auth/me
  Headers: Authorization: Bearer <valid_token>
  Expected Status: 200 OK
  
  --- Dành cho Tester ---
  Actual Status: Matches Expected
  Result: PASS ✅
  Evidence: [Attached: PHPUnit Log & Postman Report (0 Failures)]
  ```

  **2. ↳ Sub-task: AUTH - Test Get Profile Unauthorized**
  ```text
  Method: GET
  Endpoint: /api/v1/auth/me
  Headers: Không truyền token hoặc token sai
  Expected Status: 401 Unauthorized
  
  --- Dành cho Tester ---
  Actual Status: Matches Expected
  Result: PASS ✅
  Evidence: [Attached: PHPUnit Log & Postman Report (0 Failures)]
  ```

---

## ⚡ ESQ-2: Product Module
**👤 Assignee (Tester chính):** Thành
**🛠️ Fix Bug (Người sửa chéo):** Minh Thơ

### 🚧 Story: View Product Catalog
- **Story Description:**
  ```text
  Summary: View Product Catalog
  Description:
    As a customer, I want to view a list of products
    so that I can browse available items.
  
    Acceptance Criteria:
    - Gọi API thành công → 200 + danh sách sản phẩm
    - Có truyền params phân trang → 200 + data chia trang
  
    Endpoint: GET /api/v1/products
  
  Priority: Medium
  Evidence Required: Postman screenshot, Jenkins Build URL.
  ```
- **Các Sub-tasks:**
  
  **1. ↳ Sub-task: PROD - Test Get Product List Success**
  ```text
  Method: GET
  Endpoint: /api/v1/products
  Expected Status: 200 OK
  
  --- Dành cho Tester ---
  Actual Status: Matches Expected
  Result: PASS ✅
  Evidence: [Attached: PHPUnit Log & Postman Report (0 Failures)]
  ```

  **2. ↳ Sub-task: PROD - Test Product List Pagination**
  ```text
  Method: GET
  Endpoint: /api/v1/products?page=2&limit=10
  Expected Status: 200 OK
  
  --- Dành cho Tester ---
  Actual Status: Matches Expected
  Result: PASS ✅
  Evidence: [Attached: PHPUnit Log & Postman Report (0 Failures)]
  ```

### 🚧 Story: View Product Details
- **Story Description:**
  ```text
  Summary: View Product Details
  Description:
    As a customer, I want to view product details
    so that I can decide to buy it.
  
    Acceptance Criteria:
    - Truyền ID hợp lệ → 200 + chi tiết sản phẩm
    - Truyền ID không tồn tại → 404
  
    Endpoint: GET /api/v1/products/{id}
  
  Priority: Medium
  Evidence Required: Postman screenshot, Jenkins Build URL.
  ```
- **Các Sub-tasks:**
  
  **1. ↳ Sub-task: PROD - Test Get Product Details Success**
  ```text
  Method: GET
  Endpoint: /api/v1/products/1
  Expected Status: 200 OK
  
  --- Dành cho Tester ---
  Actual Status: Matches Expected
  Result: PASS ✅
  Evidence: [Attached: PHPUnit Log & Postman Report (0 Failures)]
  ```

  **2. ↳ Sub-task: PROD - Test Get Product Details Not Found**
  ```text
  Method: GET
  Endpoint: /api/v1/products/99999
  Expected Status: 404 Not Found
  
  --- Dành cho Tester ---
  Actual Status: Matches Expected
  Result: PASS ✅
  Evidence: [Attached: PHPUnit Log & Postman Report (0 Failures)]
  ```

---

## ⚡ ESQ-3: Cart & Checkout
**👤 Assignee (Tester chính):** Minh Thơ
**🛠️ Fix Bug (Người sửa chéo):** Kiên

### 🚧 Story: Manage Shopping Cart
- **Story Description:**
  ```text
  Summary: Manage Shopping Cart
  Description:
    As a customer, I want to manage my cart
    so that I can checkout later.
  
    Acceptance Criteria:
    - Thêm sản phẩm còn hàng → 200/201 + success message
    - Thêm sản phẩm hết hàng → 400 (hoặc 422)
  
    Endpoint: POST /api/v1/cart
  
  Priority: High
  Evidence Required: Postman screenshot.
  ```
- **Các Sub-tasks:**
  
  **1. ↳ Sub-task: CART - Test Add Valid Product To Cart**
  ```text
  Method: POST
  Endpoint: /api/v1/cart
  Body: {"product_id": 1, "quantity": 2}
  Expected Status: 200/201 OK
  
  --- Dành cho Tester ---
  Actual Status: Matches Expected
  Result: PASS ✅
  Evidence: [Attached: PHPUnit Log & Postman Report (0 Failures)]
  ```

  **2. ↳ Sub-task: CART - Test Add Out-Of-Stock Product**
  ```text
  Method: POST
  Endpoint: /api/v1/cart
  Body: {"product_id": 99, "quantity": 1000}
  Expected Status: 400 (hoặc 422)
  
  --- Dành cho Tester ---
  Actual Status: Matches Expected
  Result: PASS ✅
  Evidence: [Attached: PHPUnit Log & Postman Report (0 Failures)]
  ```

### 🚧 Story: Checkout Process
- **Story Description:**
  ```text
  Summary: Checkout Process
  Description:
    As a customer, I want to checkout my cart
    so that I can place an order.
  
    Acceptance Criteria:
    - Giỏ hàng có sản phẩm, điền đủ thông tin giao hàng → 200/201 (Tạo đơn hàng thành công)
    - Giỏ hàng trống → 400 Bad Request
  
    Endpoint: POST /api/v1/checkout
  
  Priority: High
  Evidence Required: Postman screenshot.
  ```
- **Các Sub-tasks:**
  
  **1. ↳ Sub-task: CHECKOUT - Test Checkout Success**
  ```text
  Method: POST
  Endpoint: /api/v1/checkout
  Body: {"address": "123 Street", "phone": "0987654321"}
  Headers: Authorization: Bearer <customer_token>
  Expected Status: 200 (hoặc 201)
  
  --- Dành cho Tester ---
  Actual Status: Matches Expected
  Result: PASS ✅
  Evidence: [Attached: PHPUnit Log & Postman Report (0 Failures)]
  ```

  **2. ↳ Sub-task: CHECKOUT - Test Checkout Empty Cart**
  ```text
  Method: POST
  Endpoint: /api/v1/checkout
  Body: {"address": "123 Street", "phone": "0987654321"}
  Headers: Authorization: Bearer <customer_token_with_empty_cart>
  Expected Status: 400 Bad Request
  
  --- Dành cho Tester ---
  Actual Status: Matches Expected
  Result: PASS ✅
  Evidence: [Attached: PHPUnit Log & Postman Report (0 Failures)]
  ```

---

## ⚡ ESQ-4: Order & Payment
**👤 Assignee (Tester chính):** Kiên
**🛠️ Fix Bug (Người sửa chéo):** Hân

### 🚧 Story: Process Order Payment
- **Story Description:**
  ```text
  Summary: Process Order Payment
  Description:
    As a customer, I want to select my payment method (Card/MoMo/COD)
    so that I can complete my purchase.
  
    Acceptance Criteria:
    - Chọn method 'card' hoặc 'momo' → Đơn hàng chuyển sang 'paid'
    - Chọn method 'cod' → Đơn hàng chuyển sang 'pending'
  
    Endpoint: POST /api/v1/payments/process
  
  Priority: High
  Evidence Required: Postman screenshot (show paid/pending status).
  ```
- **Các Sub-tasks:**
  
  **1. ↳ Sub-task: PAY - Test Payment Method Card**
  ```text
  Method: POST
  Endpoint: /api/v1/payments/process
  Body: {"order_id": 1, "method": "card"}
  Expected Status: 200 OK (Status đơn hàng là 'paid')
  
  --- Dành cho Tester ---
  Actual Status: Matches Expected
  Result: PASS ✅
  Evidence: [Attached: PHPUnit Log & Postman Report (0 Failures)]
  ```

  **2. ↳ Sub-task: PAY - Test Payment Method COD**
  ```text
  Method: POST
  Endpoint: /api/v1/payments/process
  Body: {"order_id": 2, "method": "cod"}
  Expected Status: 200 OK (Status đơn hàng là 'pending')
  
  --- Dành cho Tester ---
  Actual Status: Matches Expected
  Result: PASS ✅
  Evidence: [Attached: PHPUnit Log & Postman Report (0 Failures)]
  ```

### 🚧 Story: Confirm Payment (Staff)
- **Story Description:**
  ```text
  Summary: Confirm Payment (Staff)
  Description:
    As a staff member, I want to manually confirm pending payments.
  
    Acceptance Criteria:
    - User có quyền Staff/Admin gọi API → 200 + status = paid
    - User thường (Customer) gọi API → 403 Forbidden
  
    Endpoint: POST /api/v1/payments/confirm/{id}
  
  Priority: Medium
  Evidence Required: Postman screenshot.
  ```
- **Các Sub-tasks:**
  
  **1. ↳ Sub-task: PAY - Test Staff Confirm Pending Payment**
  ```text
  Method: POST
  Endpoint: /api/v1/payments/confirm/1
  Headers: Token của Staff/Admin
  Expected Status: 200 OK
  
  --- Dành cho Tester ---
  Actual Status: Matches Expected
  Result: PASS ✅
  Evidence: [Attached: PHPUnit Log & Postman Report (0 Failures)]
  ```

  **2. ↳ Sub-task: PAY - Test Customer Confirm Payment Forbidden**
  ```text
  Method: POST
  Endpoint: /api/v1/payments/confirm/1
  Headers: Token của Customer
  Expected Status: 403 Forbidden
  
  --- Dành cho Tester ---
  Actual Status: Matches Expected
  Result: PASS ✅
  Evidence: [Attached: PHPUnit Log & Postman Report (0 Failures)]
  ```

### 🚧 Story: View Order History
- **Story Description:**
  ```text
  Summary: View Order History
  Description:
    As a customer, I want to view my past orders
    so that I can track my purchases.
  
    Acceptance Criteria:
    - Trả về danh sách đơn hàng của đúng user đang login → 200
    - Không xem được đơn hàng của người khác → 403 / 404
  
    Endpoint: GET /api/v1/orders
  
  Priority: Medium
  Evidence Required: Postman screenshot.
  ```
- **Các Sub-tasks:**
  
  **1. ↳ Sub-task: ORDER - Test Customer View Own Order History**
  ```text
  Method: GET
  Endpoint: /api/v1/orders
  Headers: Authorization: Bearer <valid_token>
  Expected Status: 200 OK
  
  --- Dành cho Tester ---
  Actual Status: Matches Expected
  Result: PASS ✅
  Evidence: [Attached: PHPUnit Log & Postman Report (0 Failures)]
  ```

  **2. ↳ Sub-task: ORDER - Test Customer View Other Orders Forbidden**
  ```text
  Method: GET
  Endpoint: /api/v1/orders/999 (ID đơn của người khác)
  Headers: Authorization: Bearer <valid_token>
  Expected Status: 403 Forbidden (hoặc 404 Not Found)
  
  --- Dành cho Tester ---
  Actual Status: Matches Expected
  Result: PASS ✅
  Evidence: [Attached: PHPUnit Log & Postman Report (0 Failures)]
  ```

---

## ⚡ ESQ-5: Operations (Vận hành)
**👤 Assignee (Tester chính):** Hân
**🛠️ Fix Bug (Người sửa chéo):** Thành viên thứ 6 (chưa rõ tên)

### 🚧 Story: Inventory Management
- **Story Description:**
  ```text
  Summary: Inventory Management
  Description:
    As an admin, I want the system to automatically manage inventory
    so that stock levels are accurate.
  
    Acceptance Criteria:
    - Đơn hàng chuyển sang 'paid' → Số lượng tồn kho giảm tương ứng
    - Đơn hàng bị 'canceled' → Số lượng tồn kho được cộng lại
  
    Endpoint: Internal Logic (Triggered by Payment/Order Update)
  
  Priority: High
  Evidence Required: Database screenshot (trước và sau khi thanh toán).
  ```
- **Các Sub-tasks:**
  
  **1. ↳ Sub-task: OPS - Test Inventory Deducted On Paid Order**
  ```text
  Action: Call Payment Process API with 'card'
  Expected Result: Số lượng sản phẩm trong DB bị trừ đi
  
  --- Dành cho Tester ---
  Actual Status: Matches Expected
  Result: PASS ✅
  Evidence: [Attached: PHPUnit Log & Postman Report (0 Failures)]
  ```

  **2. ↳ Sub-task: OPS - Test Inventory Restored On Cancellation**
  ```text
  Action: Call Cancel Order API
  Expected Result: Số lượng sản phẩm trong DB được cộng lại
  
  --- Dành cho Tester ---
  Actual Status: Matches Expected
  Result: PASS ✅
  Evidence: [Attached: PHPUnit Log & Postman Report (0 Failures)]
  ```

---

## ⚡ ESQ-6: Dashboard & Admin (Bảng điều khiển & Quản trị)
**👤 Assignee (Tester chính):** Thành viên thứ 6 (chưa rõ tên)
**🛠️ Fix Bug (Người sửa chéo):** Nhựt

### 🚧 Story: Sales Dashboard
- **Story Description:**
  ```text
  Summary: Sales Dashboard
  Description:
    As a store owner, I want to view revenue statistics
    so that I can monitor business performance.
  
    Acceptance Criteria:
    - Trả về tổng doanh thu và số đơn hàng → 200 OK
    - Bị chặn nếu không có quyền Admin/Owner → 403 Forbidden
  
    Endpoint: GET /api/v1/dashboard/sales-report
  
  Priority: Medium
  Evidence Required: Postman screenshot.
  ```
- **Các Sub-tasks:**
  
  **1. ↳ Sub-task: ADMIN - Test Dashboard API Returns Revenue**
  ```text
  Method: GET
  Endpoint: /api/v1/dashboard/sales-report
  Headers: Authorization: Bearer <admin_token>
  Expected Status: 200 OK
  
  --- Dành cho Tester ---
  Actual Status: Matches Expected
  Result: PASS ✅
  Evidence: [Attached: PHPUnit Log & Postman Report (0 Failures)]
  ```

  **2. ↳ Sub-task: ADMIN - Test Unauthorized Access Dashboard Forbidden**
  ```text
  Method: GET
  Endpoint: /api/v1/dashboard/sales-report
  Headers: Authorization: Bearer <customer_token>
  Expected Status: 403 Forbidden
  
  --- Dành cho Tester ---
  Actual Status: Matches Expected
  Result: PASS ✅
  Evidence: [Attached: PHPUnit Log & Postman Report (0 Failures)]
  ```

### 🚧 Story: User Management
- **Story Description:**
  ```text
  Summary: User Management
  Description:
    As an admin, I want to manage user accounts
    so that I can ban violators.
  
    Acceptance Criteria:
    - Admin khóa tài khoản thành công → 200 OK
    - User bị khóa (status = inactive/banned) không thể đăng nhập → 403 / 401
  
    Endpoint: PUT /api/v1/admin/users/{id}
  
  Priority: High
  Evidence Required: Postman screenshot.
  ```
- **Các Sub-tasks:**
  
  **1. ↳ Sub-task: ADMIN - Test Ban User Successfully**
  ```text
  Method: PUT
  Endpoint: /api/v1/admin/users/2
  Body: {"status": "banned"}
  Headers: Authorization: Bearer <admin_token>
  Expected Status: 200 OK
  
  --- Dành cho Tester ---
  Actual Status: Matches Expected
  Result: PASS ✅
  Evidence: [Attached: PHPUnit Log & Postman Report (0 Failures)]
  ```

  **2. ↳ Sub-task: ADMIN - Test Banned User Cannot Login**
  ```text
  Method: POST
  Endpoint: /api/v1/auth/login
  Body: Tài khoản vừa bị ban ở bước trên
  Expected Status: 403 Forbidden (hoặc 401)
  
  --- Dành cho Tester ---
  Actual Status: Matches Expected
  Result: PASS ✅
  Evidence: [Attached: PHPUnit Log & Postman Report (0 Failures)]
  ```
