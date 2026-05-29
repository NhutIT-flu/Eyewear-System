# ESQ-38: Test - Khách hàng xem lịch sử đơn hàng của riêng mình

## Thông tin Test
- **Ngày:** 29/05/2026
- **Người test:** kien0710
- **Môi trường:** Development
- **Kết quả:** ✅ PASS

## Mục tiêu Test
Xác minh rằng khách hàng có thể xem lịch sử đơn hàng của riêng mình thông qua GET /api/v1/orders

## Các bước test
1. Đăng nhập với tài khoản khách hàng (postman.test@example.com)
2. Gọi API GET /api/v1/orders
3. Kiểm tra response chứa dữ liệu đơn hàng

## Kết quả mong đợi
- Mã trạng thái: 200 OK
- Response: `{"success": true, "data": [orders]}`
- Dữ liệu chỉ chứa đơn hàng của khách hàng đã đăng nhập

## Kết quả thực tế
- ✅ Mã trạng thái: 200 OK
- ✅ Thời gian response: 21 ms
- ✅ Kích thước response: 553 B

### Response JSON:
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "order_number": "ORD-001",
            "status": "pending",
            "total_amount": "500000.00",
            "placed_at": "2026-05-29 21:38:23",
            "production_step": null,
            "payment_method": null,
            "payment_status": "pending"
        }
    ]
}
```

## Kết quả Test
✅ **PASS** - Tất cả tiêu chí chấp nhận đều đạt

## Xác minh Tiêu chí Chấp nhận
- [x] Khách hàng có thể lấy danh sách đơn hàng của mình
- [x] Response chứa dữ liệu đơn hàng
- [x] Mã trạng thái là 200 OK
- [x] Thời gian response hợp lý (<100ms)
- [x] Cấu trúc dữ liệu chính xác

## Chi tiết API Endpoint
- **Phương thức:** GET
- **URL:** {{baseUrl}}/api/v1/orders
- **Xác thực:** Bearer Token (postman.test@example.com)
- **Thời gian response:** 21 ms
- **Kích thước response:** 553 B

## Bộ sưu tập Postman
- Bộ sưu tập: Eyewear System API
- Thư mục: 06. Checkout & Orders
- Yêu cầu: Get My Orders

## Kết luận
✅ **PASS** - Khách hàng có thể thành công xem lịch sử đơn hàng của riêng mình. API trả về dữ liệu chính xác với xác thực hợp lệ.