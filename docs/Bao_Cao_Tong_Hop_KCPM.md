# TÀI LIỆU BÁO CÁO TỔNG HỢP DỰ ÁN
**Môn học:** Kiểm thử Phần mềm & Kiến trúc Phần mềm (KCPM)  
**Tên dự án:*12* Eyewear System UTH  
**Repository:** [github.com/Kien0710/Eyewear-System](https://github.com/Kien0710/Eyewear-System)

---

## MỤC LỤC

1. [Tài liệu Thiết kế Hệ thống](#1-tài-liệu-thiết-kế-hệ-thống)
   - [1.1. Kiến trúc Tổng thể (Overall Architecture)](#11-kiến-trúc-tổng-thể-overall-architecture)
   - [1.2. Phân rã Hệ thống (System Components)](#12-phân-rã-hệ-thống-system-components)
   - [1.3. Cấu trúc Thư mục Backend Chi tiết (Layered Architecture)](#13-cấu-trúc-thư-mục-backend-chi-tiết-layered-architecture)
   - [1.4. Cơ sở dữ liệu (Database Schema)](#14-cơ-sở-dữ-liệu-database-schema)
2. [Hướng dẫn sử dụng API](#2-hướng-dẫn-sử-dụng-api)
   - [2.1. Chuẩn giao tiếp](#21-chuẩn-giao-tiếp)
   - [2.2. Thiết lập Postman](#22-thiết-lập-postman)
   - [2.3. Danh sách Endpoint Chính](#23-danh-sách-endpoint-chính)
3. [Tài liệu và Chiến lược Kiểm thử](#3-tài-liệu-và-chiến-lược-kiểm-thử)
   - [3.1. Kiểm thử Đơn vị (Unit Testing)](#31-kiểm-thử-đơn-vị--unit-testing)
   - [3.2. Kiểm thử Tích hợp API (Integration/API Testing)](#32-kiểm-thử-tích-hợp-api--integrationapi-testing)
   - [3.3. Kiểm thử Giao diện (E2E Testing)](#33-kiểm-thử-giao-diện-end-to-end--e2e-testing)
4. [Báo cáo Phân tích Chất lượng Mã nguồn — SonarQube](#4-báo-cáo-phân-tích-chất-lượng-mã-nguồn--sonarqube)
   - [4.1. Cấu hình](#41-cấu-hình-file-sonar-projectproperties)
   - [4.2. Các Chỉ số Đo lường](#42-các-chỉ-số-đo-lường-quality-metrics)
5. [Kịch bản Tích hợp và Giao hàng Liên tục — CI/CD](#5-kịch-bản-tích-hợp-và-giao-hàng-liên-tục--cicd)
   - [5.1. Jenkins Pipeline (On-Premise)](#51-jenkins-pipeline-on-premise--file-jenkinsfile)
   - [5.2. GitHub Actions (Cloud)](#52-github-actions-cloud--file-githubworkflowspostman-testsyml)
   - [5.3. Tự động hóa Quản lý Dự án (Jira Automation Scripts)](#53-tự-động-hóa-quản-lý-dự-án-jira-automation-scripts)

---

## BẢNG TỔNG HỢP CÔNG NGHỆ VÀ PHIÊN BẢN

| STT | Công nghệ / Công cụ | Phiên bản chính xác | File cấu hình tham chiếu | Vai trò trong dự án |
| :---: | :--- | :--- | :--- | :--- |
| 1 | **PHP** | **8.3** | [`.github/workflows/postman-tests.yml`](../.github/workflows/postman-tests.yml) | Ngôn ngữ lập trình Backend |
| 2 | **MySQL** | **8.0** | [`.github/workflows/postman-tests.yml`](../.github/workflows/postman-tests.yml) | Hệ quản trị cơ sở dữ liệu |
| 3 | **JavaScript** | **ES6+** | [`frontend/js/services/apiClient.js`](../frontend/js/services/apiClient.js) | Ngôn ngữ lập trình Frontend |
| 4 | **Axios (CDN)** | **1.6.7** | [`frontend/js/services/apiClient.js`](../frontend/js/services/apiClient.js) | HTTP Client cho Frontend gọi API |
| 5 | **PHPUnit** | **10.5.63** | [`backend/composer.lock`](../backend/composer.lock) | Framework kiểm thử đơn vị (Unit Test) |
| 6 | **Postman** | — | [`Eyewear-System.postman_collection.json`](../Eyewear-System.postman_collection.json) | Thiết kế và quản lý kịch bản kiểm thử API |
| 7 | **Newman (CLI)** | — | [`.github/workflows/postman-tests.yml`](../.github/workflows/postman-tests.yml) | Thực thi tự động Postman Collection trong CI/CD |
| 8 | **CodeceptJS** | **4.0.3** | [`package.json`](../package.json) | Framework kiểm thử End-to-End (E2E) |
| 9 | **Playwright** | **1.60.0** | [`package.json`](../package.json) | Trình điều khiển trình duyệt cho E2E Testing |
| 10 | **SonarQube** | **Community Ed.** | [`sonar-project.properties`](../sonar-project.properties) | Phân tích chất lượng mã nguồn tĩnh |
| 11 | **Jenkins** | **Pipeline** | [`Jenkinsfile`](../Jenkinsfile) | CI/CD Server tự động (môi trường On-Premise) |
| 12 | **GitHub Actions** | **ubuntu-latest**| [`.github/workflows/postman-tests.yml`](../.github/workflows/postman-tests.yml) | CI/CD Server tự động (môi trường Cloud) |
| 13 | **Node.js** | **20** | [`.github/workflows/postman-tests.yml`](../.github/workflows/postman-tests.yml) | Runtime cho Newman, CodeceptJS, Jira Scripts |
| 14 | **Composer** | — | [`backend/composer.json`](../backend/composer.json) | Quản lý thư viện PHP |
| 15 | **Jira REST API**| **v3** | [`.github/scripts/jira-sync.js`](../.github/scripts/jira-sync.js) | Quản lý dự án Agile/Scrum & Bug Tracking |
| 16 | **OpenAI API** | **gpt-4o-mini** | [`.github/scripts/jira-sync.js`](../.github/scripts/jira-sync.js) | AIOps — Tự động chấm Story Point cho Bug |

---

## 1. TÀI LIỆU THIẾT KẾ HỆ THỐNG

### 1.1. Kiến trúc Tổng thể (Overall Architecture)

Dự án **Eyewear System** được thiết kế theo kiến trúc **Client-Server** với sự tách biệt hoàn toàn (Decoupled Architecture) giữa giao diện người dùng (Frontend) và xử lý nghiệp vụ (Backend):

- **Frontend** giao tiếp với **Backend** thông qua các RESTful API Endpoints (JSON).
- **Backend** sử dụng **PDO Prepared Statements** ([`backend/core/Database.php`](../backend/core/Database.php)) để tương tác an toàn với **MySQL**, chống SQL Injection.

Mô hình này cho phép Frontend và Backend phát triển song song, dễ dàng mở rộng (scale) độc lập.

### 1.2. Phân rã Hệ thống (System Components)

| Thành phần | Thư mục gốc | Mô tả |
| :--- | :--- | :--- |
| **Frontend** | [`frontend/`](../frontend/) | HTML5, CSS3, JavaScript ES6+. Giao diện người dùng, cấu trúc Component-based (`/pages`, `/components`, `/layouts`). Sử dụng **Axios v1.6.7** (import từ CDN qua ES Module) làm HTTP Client gọi API |
| **Backend** | [`backend/`](../backend/) | PHP 8.3 (Custom MVC + DDD). Xử lý logic nghiệp vụ. Kiến trúc tự xây dựng không phụ thuộc framework bên ngoài. Áp dụng mô hình **MVC kết hợp Domain-Driven Design** tinh gọn |
| **Database** | [`backend/database/`](../backend/database/) | MySQL 8.0. Lưu trữ dữ liệu quan hệ. Sử dụng `utf8mb4` charset, Foreign Key Constraints, và Transaction |

### 1.3. Cấu trúc Thư mục Backend Chi tiết (Layered Architecture)

| Lớp (Layer) | Thư mục | Chức năng chi tiết |
| :--- | :--- | :--- |
| **Nền tảng (Core)** | [`backend/core/`](../backend/core/) | Các class tự xây dựng: [`Router.php`](../backend/core/Router.php) (Điều hướng URL), [`Database.php`](../backend/core/Database.php) (PDO Singleton Wrapper), [`ApiResponse.php`](../backend/core/ApiResponse.php) (Chuẩn hóa JSON Response), [`Session.php`](../backend/core/Session.php), [`Model.php`](../backend/core/Model.php) (Active Record Base) |
| **Giao tiếp HTTP** | [`backend/app/Http/`](../backend/app/Http/) | `Controllers/` (Tiếp nhận Request), `Middleware/` (Auth, Role, Permission, CORS), `Requests/` (Validate đầu vào), `Resources/` (Format đầu ra) |
| **Dịch vụ nghiệp vụ** | [`backend/app/Application/`](../backend/app/Application/) | **18 Service classes**: `AuthService`, `OrderService`, `CartService`, `CatalogService`, `CheckoutService`, `AdminService`, `InventoryService`, `PaymentService`, `SupportTicketService`, `WishlistService`, `ProfileService`, `SalesVerificationService`, v.v. |
| **Quy tắc nghiệp vụ** | [`backend/app/Domain/`](../backend/app/Domain/) | Business Rules cốt lõi |
| **Hạ tầng** | [`backend/app/Infrastructure/`](../backend/app/Infrastructure/) | Kết nối hạ tầng (DB, Env, Validator) |
| **Mô hình dữ liệu** | [`backend/app/Models/`](../backend/app/Models/) | **14 Model classes** ánh xạ bảng MySQL: `User`, `Product`, `Order`, `OrderItem`, `Cart`, `CartItem`, `Payment`, `Shipment`, `Category`, `Lens`, `Prescription`, `Ticket`, `Profile`, `ProductVariant` |
| **Định tuyến** | [`backend/routes/`](../backend/routes/) | File `api.php` khai báo tập trung toàn bộ Endpoints |

### 1.4. Cơ sở dữ liệu (Database Schema)

CSDL `eyewear_system` được định nghĩa trong file [`backend/database/schema.sql`](../backend/database/schema.sql), gồm **20+ bảng** với đầy đủ ràng buộc Foreign Key:

| Nhóm | Các bảng chính |
| :--- | :--- |
| **Người dùng & Phân quyền** | `user`, `role`, `user_roles`, `permissions`, `role_permissions`, `profiles`, `user_addresses`, `password_reset_tokens` |
| **Sản phẩm & Kho** | `category`, `product`, `productvariant`, `inventory`, `lens` |
| **Đặt hàng & Thanh toán** | `cart`, `cartitem`, `wishlist`, `order`, `orderitem`, `payment`, `shipment`, `promotion` |
| **Hỗ trợ** | `supportticket`, `ticket_replies`, `returnrequest`, `prescription` |

---

## 2. HƯỚNG DẪN SỬ DỤNG API

### 2.1. Chuẩn giao tiếp

Hệ thống sử dụng chuẩn **RESTful API**, trả về JSON thống nhất qua class [`ApiResponse.php`](../backend/core/ApiResponse.php):

```json
{
  "success": true,
  "message": "Mô tả kết quả",
  "data": { ... }
}
```

### 2.2. Thiết lập Postman

Toàn bộ đặc tả API được đóng gói trong 2 file:

| File | Link | Mô tả |
| :--- | :--- | :--- |
| **Collection** | [`Eyewear-System.postman_collection.json`](../Eyewear-System.postman_collection.json) | Chứa toàn bộ kịch bản API (Requests, Headers, Body, Test Scripts) |
| **Environment** | [`Eyewear-Local-Environment.postman_environment.json`](../Eyewear-Local-Environment.postman_environment.json) | Chứa biến môi trường (`{{baseUrl}}` = `http://localhost:8000/api/v1`) |

**Cách sử dụng:** Import cả 2 file vào Postman → Chọn Environment → Gửi Request.

### 2.3. Danh sách Endpoint Chính

| Module | Method | Endpoint | Mô tả |
| :--- | :--- | :--- | :--- |
| **Auth** | `POST` | `/api/login` | Đăng nhập (email + password hash) |
| **Auth** | `POST` | `/api/register` | Đăng ký tài khoản mới |
| **Products** | `GET` | `/api/v1/products` | Danh sách sản phẩm (phân trang, tìm kiếm) |
| **Cart** | `POST` | `/api/v1/cart/items` | Thêm sản phẩm vào giỏ hàng |
| **Order** | `POST` | `/api/v1/orders` | Tạo đơn hàng (Transaction, trừ tồn kho) |
| **Profile** | `PUT` | `/api/v1/profile` | Cập nhật thông tin cá nhân |

---

## 3. CHI TIẾT TÀI LIỆU VÀ KỊCH BẢN KIỂM CHỨNG (TESTING)

Dự án áp dụng mô hình **Kim tự tháp Kiểm thử (Test Pyramid)**, chia làm 3 tầng chuyên sâu: Unit Test (Đáy), API Test (Giữa), và E2E Test (Đỉnh). Mục tiêu là bao phủ (coverage) ít nhất 80% logic nghiệp vụ cốt lõi.

### 3.1. Kiểm thử Đơn vị — Unit Testing (Backend)

Kiểm thử đơn vị tập trung vào tầng nền tảng (Core) và tầng Dịch vụ (Service) của hệ thống Backend.

| Tiêu chí | Chi tiết cấu hình |
| :--- | :--- |
| **Công cụ** | PHPUnit **v10.5.63** |
| **Cấu hình** | [`backend/phpunit.xml`](../backend/phpunit.xml) thiết lập thư mục quét là `backend/tests/Unit/`. Tích hợp Xdebug để đo mã bao phủ (Code Coverage). |
| **Báo cáo** | `--coverage-clover tests/coverage.xml` (dùng cho SonarQube) và `--testdox-html` |

**Các kịch bản kiểm thử (Test Cases) trọng tâm trong Core:**
1. **[`RouterTest.php`](../backend/tests/Unit/Core/RouterTest.php):**
   - *Mục tiêu:* Đảm bảo hệ thống Routing phân tích đúng URL tĩnh và URL có tham số (`/api/v1/products/{id}`).
   - *Assertions:* `assertEquals` URI match, kiểm tra ngoại lệ `NotFoundException` khi URL sai.
2. **[`AuthMiddlewareTest.php`](../backend/tests/Unit/Core/AuthMiddlewareTest.php):**
   - *Mục tiêu:* Kiểm tra bộ lọc Auth.
   - *Mocking:* Giả lập (Mock) Request Header không có `Bearer Token` hoặc Token hết hạn, đảm bảo Middleware trả về đúng HTTP 401 Unauthorized.
3. **[`ApiResponseTest.php`](../backend/tests/Unit/Core/ApiResponseTest.php):**
   - *Mục tiêu:* Đảm bảo chuẩn format JSON (`success`, `message`, `data`) xuất ra ổn định.
4. **[`ProductFilterTest.php`](../backend/tests/Unit/Core/ProductFilterTest.php):**
   - *Mục tiêu:* Đảm bảo thuật toán lọc giá, phân trang và sắp xếp sản phẩm hoạt động chính xác theo Design Pattern.

### 3.2. Kiểm thử Tích hợp API — Integration Testing

Phần này dùng để kiểm chứng sự giao tiếp giữa Frontend và Backend thông qua HTTP REST.

| Tiêu chí | Chi tiết cấu hình |
| :--- | :--- |
| **Công cụ** | **Postman** (viết kịch bản) & **Newman CLI** (chạy tự động trên CI/CD) |
| **Kịch bản** | Báo cáo trong [`Eyewear-System.postman_collection.json`](../Eyewear-System.postman_collection.json) |
| **Môi trường** | [`Eyewear-Local-Environment.postman_environment.json`](../Eyewear-Local-Environment.postman_environment.json) |

**Chi tiết các Assertions (pm.test) thực hiện trong Postman:**
- **Status Code Validation:** `pm.response.to.have.status(200)` hoặc `201` (Create).
- **JSON Schema Validation:** Xác thực payload trả về có đầy đủ cấu trúc (VD: Đơn hàng phải có `order_id`, `total_price`).
- **Response Time (Hiệu năng API):** `pm.expect(pm.response.responseTime).to.be.below(500)` (Response phải dưới 500ms).
- **BVA (Boundary Value Analysis):** Kiểm thử biên với số lượng giỏ hàng (`quantity = 0`, `quantity = 1000`) để bắt lỗi HTTP 400.

### 3.3. Kiểm thử Giao diện Người dùng — End-to-End (E2E) Testing

Kiểm thử E2E mô phỏng hành vi thực tế của người dùng từ lúc mở trình duyệt đến lúc thanh toán.

| Tiêu chí | Chi tiết cấu hình |
| :--- | :--- |
| **Framework** | **CodeceptJS v4.0.3** kết hợp Driver **Playwright v1.60.0** (Chromium) |
| **Cấu hình** | [`codecept.conf.cjs`](../codecept.conf.cjs) (`url: http://localhost/Eyewear-System/frontend`) |
| **Script chính**| [`tests/eyewear_test.js`](../tests/eyewear_test.js) (Hơn 20 Test Cases) |

**Danh sách Kịch bản E2E Thực tế (Trích xuất từ source code):**
1. **Luồng Mua hàng (Shop & Cart):**
   - `TC-PROD-01`: Vào `/pages/shop/`, kiểm tra Render Toolbar, Filter Sidebar và Sort dropdown tĩnh.
   - `TC-PROD-03`: Kiểm tra nút chuyển đổi chế độ xem Grid (Lưới) và List (Danh sách).
   - `TC-DTL-01`: Ở `/pages/details/`, verify hiển thị hình ảnh (`#details-main-img`), giá (`.details__price`), nút Add to Cart (`#add-to-cart-btn`).
   - `TC-CART-02`: Ở `/pages/cart/`, xác nhận có bảng Table chứa sản phẩm, tổng tiền (`#cart-total`) và nút Checkout.
2. **Luồng Tài khoản (Auth & Checkout):**
   - `TC-AUTH-05`: Nhập sai email/password, verify nút Sign In vẫn ở trạng thái lỗi, không bị Redirect (bắt lỗi UI).
   - `TC-CHK-01`: Tại `/pages/checkout/`, kiểm tra render form chứa `#checkout-name`, `#checkout-email` và `#place-order-btn`.
3. **Điều hướng (Navigation):**
   - `TC-NAV-01`: Truy cập `/pages/nonexistent-page/`, expect hệ thống hiển thị trang lỗi 404 thay vì vỡ giao diện.

---

## 4. ĐÁNH GIÁ CHẤT LƯỢNG MÃ NGUỒN (CODE QUALITY) — SONARQUBE

Hệ thống mã nguồn được đưa qua bộ lọc quét tĩnh SonarQube để bắt lỗi bảo mật và tối ưu code trước khi release.

### 4.1. Cấu hình Quét tĩnh (Static Analysis)

Xem file gốc: [`sonar-project.properties`](../sonar-project.properties)
- **Khu vực quét (`sonar.sources`):** `backend/app`, `backend/core`, `frontend/js`.
- **Khu vực bỏ qua (`sonar.exclusions`):** Bỏ qua thư viện bên thứ 3 (`vendor/`, `node_modules/`, `PHPMailer/`).
- **Đọc Coverage:** Import kết quả từ `tests/coverage.xml` do PHPUnit sinh ra để đánh giá tỷ lệ dòng code đã được test.

### 4.2. Các Chỉ số Kiểm chứng (Quality Gate Metrics)

Một phiên bản chỉ được coi là "Pass" nếu thoả mãn **Quality Gate**:
1. **Bảo mật (Security):** 0 Vulnerabilities, 0 Security Hotspots (Ngăn chặn SQL Injection, XSS, CSRF). Code backend bắt buộc phải dùng PDO Prepare Statements.
2. **Độ tin cậy (Reliability):** 0 Bugs (Không có biến chưa khởi tạo, không có vòng lặp vô hạn).
3. **Khả năng bảo trì (Maintainability):** Technical Debt Ratio < 5%. (Cognitive Complexity < 15, không có hàm dài quá 100 dòng).
4. **Code Coverage:** > 80% (Bắt buộc với Core và Services).
5. **Trùng lặp (Duplications):** Mật độ trùng lặp < 3%.

---

## 5. TỰ ĐỘNG HÓA KIỂM CHỨNG & TRIỂN KHAI — CI/CD

Đồ án ứng dụng mạnh mẽ tư duy DevOps bằng cách tự động hóa toàn bộ quá trình kiểm chứng thông qua 2 hệ thống CI/CD độc lập.

### 5.1. GitHub Actions (Cloud Testing & AIOps)

Workflow này (`postman-tests.yml`) được kích hoạt mỗi khi có Developer đẩy code (Push) hoặc tạo Pull Request (PR) vào nhánh `main/develop`.

**Tiến trình kiểm chứng tự động (Workflow Steps):**
1. **Khởi tạo Môi trường:** Cài đặt **PHP 8.3**, **Node.js 20**, dựng Database **MySQL 8.0** qua Docker.
2. **Security Audit:** Chạy `composer audit` để phát hiện lỗ hổng bảo mật trong package.
3. **Chuẩn bị Dữ liệu:** Chạy script `schema.sql` và `seeder.php` để tạo dữ liệu giả lập cho test.
4. **Health Check Server:** Khởi chạy Backend nội bộ (127.0.0.1:8000), Ping endpoint `/api/v1/health` liên tục 15 lần (mỗi 2s) đến khi HTTP 200.
5. **Thực thi Unit Test:** Gọi `vendor/bin/phpunit` chạy bộ test Backend.
6. **Thực thi Integration Test:** Gọi **Newman** chạy kịch bản Postman Collection với timeout bảo vệ 10s cho mỗi Request.
7. **Jira AIOps Sync:** Nếu API lỗi, chạy script Node.js tương tác với OpenAI API (`gpt-4o-mini`) phân tích Payload lỗi, đánh giá Story Point, và tự động tạo/cập nhật Bug Ticket lên Jira Cloud.
8. **Báo cáo:** Gửi file xuất ra dạng HTML lên GitHub Artifacts và bắn thông báo qua Discord Webhook.

### 5.2. Tự động hóa Jira (AIOps & Automation Scripts)

Phần kiểm chứng được tích hợp chặt chẽ với công cụ quản lý dự án Jira, giúp nhóm ngay lập tức phát hiện và điều phối công việc khi có lỗi xảy ra ở CI/CD:

| Script File (Node.js) | Chức năng kiểm chứng & Quản trị |
| :--- | :--- |
| [`auto-assign-sprint.js`](../auto-assign-sprint.js) | Quét toàn bộ Bug trên Jira, gán round-robin tự động cho các thành viên trong nhóm, phân bổ vào các Sprint Board đang Active (ID: 37). |
| [`auto-point.js`](../auto-point.js) | Thuật toán phân tích ngữ nghĩa (Keyword-based) tên Bug. Nếu liên quan đến "Checkout", "Login" gán mức **5 Points** (Khó/Quan trọng). Nếu lỗi nhỏ gán **2 Points**. |
| [`.github/scripts/jira-sync.js`](../.github/scripts/jira-sync.js) | Đọc log xuất ra từ Newman. Gọi OpenAI AI Model để phân tích Stack Trace lỗi. Mở 1 Bug Ticket mới trên Jira với mức độ ưu tiên tương ứng. Nếu CI/CD pass ở lần sau, script tự động chuyển Bug đó sang trạng thái "Done". |
