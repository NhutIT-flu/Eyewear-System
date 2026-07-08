# Hướng Dẫn Git Workflow Khi Fix Bug (Sửa Lỗi)

Tài liệu này dành cho người được phân công **Fix Bug** (Sửa chéo). 
Ví dụ: Nhựt test tính năng Login phát hiện lỗi (Bug), sau đó gán (Assign) cái Bug mang mã số **ESQ-15** cho **Thành** sửa. Thành sẽ làm theo các bước Git sau đây:

---

## 🛠️ Quy trình 5 bước dành cho người Fix Bug (Coder)

### Bước 1: Cập nhật code mới nhất về máy
Trước khi sửa bất cứ cái gì, phải đảm bảo code trên máy bạn đang đồng bộ với GitHub.
Mở Terminal (VS Code) và gõ:
```bash
git checkout main
git pull origin main
```

### Bước 2: Tạo nhánh riêng để sửa Bug (KHÔNG code đè lên main)
Mã của thẻ Bug trên Jira là gì thì bạn đặt tên nhánh có chứa mã đó (để Jira tự động link).
Ví dụ thẻ Bug là **ESQ-15**:
```bash
git checkout -b bugfix/ESQ-15-fix-loi-login
```

### Bước 3: Fix Bug trong VS Code
- Tìm file bị lỗi (VD: `AuthController.php`).
- Sửa code sao cho hết lỗi 500.
- (Mẹo: Có thể tự dùng Postman test thử trên máy mình trước xem API chạy đúng chưa).

### Bước 4: Lưu lại thay đổi (Commit)
Lưu ý: Bắt buộc phải có mã **ESQ-15** trong câu lệnh commit để Jira nhận diện được.
```bash
git add .
git commit -m "fix(ESQ-15): Sửa lỗi văng 500 khi login đúng pass"
```

### Bước 5: Đẩy code lên GitHub (Push)
```bash
git push origin bugfix/ESQ-15-fix-loi-login
```

---

## 🚀 Kết thúc nhiệm vụ Fix Bug

1. Lên giao diện **GitHub**, tạo Pull Request (PR) từ nhánh `bugfix/ESQ-15-fix-loi-login` gộp vào `main`. (Thường GitHub sẽ tự hiện nút màu xanh `Compare & pull request`).
2. Nhấn Merge (Gộp code).
3. **[ĐÃ TỰ ĐỘNG HÓA - AIOps]**: Bạn KHÔNG CẦN lên Jira kéo thẻ sang cột **DONE**. Hệ thống CI/CD GitHub Actions sẽ tự chạy Postman API Test. Nếu test xanh (Pass 100%), script `jira-sync.js` sẽ tự động gọi API đổi trạng thái thẻ **ESQ-15** sang DONE và comment báo cáo test.
4. Hét lớn: *"Tui fix xong rồi nha ông Nhựt, chờ bot CI/CD chạy xanh là pull code về check nhé!"*

---

## 🔄 Quy trình dành cho Tester (Người Test Lại)

Sau khi nghe báo fix xong và thấy Bot Discord báo Pipeline xanh, Tester (Nhựt) chỉ cần:
1. Xóa code cũ, tải code mới về:
   ```bash
   git checkout main
   git pull origin main
   ```
2. Chạy lại E2E Test bằng CodeceptJS:
   ```bash
   npx codeceptjs run --steps
   ```
3. Nếu thành công -> Done hoàn toàn! Mọi thứ đã có Log từ CI/CD chứng minh.
