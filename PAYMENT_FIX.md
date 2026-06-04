# Fix Payment API 403 Forbidden Errors

## 📋 Chi Tiết Vấn Đề

### Lỗi Gặp Phải
- `POST /api/v1/payments/process` → 403 Forbidden
- `POST /api/v1/payments/confirm` → 403 Forbidden  
- `GET /api/v1/payments/pending` → 403 Forbidden

### Nguyên Nhân
- OPERATIONS_STAFF role thiếu quyền `confirm_order`
- MANAGER role thiếu quyền `confirm_order`

## ✅ Giải Pháp

### File Thay Đổi
- `backend/database/seeder.php`

### Chi Tiết
```php
// OPERATIONS_STAFF - Thêm 'confirm_order'
'OPERATIONS_STAFF' => [
    'view_orders',
    'pack_order',
    'create_shipment',
    'update_tracking',
    'process_preorder_inventory',
    'process_prescription_orders',
    'update_order_status',
    'confirm_order'  // ✅ THÊM DÒNG NÀY
],

// MANAGER - Thêm 'confirm_order'
'MANAGER' => [
    'manage_products',
    'manage_pricing',
    'manage_promotions',
    'manage_users',
    'view_reports',
    'manage_policies',
    'confirm_order'  // ✅ THÊM DÒNG NÀY
],
```

## 🔄 Cách Thực Hiện

1. **Chạy seeding lại:**
   ```bash
   cd backend
   php database/seeder.php
   ```

2. **Test API:**
   ```bash
   POST http://127.0.0.1:8000/api/v1/payments/confirm
   Headers: Authorization: Bearer <manager_or_operations_token>
   Body: {"payment_id": 1}
   # Kỳ vọng: 200 OK
   ```

3. **Xác nhận:**
   ```bash
   GET http://127.0.0.1:8000/api/v1/payments/pending
   # Kỳ vọng: 200 OK (danh sách thanh toán chờ)
   ```

## 📝 Ghi Chú
- Fix không ảnh hưởng đến `main` branch
- Chỉ cần chạy seeding lại để áp dụng
- SALES_STAFF role đã có `confirm_order` từ trước
