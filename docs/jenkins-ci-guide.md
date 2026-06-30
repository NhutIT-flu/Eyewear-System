# Hướng dẫn Jenkins CI & GitHub Actions - Eyewear System

## 1. CI/CD Enterprise Pipeline

Hệ thống Eyewear System hiện tại được trang bị một hệ thống tự động hóa CI/CD cực kỳ khắt khe và đạt tiêu chuẩn Enterprise (Doanh nghiệp). Hệ thống này được chạy tự động trên mỗi lần đẩy mã nguồn (Push/Pull Request).

Pipeline hiện tại đạt chuẩn CI/CD Enterprise nâng cao, bao gồm các quy trình kiểm duyệt tự động sau:
1. **Quét bảo mật mã nguồn (Composer Audit):** Kiểm tra lỗ hổng bảo mật của các thư viện.
2. **Khởi tạo môi trường CSDL (Real Database):** Dựng cơ sở dữ liệu thật với cấu trúc schema.sql để phục vụ Integration Test.
3. **Thực thi Kiểm thử PHPUnit (284 Test Cases):** Khởi chạy 284 bài Unit & Integration Test Hộp trắng (White-box) để vét cạn các nhánh code (Coverage) và kiểm tra tính đúng đắn của BVA/EP.
4. **Phân tích Code Coverage (SonarQube):** Đo lường chất lượng mã nguồn, đảm bảo độ bao phủ (Coverage) vượt ngưỡng 66.9%.
5. **Khởi chạy Kiểm thử API (Newman/Postman):** Chạy kiểm thử Hộp đen (Black-box) với hàng trăm Request trực tiếp vào các Endpoints thực tế để đảm bảo hệ thống phản hồi HTTP Status Code chính xác.
6. **Đồng bộ với Jira:** Tự động báo cáo lỗi hoặc thay đổi trạng thái thẻ Bug trên hệ thống Jira.

## 2. Lý do cần CI/CD nghiêm ngặt

Hệ thống CI/CD khắt khe này giúp nhóm:
- Đảm bảo chất lượng hệ thống (Quality Gate) luôn ở trạng thái tốt nhất. Không một dòng code lỗi nào có thể lọt vào nhánh main.
- Đảm bảo "Single Source of Truth": Nếu code vượt qua CI/CD, có nghĩa là hệ thống an toàn tuyệt đối.
- Làm bằng chứng (Evidence) cho các đợt bàn giao mã nguồn (Sprint Review) hoặc nộp bài tập môn học.

## 3. Các thành phần CI/CD cốt lõi

Dự án hiện tại duy trì 2 kịch bản CI/CD chạy song song để đảm bảo tính sẵn sàng cao:
1. **Jenkins (Jenkinsfile)**: Dùng cho server nội bộ (On-premise).
2. **GitHub Actions (.github/workflows/postman-tests.yml)**: Dùng cho Cloud.

## 4. Báo cáo tự động (Artifacts)

Mỗi lần chạy thành công, Pipeline sẽ tự động sinh ra các báo cáo (Artifacts) trực quan:
- **Báo cáo Coverage (HTML):** Thể hiện những dòng code nào đã được chạy qua.
- **Báo cáo Newman (HTML):** Báo cáo đồ họa tuyệt đẹp hiển thị chi tiết số lượng Requests, Assertions Pass/Fail của Postman.
- Có thông báo trực tiếp qua Webhook (Discord) cho toàn bộ Team.

---
*Tài liệu này phản ánh cấu trúc CI/CD tối tân nhất của Eyewear System ở giai đoạn nghiệm thu dự án.*