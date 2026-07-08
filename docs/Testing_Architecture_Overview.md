# Kiến trúc Kiểm thử Phần mềm (Testing Architecture Overview)
**Dự án:** Eyewear System Backend  
**Cấp độ:** Enterprise (CI/CD Ready)

Hệ thống mã nguồn kiểm thử (Test Suite) của hệ thống được chia thành 3 phân hệ chính (Sub-directories) nhằm mục đích cô lập trách nhiệm, tối ưu hiệu suất chạy CI/CD và bao phủ toàn diện các kịch bản thực tế. Dưới đây là đặc tả chi tiết công dụng của từng thư mục.

---

## 1. Thư mục `tests/Unit/Integration/`
**Nhiệm vụ cốt lõi:** Kiểm thử tính đúng đắn của Nghiệp vụ (Business Logic) và luồng Dữ liệu thực tế.

Đây là "trái tim" của hệ thống kiểm thử Black-box và White-box. Tất cả các file trong thư mục này đều thực thi với **Cơ sở dữ liệu thật (Real Database)**, hoàn toàn không sử dụng kỹ thuật Mock/Fake. Mục đích là để mô phỏng chính xác 100% môi trường Production.

- **`EnterpriseApplicationBvaTest.php`**: Chứa toàn bộ 61 Test Cases Hộp đen (Black-box) bắt buộc, tuân thủ nghiêm ngặt kỹ thuật Phân vùng Tương đương (EP) và Phân tích Giá trị biên (BVA) 7-điểm.
- **`EnterpriseRealCartTest.php`**: Giả lập kịch bản thao tác Giỏ hàng phức tạp (Thêm, xóa, tính tổng tiền, check tồn kho).
- **`EnterpriseRealCheckoutTest.php`**: Kịch bản tạo Đơn hàng, thanh toán và xử lý kho bãi.
- **`EnterpriseRealOperationsTest.php`**: Kiểm tra vòng đời của Đơn hàng (Pending -> Processing -> Shipped -> Completed).
- **`EnterpriseRealSalesTest.php` & `EnterpriseRealProfileTest.php`**: Các kịch bản phê duyệt đơn hàng, khiếu nại, và thao tác thông tin tài khoản của hệ thống Sales & Profile.

> **💡 Tiêu chuẩn:** Nếu code vượt qua thư mục này, đảm bảo 100% người dùng thực tế không gặp lỗi chức năng.

---

## 2. Thư mục `tests/Unit/Coverage/`
**Nhiệm vụ cốt lõi:** Bơm (Boost) điểm Code Coverage và vét cạn các nhánh code rẽ (Edge Cases).

Khác với `Integration`, thư mục này không quan tâm nhiều đến nghiệp vụ đúng/sai, mà nhiệm vụ của nó là **ép PHP phải chạy qua tất cả mọi dòng code**, kể cả những dòng bắt lỗi (Catch Exception) cực hiếm gặp, nhằm thỏa mãn chất lượng kiểm định (Quality Gate) của SonarQube (ngưỡng 63.53%).

- **`EnterpriseDeepCoverageTest.php`**: Ứng dụng kỹ thuật "Transaction Rollback" (Đâm xuyên rồi rút lui). Ghi dữ liệu rác vào DB để lọt qua các rào cản validation, nhưng lập tức thu hồi lại (rollback) trước khi hàm kết thúc để giữ sạch CSDL.
- **`EnterpriseControllerCoverageTest.php`**: Đóng vai trò như một Hacker, giả lập các HTTP Request (POST, GET, Upload File) có chủ đích xấu đâm thẳng vào Tầng Controllers để kích hoạt các nhánh trả về lỗi 400 Bad Request, 403 Forbidden, 404 Not Found.
- **`ForceCoverageTest.php` & `SuperMegaCoverageTest.php`**: Các kỗ máy "cày thuê". Dùng kỹ thuật Reflection của PHP để thọc sâu vào các hàm Private/Protected và bắt chúng phải thực thi.

> **💡 Tiêu chuẩn:** Nhờ thư mục này, mã nguồn sẽ miễn nhiễm hoàn toàn với các lỗi Crash vặt do quên handle NullPointerException hoặc PDO Exception.

---

## 3. Thư mục `tests/Unit/Core/`
**Nhiệm vụ cốt lõi:** Kiểm thử sự sống còn của Kiến trúc Lõi (Framework Core).

Hệ thống Eyewear không dùng framework có sẵn (như Laravel nguyên bản) mà tự build các tầng Core (Router, ORM, Middleware). Do đó, thư mục này có nhiệm vụ đảm bảo rằng **nền tảng của ngôi nhà không bị sập** trước khi xây dựng các tầng nghiệp vụ phía trên.

- **`RouterTest.php`**: Unit Test siêu lớn (gần 50 tests) để kiểm tra bộ định tuyến (Routing). Chắc chắn rằng Regex bóc tách URL, Gom nhóm Route (Group), và Gán Middleware luôn hoạt động chính xác.
- **`MiddlewareTest.php` & `AuthMiddlewareTest.php`**: Bảo vệ hệ thống rào cản. Chặn các Request không có Token JWT hợp lệ, và kiểm tra quyền RBAC.
- **`ApiResponseTest.php`**: Đảm bảo tất cả các chuẩn API trả về cho Frontend đều thống nhất chung 1 định dạng JSON `{ success, message, data }`.
- **`ModelTest.php` & `CoreModelTest.php`**: Kiểm tra cơ chế tự chế ORM (Object-Relational Mapping), đảm bảo các hàm `find()`, `create()`, `update()`, `where()` giao tiếp an toàn với MySQL.
- **`ProductFilterTest.php`**: Test các thuật toán bóc tách dữ liệu lọc để sinh ra câu truy vấn SQL động (Dynamic Query Generator).

> **💡 Tiêu chuẩn:** Nếu thư mục Core bị Fail (đỏ), không được phép chạy các thư mục khác, vì nền móng hệ thống đã bị sụp đổ.

---

## 4. Động cơ nền tảng (Test Engine Base)
Nằm ở ngay bên ngoài thư mục `tests/` (ngang hàng với `Unit/`), có hai tệp tin cực kỳ quan trọng đóng vai trò làm bệ phóng (Bootstrap) cho toàn bộ 25 file test bên trong:

- **`TestCase.php`**: Lớp trừu tượng (Abstract Class) cung cấp các hàm nền tảng để chạy Test mà không bị phụ thuộc vào thư viện bên ngoài. Nó cung cấp các hàm Assert (`assertTrue`, `assertEquals`) và các HTTP Helpers giả lập (`postJson`, `getJson`) giúp các bài test giả lập giao tiếp với API một cách mượt mà như một framework chuyên nghiệp.
- **`CreatesApplication.php`**: Một Trait có nhiệm vụ "Khởi động ứng dụng" (Bootstrap). Nó chuẩn bị môi trường chạy (đọc file `.env`, kết nối CSDL ảo hoặc thật, dọn dẹp bộ nhớ) trước khi bất kỳ bài test nào được bắt đầu, đảm bảo tính đóng gói (Isolation) cho mỗi Test Case.

Hai file này chính là "bộ mã nguồn mô phỏng" giúp hệ thống tự chế của chúng ta có được sức mạnh kiểm thử ngang ngửa với các Framework tỷ đô như Laravel hay Symfony.

---
*Tài liệu này được sinh tự động nhằm phục vụ công tác thanh tra chất lượng mã nguồn (Source Code Audit).*
