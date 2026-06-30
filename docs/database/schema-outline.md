> ✅ **PROJECT VERIFIED & CI/CD READY**
> *Hệ thống đã được nâng cấp lên chuẩn Enterprise với 284 kịch bản PHPUnit Tests (Coverage > 66.9%) và tích hợp hoàn toàn vào CI/CD Pipeline (Jenkins & GitHub Actions). Vui lòng tham khảo thêm tại `Testing_Architecture_Overview.md` và `jenkins-ci-guide.md` để biết cấu trúc kiểm thử mới nhất.*

# Database Schema Outline (Revised)

The MySQL database clusters:
1. **Identity (IAM)**: `roles`, `users`, `addresses`.
2. **Catalog**: `categories`, `products`, `product_variants`, `product_images`, `inventories`, `lenses`, `promotions`.
3. **Commerce**: `prescriptions`, `carts`, `cart_items`.
4. **Orders**: `orders`, `order_items`, `payments`, `shipments`.
5. **Support**: `support_tickets`, `return_warranties`.
