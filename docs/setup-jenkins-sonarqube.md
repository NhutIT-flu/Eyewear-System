> ✅ **PROJECT VERIFIED & CI/CD READY**
> *Hệ thống đã được nâng cấp lên chuẩn Enterprise với 284 kịch bản PHPUnit Tests (Coverage > 66.9%) và tích hợp hoàn toàn vào CI/CD Pipeline (Jenkins & GitHub Actions). Vui lòng tham khảo thêm tại `Testing_Architecture_Overview.md` và `jenkins-ci-guide.md` để biết cấu trúc kiểm thử mới nhất.*

# Hướng Dẫn Cài Đặt Jenkins & SonarQube (Local)

Tài liệu này hướng dẫn các thành viên trong nhóm cách cài đặt và cấu hình hệ thống CI/CD (Jenkins) và hệ thống kiểm tra chất lượng code (SonarQube) trên máy cá nhân (Windows).

---

## 1. Yêu Cầu Bắt Buộc (Prerequisites)
Cả Jenkins và SonarQube phiên bản mới nhất đều yêu cầu **Java 21**.
- **Tải Java 21 (JDK 21):** [Link tải trực tiếp (.msi)](https://download.oracle.com/java/21/latest/jdk-21_windows-x64_bin.msi)
- **Cài đặt:** Click đúp vào file vừa tải, cứ bấm `Next` -> `Install`.
- **Kiểm tra:** Mở Terminal (PowerShell hoặc CMD) gõ `java -version` để chắc chắn máy đã nhận Java 21.

---

## 2. Cài Đặt Jenkins

1. **Tải file cài đặt:** Vào trang chủ Jenkins ([Link](https://www.jenkins.io/download/)), tìm cột **Stable (LTS)**, click vào **Windows** để tải file `.msi`.
2. **Chạy file cài đặt:**
   - Click đúp vào file `.msi` vừa tải.
   - Đến bước **Service Logon Credentials**: 
     👉 **Quan trọng:** Chọn ô đầu tiên: `Run service as LocalSystem (not recommended)`. (Mặc kệ cảnh báo, cách này giúp chạy local không bị vướng quyền).
   - Đến bước **Port Selection**: Để mặc định là `8080`, bấm `Test Port` để chắc chắn cổng chưa bị chiếm dụng.
   - Đến bước **Java Directory**: Nó sẽ tự động nhận diện hoặc bạn trỏ tay tới thư mục cài Java (VD: `C:\Program Files\Java\jdk-21.0.10`).
   - Đến bước **Firewall Exception**: Click vào dấu ❌ màu đỏ, chọn `Will be installed on local hard drive`.
   - Bấm `Next` và `Install`.
3. **Cấu hình lần đầu:**
   - Mở trình duyệt web, truy cập: `http://localhost:8080`
   - Nó sẽ yêu cầu **Administrator password**. Bạn mở file theo đường dẫn nó báo (thường là `C:\ProgramData\Jenkins\.jenkins\secrets\initialAdminPassword`), copy đoạn mã bên trong dán vào web.
   - Chọn **"Install suggested plugins"** và đợi khoảng 5-10 phút.
   - Tạo tài khoản admin (VD: username/password là `admin` / `admin`). Bấm Save and Finish.

---

## 3. Cài Đặt SonarQube Server

1. **Tải file:** Vào trang tải SonarQube ([Link](https://www.sonarsource.com/products/sonarqube/downloads/)). Bấm nút **Download for free** ở cột **Community Build** để tải file `.zip`.
2. **Giải nén:** 
   - Giải nén file `.zip` vừa tải ra ổ đĩa `C:\` (VD: `C:\sonarqube\`).
3. **Khởi động Server:**
   - Vào thư mục vừa giải nén, theo đường dẫn: `bin\windows-x86-64\`
   - Click đúp vào file `StartSonar.bat`.
   - Một cửa sổ đen (CMD) hiện lên. Bạn chờ khoảng 1-2 phút cho đến khi thấy dòng chữ: **`SonarQube is operational`**. (Tuyệt đối không tắt cửa sổ đen này trong lúc làm việc).
4. **Cấu hình lần đầu:**
   - Mở trình duyệt, truy cập: `http://localhost:9000`
   - Đăng nhập với tài khoản mặc định: `admin` / `admin`.
   - Đổi mật khẩu mới khi được yêu cầu.

---

## 4. Tạo Token (Key) trên SonarQube để quét code
Token này dùng để cấu hình tự động hóa quét code từ Jenkins hoặc chạy bằng tay (Sonar Scanner).

1. Tại trang chủ SonarQube (`localhost:9000`), click vào icon Avatar chữ **A** ở góc phải trên cùng -> Chọn **My Account**.
2. Chuyển sang tab **Security**.
3. Tại ô **Generate Tokens**:
   - **Name:** Điền tên bất kỳ (VD: `Jenkins-Key`).
   - **Type:** Chọn `User Token`.
   - **Expires in:** Chọn `No expiration`.
   - Bấm **Generate**.
4. **Copy và lưu lại** chuỗi mã vừa tạo ra (chuỗi này chỉ hiển thị 1 lần duy nhất).

---

## 5. Quét Code Bằng Sonar Scanner (Tuỳ chọn kiểm tra local)
1. Tải Sonar Scanner CLI (Windows) về máy.
2. Giải nén và mở Terminal tại thư mục gốc của dự án (`Eyewear-System`).
3. Chạy lệnh (thay mã token của bạn vào):
   ```powershell
   <Đường_dẫn_tới_sonar_scanner>\bin\sonar-scanner.bat -D"sonar.login=MÃ_TOKEN_VỪA_TẠO"
   ```
4. Đợi phân tích xong, vào `http://localhost:9000` để xem báo cáo điểm số chi tiết của dự án.
