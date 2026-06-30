<?php

namespace Tests\Unit\Integration;

use PHPUnit\Framework\TestCase;
use App\Application\AuthService;
use App\Application\CartService;
use App\Application\PrescriptionService;
use App\Application\SupportTicketService;
use App\Application\OrderService;
use Core\Database;
use PDO;
use PDOStatement;
use Exception;

/**
 * BỘ TEST ENTERPRISE BVA (REAL DATABASE INTEGRATION)
 * 
 * LÝ THUYẾT:
 * Thay vì Mock DB, ở đây chúng ta test SÂU vào TẬN ĐÁY của hệ thống bằng cách
 * kết nối trực tiếp với CSDL thật. Các Service sẽ chạy 100% logic thật của mình,
 * bao gồm cả việc kết nối PDO thật. Exception quăng ra là Exception thật từ lõi nghiệp vụ.
 */
class EnterpriseApplicationBvaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // KẾT NỐI DATABASE THẬT TỪ HỆ THỐNG
        if (!defined('APP_ROOT')) {
            define('APP_ROOT', dirname(__DIR__, 3));
        }
        
        require_once APP_ROOT . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Infrastructure' . DIRECTORY_SEPARATOR . 'env.php';
        require_once APP_ROOT . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Infrastructure' . DIRECTORY_SEPARATOR . 'database.php';
        
        try {
            connect_application_database();
        } catch (\Throwable $e) {
            // Đã kết nối, bỏ qua lỗi nếu gọi nhiều lần
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    // ==========================================
    // 1. AUTH SERVICE (BVA)
    // ==========================================

    public function test_auth_password_bva_01_min_minus_1_throws_exception(): void
    {
        $authService = new AuthService();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Invalid password. Must be between 6 and 50 characters.");

        $authService->register([
            'email' => 'test_bva_auth_1@example.com',
            'name' => 'Test User',
            'password' => '12345' // 5 chars
        ]);
    }

    public function test_auth_password_bva_02_min_succeeds(): void
    {
        $this->cleanupAuthData();
        $authService = new AuthService();
        $result = $authService->register(['email' => 'test_bva_auth_1@example.com', 'name' => 'User', 'password' => '123456']);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function test_auth_password_bva_03_min_plus_1_succeeds(): void
    {
        $this->cleanupAuthData();
        $authService = new AuthService();
        $result = $authService->register(['email' => 'test_bva_auth_2@example.com', 'name' => 'User', 'password' => '1234567']);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function test_auth_password_bva_04_nominal_succeeds(): void
    {
        $this->cleanupAuthData();
        $authService = new AuthService();
        $result = $authService->register(['email' => 'test_bva_auth_3@example.com', 'name' => 'User', 'password' => '12345678901234567890']);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function test_auth_password_bva_05_max_minus_1_succeeds(): void
    {
        $this->cleanupAuthData();
        $authService = new AuthService();
        $result = $authService->register(['email' => 'test_bva_auth_4@example.com', 'name' => 'User', 'password' => str_repeat('a', 49)]);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function test_auth_password_bva_06_max_succeeds(): void
    {
        $this->cleanupAuthData();
        $authService = new AuthService();
        $result = $authService->register(['email' => 'test_bva_auth_5@example.com', 'name' => 'User', 'password' => str_repeat('a', 50)]);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function test_auth_password_bva_07_max_plus_1_throws_exception(): void
    {
        $authService = new AuthService();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Invalid password. Must be between 6 and 50 characters.");

        $authService->register([
            'email' => 'test_bva_auth_6@example.com',
            'name' => 'Test User',
            'password' => str_repeat('a', 51)
        ]);
    }

    private function cleanupAuthData()
    {
        $db = Database::getInstance();
        $db->exec("DELETE FROM user_roles WHERE user_id IN (SELECT id FROM `user` WHERE email LIKE 'test_bva_auth_%')");
        $db->exec("DELETE FROM `user` WHERE email LIKE 'test_bva_auth_%'");
    }

    // ==========================================
    // 2. CART SERVICE (BVA)
    // ==========================================

    public function test_cart_quantity_bva_01_min_minus_1_throws_exception(): void
    {
        $cartService = new CartService();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Invalid quantity. Cart quantity must be between 1 and 99.");

        $cartService->addItem(777, ['variant_id' => 777, 'quantity' => 0]);
    }

    public function test_cart_quantity_bva_02_min_succeeds(): void
    {
        $this->setupCartDataForBva();
        $cartService = new CartService();
        
        $itemId = $cartService->addItem(777, ['variant_id' => 777, 'quantity' => 1]);
        $this->assertIsNumeric($itemId);
    }

    public function test_cart_quantity_bva_03_min_plus_1_succeeds(): void
    {
        $this->setupCartDataForBva();
        $cartService = new CartService();
        
        $itemId = $cartService->addItem(777, ['variant_id' => 777, 'quantity' => 2]);
        $this->assertIsNumeric($itemId);
    }

    public function test_cart_quantity_bva_04_nominal_succeeds(): void
    {
        $this->setupCartDataForBva();
        $cartService = new CartService();
        
        $itemId = $cartService->addItem(777, ['variant_id' => 777, 'quantity' => 50]);
        $this->assertIsNumeric($itemId);
    }

    public function test_cart_quantity_bva_05_max_minus_1_succeeds(): void
    {
        $this->setupCartDataForBva();
        $cartService = new CartService();
        
        $itemId = $cartService->addItem(777, ['variant_id' => 777, 'quantity' => 98]);
        $this->assertIsNumeric($itemId);
    }

    public function test_cart_quantity_bva_06_max_succeeds(): void
    {
        $this->setupCartDataForBva();
        $cartService = new CartService();
        
        $itemId = $cartService->addItem(777, ['variant_id' => 777, 'quantity' => 99]);
        $this->assertIsNumeric($itemId);
    }

    public function test_cart_quantity_bva_07_max_plus_1_throws_exception(): void
    {
        $cartService = new CartService();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Invalid quantity. Cart quantity must be between 1 and 99.");

        $cartService->addItem(777, ['variant_id' => 777, 'quantity' => 100]);
    }

    private function setupCartDataForBva()
    {
        $db = Database::getInstance();
        $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $db->exec("DELETE FROM cartitem WHERE cart_id IN (SELECT id FROM cart WHERE user_id = 777)");
        $db->exec("DELETE FROM cart WHERE user_id = 777");
        $db->exec("DELETE FROM inventory WHERE id = 777");
        $db->exec("DELETE FROM productvariant WHERE id = 777");
        $db->exec("DELETE FROM product WHERE id = 777");
        $db->exec("DELETE FROM category WHERE id = 777");
        $db->exec("DELETE FROM `user` WHERE id = 777");
        
        $db->exec("INSERT IGNORE INTO `user` (id, full_name, email, password_hash, status) VALUES (777, 'BVA User', 'bva@example.com', 'hash', 'active')");
        $db->exec("INSERT IGNORE INTO category (id, name, slug) VALUES (777, 'Cat', 'cat')");
        $db->exec("INSERT IGNORE INTO product (id, name, slug, base_price, category_id, is_active) VALUES (777, 'BVA Prod', 'bva-prod', 150, 777, 1)");
        $db->exec("INSERT IGNORE INTO productvariant (id, product_id, sku, price_override, additional_price, stock_quantity) VALUES (777, 777, 'SKU777', NULL, 0, 9999)");
        $db->exec("INSERT IGNORE INTO inventory (id, productvariant_id, quantity) VALUES (777, 777, 9999)");
        $db->exec("SET FOREIGN_KEY_CHECKS = 1;");
    }

    // ==========================================
    // 3. PRESCRIPTION SERVICE (BVA)
    // ==========================================

    public function test_prescription_bva_01_min_minus_1_throws_exception(): void
    {
        $service = new PrescriptionService();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("SPH OD value (-20.25) is invalid. Must be between -20 and 20.");

        $service->savePrescription(1, ['sph_od' => -20.25]);
    }

    public function test_prescription_bva_02_min_succeeds(): void
    {
        $this->cleanupPrescriptionData();
        $service = new PrescriptionService();
        $id = $service->savePrescription(777, ['sph_od' => -20.00]);
        $this->assertIsNumeric($id);
    }

    public function test_prescription_bva_03_min_plus_1_succeeds(): void
    {
        $this->cleanupPrescriptionData();
        $service = new PrescriptionService();
        $id = $service->savePrescription(777, ['sph_od' => -19.75]);
        $this->assertIsNumeric($id);
    }

    public function test_prescription_bva_04_nominal_succeeds(): void
    {
        $this->cleanupPrescriptionData();
        $service = new PrescriptionService();
        $id = $service->savePrescription(777, ['sph_od' => 0.00]);
        $this->assertIsNumeric($id);
    }

    public function test_prescription_bva_05_max_minus_1_succeeds(): void
    {
        $this->cleanupPrescriptionData();
        $service = new PrescriptionService();
        $id = $service->savePrescription(777, ['sph_od' => 19.75]);
        $this->assertIsNumeric($id);
    }

    public function test_prescription_bva_06_max_succeeds(): void
    {
        $this->cleanupPrescriptionData();
        $service = new PrescriptionService();
        $id = $service->savePrescription(777, ['sph_od' => 20.00]);
        $this->assertIsNumeric($id);
    }

    public function test_prescription_bva_07_max_plus_1_throws_exception(): void
    {
        $service = new PrescriptionService();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("SPH OD value (20.25) is invalid. Must be between -20 and 20.");

        $service->savePrescription(1, ['sph_od' => 20.25]);
    }

    private function cleanupPrescriptionData()
    {
        $db = Database::getInstance();
        $db->exec("DELETE FROM prescription WHERE user_id = 777");
        $db->exec("INSERT IGNORE INTO `user` (id, full_name, email, password_hash, status) VALUES (777, 'BVA User', 'bva@example.com', 'hash', 'active')");
    }

    // ==========================================
    // 4. SUPPORT TICKET SERVICE (BVA)
    // ==========================================

    public function test_ticket_bva_01_min_minus_1_throws_exception(): void
    {
        $service = new SupportTicketService();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Ticket message must be between 10 and 1000 characters.");

        $service->createTicket(1, "Subject", str_repeat("a", 9));
    }

    public function test_ticket_bva_02_min_succeeds(): void
    {
        $this->cleanupTicketData();
        $service = new SupportTicketService();
        $ticket = $service->createTicket(777, "Subject", str_repeat("a", 10));
        $this->assertArrayHasKey('id', $ticket);
    }

    public function test_ticket_bva_03_min_plus_1_succeeds(): void
    {
        $this->cleanupTicketData();
        $service = new SupportTicketService();
        $ticket = $service->createTicket(777, "Subject", str_repeat("a", 11));
        $this->assertArrayHasKey('id', $ticket);
    }

    public function test_ticket_bva_04_nominal_succeeds(): void
    {
        $this->cleanupTicketData();
        $service = new SupportTicketService();
        $ticket = $service->createTicket(777, "Subject", str_repeat("a", 500));
        $this->assertArrayHasKey('id', $ticket);
    }

    public function test_ticket_bva_05_max_minus_1_succeeds(): void
    {
        $this->cleanupTicketData();
        $service = new SupportTicketService();
        $ticket = $service->createTicket(777, "Subject", str_repeat("a", 999));
        $this->assertArrayHasKey('id', $ticket);
    }

    public function test_ticket_bva_06_max_succeeds(): void
    {
        $this->cleanupTicketData();
        $service = new SupportTicketService();
        $ticket = $service->createTicket(777, "Subject", str_repeat("a", 1000));
        $this->assertArrayHasKey('id', $ticket);
    }

    public function test_ticket_bva_07_max_plus_1_throws_exception(): void
    {
        $service = new SupportTicketService();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Ticket message must be between 10 and 1000 characters.");

        $service->createTicket(1, "Subject", str_repeat("a", 1001));
    }

    private function cleanupTicketData()
    {
        $db = Database::getInstance();
        $db->exec("DELETE FROM supportticket WHERE user_id = 777");
        $db->exec("INSERT IGNORE INTO `user` (id, full_name, email, password_hash, status) VALUES (777, 'BVA User', 'bva@example.com', 'hash', 'active')");
    }

    // ==========================================
    // 5. ADMIN SERVICE (VOUCHER BVA)
    // ==========================================

    public function test_voucher_percentage_bva_01_min_minus_1_throws_exception(): void
    {
        $service = new \App\Application\AdminService();
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Percentage discount must be between 1 and 100.");

        $service->createVoucher([
            'code' => 'TEST0',
            'title' => 'Test',
            'discount_type' => 'percentage',
            'discount_value' => 0, // Dưới biên
            'starts_at' => date('Y-m-d H:i:s'),
            'ends_at' => date('Y-m-d H:i:s', strtotime('+1 day'))
        ]);
    }

    public function test_voucher_percentage_bva_02_min_succeeds(): void
    {
        $service = new \App\Application\AdminService();
        $this->cleanupVoucher('TEST1');
        $voucher = $service->createVoucher([
            'code' => 'TEST1',
            'title' => 'Test',
            'discount_type' => 'percentage',
            'discount_value' => 1, // Tại biên dưới
            'starts_at' => date('Y-m-d H:i:s'),
            'ends_at' => date('Y-m-d H:i:s', strtotime('+1 day'))
        ]);
        $this->assertEquals(1, $voucher['discount_value']);
    }

    public function test_voucher_percentage_bva_03_min_plus_1_succeeds(): void
    {
        $service = new \App\Application\AdminService();
        $this->cleanupVoucher('TEST2');
        $voucher = $service->createVoucher([
            'code' => 'TEST2',
            'title' => 'Test',
            'discount_type' => 'percentage',
            'discount_value' => 2, // Trên biên dưới 1 xíu
            'starts_at' => date('Y-m-d H:i:s'),
            'ends_at' => date('Y-m-d H:i:s', strtotime('+1 day'))
        ]);
        $this->assertEquals(2, $voucher['discount_value']);
    }

    public function test_voucher_percentage_bva_04_nominal_succeeds(): void
    {
        $service = new \App\Application\AdminService();
        $this->cleanupVoucher('TEST50');
        $voucher = $service->createVoucher([
            'code' => 'TEST50',
            'title' => 'Test',
            'discount_type' => 'percentage',
            'discount_value' => 50, // Danh định
            'starts_at' => date('Y-m-d H:i:s'),
            'ends_at' => date('Y-m-d H:i:s', strtotime('+1 day'))
        ]);
        $this->assertEquals(50, $voucher['discount_value']);
    }

    public function test_voucher_percentage_bva_05_max_minus_1_succeeds(): void
    {
        $service = new \App\Application\AdminService();
        $this->cleanupVoucher('TEST99');
        $voucher = $service->createVoucher([
            'code' => 'TEST99',
            'title' => 'Test',
            'discount_type' => 'percentage',
            'discount_value' => 99, // Dưới biên trên 1 xíu
            'starts_at' => date('Y-m-d H:i:s'),
            'ends_at' => date('Y-m-d H:i:s', strtotime('+1 day'))
        ]);
        $this->assertEquals(99, $voucher['discount_value']);
    }

    public function test_voucher_percentage_bva_06_max_succeeds(): void
    {
        $service = new \App\Application\AdminService();
        $this->cleanupVoucher('TEST100');
        $voucher = $service->createVoucher([
            'code' => 'TEST100',
            'title' => 'Test',
            'discount_type' => 'percentage',
            'discount_value' => 100, // Tại biên trên
            'starts_at' => date('Y-m-d H:i:s'),
            'ends_at' => date('Y-m-d H:i:s', strtotime('+1 day'))
        ]);
        $this->assertEquals(100, $voucher['discount_value']);
    }

    public function test_voucher_percentage_bva_07_max_plus_1_throws_exception(): void
    {
        $service = new \App\Application\AdminService();
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Percentage discount must be between 1 and 100.");

        $service->createVoucher([
            'code' => 'TEST101',
            'title' => 'Test',
            'discount_type' => 'percentage',
            'discount_value' => 101, // Trên biên trên
            'starts_at' => date('Y-m-d H:i:s'),
            'ends_at' => date('Y-m-d H:i:s', strtotime('+1 day'))
        ]);
    }

    private function cleanupVoucher(string $code)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM promotion WHERE code = ?");
        $stmt->execute([$code]);
    }

    // ==========================================
    // 6. INVENTORY SERVICE (STOCK BVA)
    // ==========================================

    public function test_inventory_stock_below_zero_throws_exception(): void
    {
        $service = new \App\Application\InventoryService();
        $db = \Core\Database::getInstance();
        
        // 1. Tạo một User tạm thời để làm Staff
        $tempEmail = 'test_staff_' . time() . '@example.com';
        $db->exec("INSERT INTO `user` (full_name, email, password_hash, status) VALUES ('Test Staff', '{$tempEmail}', 'hash', 'active')");
        $staffId = (int)$db->lastInsertId();

        // 2. Lấy Role ADMIN
        $roleStmt = $db->query("SELECT id FROM role WHERE name = 'ADMIN' LIMIT 1");
        $role = $roleStmt->fetch();
        if (!$role) {
            $db->exec("INSERT IGNORE INTO role (name, description) VALUES ('ADMIN', 'System Administrator')");
            $roleId = (int)$db->lastInsertId();
        } else {
            $roleId = (int)$role['id'];
        }

        // 3. Gán Role cho Staff
        $db->exec("INSERT INTO user_roles (user_id, role_id) VALUES ({$staffId}, {$roleId})");

        // 4. Test BVA (Bắt lỗi Stock < 0)
        try {
            $service->updateStockQuantities($staffId, [
                [
                    'variant_id' => 10,
                    'quantity' => -1 // Dưới biên
                ]
            ]);
            $this->fail("Expected Exception was not thrown");
        } catch (\Exception $e) {
            $this->assertStringContainsString("Invalid stock. Stock quantity cannot be less than 0.", $e->getMessage());
        } finally {
            // 5. Dọn dẹp Database (Teardown)
            $db->exec("DELETE FROM user_roles WHERE user_id = {$staffId}");
            $db->exec("DELETE FROM `user` WHERE id = {$staffId}");
        }
    }

    // ==========================================
    // 7. PAYMENT SERVICE (EP)
    // ==========================================

    public function test_payment_ep_01_cod_succeeds(): void
    {
        $service = new \App\Application\PaymentService();
        $db = \Core\Database::getInstance();
        $db->exec("INSERT IGNORE INTO `order` (id, user_id, order_number, status, total_amount) VALUES (999901, 1, 'ORD-999901', 'pending', 100)");
        
        $result = $service->processPayment(999901, 'cod', 100.0);
        $this->assertIsArray($result);
        $this->assertEquals('cod', $result['payment_method']);
        $db->exec("DELETE FROM `order` WHERE id = 999901");
    }

    public function test_payment_ep_02_bank_transfer_succeeds(): void
    {
        $service = new \App\Application\PaymentService();
        $db = \Core\Database::getInstance();
        $db->exec("INSERT IGNORE INTO `order` (id, user_id, order_number, status, total_amount) VALUES (999902, 1, 'ORD-999902', 'pending', 100)");
        
        $result = $service->processPayment(999902, 'bank_transfer', 100.0);
        $this->assertIsArray($result);
        $this->assertEquals('bank_transfer', $result['payment_method']);
        $db->exec("DELETE FROM `order` WHERE id = 999902");
    }

    public function test_payment_ep_03_crypto_throws_exception(): void
    {
        $service = new \App\Application\PaymentService();
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Unsupported payment method");

        $service->processPayment(999999, 'crypto', 100.0);
    }

    public function test_payment_ep_04_empty_throws_exception(): void
    {
        $service = new \App\Application\PaymentService();
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Unsupported payment method");

        $service->processPayment(999999, '', 100.0);
    }

    // ==========================================
    // 8. TICKET SERVICE (PRIORITY EP)
    // ==========================================

    public function test_ticket_ep_01_low_priority_succeeds(): void
    {
        $this->cleanupTicketData();
        $service = new \App\Application\SupportTicketService();
        $ticket = $service->createTicket(777, "Subject", str_repeat("a", 50), null, 'low');
        $this->assertEquals('low', $ticket['priority']);
    }

    public function test_ticket_ep_02_normal_priority_succeeds(): void
    {
        $this->cleanupTicketData();
        $service = new \App\Application\SupportTicketService();
        $ticket = $service->createTicket(777, "Subject", str_repeat("a", 50), null, 'normal');
        $this->assertEquals('medium', $ticket['priority']);
    }

    public function test_ticket_ep_03_high_priority_succeeds(): void
    {
        $this->cleanupTicketData();
        $service = new \App\Application\SupportTicketService();
        $ticket = $service->createTicket(777, "Subject", str_repeat("a", 50), null, 'high');
        $this->assertEquals('high', $ticket['priority']);
    }

    public function test_ticket_ep_04_urgent_throws_exception(): void
    {
        $service = new \App\Application\SupportTicketService();
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Unsupported ticket priority.");

        $service->createTicket(1, "Subject", "1234567890", null, 'urgent');
    }

    public function test_ticket_ep_05_empty_throws_exception(): void
    {
        $service = new \App\Application\SupportTicketService();
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Unsupported ticket priority.");

        $service->createTicket(1, "Subject", "1234567890", null, '');
    }

    // ==========================================
    // 9. AUTH SERVICE (EMAIL EP)
    // ==========================================

    public function test_auth_email_ep_01_valid_succeeds(): void
    {
        $this->cleanupAuthData();
        $service = new \App\Application\AuthService();
        $result = $service->register([
            'email' => 'test_bva_auth_10@example.com',
            'password' => '12345678',
            'name' => 'Test'
        ]);
        $this->assertIsArray($result);
    }

    public function test_auth_email_ep_02_missing_at_throws_exception(): void
    {
        $service = new \App\Application\AuthService();
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Invalid email format.");

        $service->register([
            'email' => 'userexample.com',
            'password' => '12345678',
            'name' => 'Test'
        ]);
    }

    public function test_auth_email_ep_03_missing_domain_throws_exception(): void
    {
        $service = new \App\Application\AuthService();
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Invalid email format.");

        $service->register([
            'email' => 'user@.com',
            'password' => '12345678',
            'name' => 'Test'
        ]);
    }

    public function test_auth_email_ep_04_empty_throws_exception(): void
    {
        $service = new \App\Application\AuthService();
        $this->expectException(\Exception::class);
        // Email is empty, so it fails the validation "Invalid email format"
        $this->expectExceptionMessage("Invalid email format");

        $service->register([
            'email' => '',
            'password' => '12345678',
            'name' => 'Test'
        ]);
    }

    // ==========================================
    // 10. ORDER SERVICE (WORKFLOW EP)
    // ==========================================

    public function test_order_wfl_ep_01_pending_to_paid_succeeds(): void
    {
        $service = new \App\Application\OrderService();
        $db = \Core\Database::getInstance();
        $db->exec("INSERT INTO `order` (id, user_id, order_number, status, total_amount, shipping_address, placed_at) VALUES (888801, 1, 'ORD-888801', 'pending', 100, 'Test', NOW())");
        $service->transitionStatus(888801, 'paid', 1);
        $order = \App\Models\Order::find(888801);
        $this->assertEquals('paid', $order->status);
        $db->exec("DELETE FROM `order` WHERE id = 888801");
    }

    public function test_order_wfl_ep_02_pending_to_cancelled_succeeds(): void
    {
        $service = new \App\Application\OrderService();
        $db = \Core\Database::getInstance();
        $db->exec("INSERT INTO `order` (id, user_id, order_number, status, total_amount, shipping_address, placed_at) VALUES (888802, 1, 'ORD-888802', 'pending', 100, 'Test', NOW())");
        $service->transitionStatus(888802, 'cancelled', 1);
        $order = \App\Models\Order::find(888802);
        $this->assertEquals('cancelled', $order->status);
        $db->exec("DELETE FROM `order` WHERE id = 888802");
    }

    public function test_order_wfl_ep_03_paid_to_processing_succeeds(): void
    {
        $service = new \App\Application\OrderService();
        $db = \Core\Database::getInstance();
        $db->exec("INSERT INTO `order` (id, user_id, order_number, status, total_amount, shipping_address, placed_at) VALUES (888803, 1, 'ORD-888803', 'paid', 100, 'Test', NOW())");
        $service->transitionStatus(888803, 'processing', 1);
        $order = \App\Models\Order::find(888803);
        $this->assertEquals('processing', $order->status);
        $db->exec("DELETE FROM `order` WHERE id = 888803");
    }

    public function test_order_wfl_ep_06_processing_to_paid_throws_exception(): void
    {
        $service = new \App\Application\OrderService();
        $db = \Core\Database::getInstance();

        $db->exec("INSERT INTO `order` (id, user_id, order_number, status, total_amount, shipping_address, placed_at) VALUES (888806, 1, 'ORD-888806', 'processing', 100, 'Test', NOW())");

        try {
            $service->transitionStatus(888806, 'paid', 1); // Lùi
            $this->fail("Expected Exception was not thrown");
        } catch (\Exception $e) {
            $this->assertStringContainsString("Invalid status transition from processing to paid.", $e->getMessage());
        } finally {
            $db->exec("DELETE FROM `order` WHERE id = 888806");
        }
    }

    public function test_order_wfl_ep_07_pending_to_shipped_throws_exception(): void
    {
        $service = new \App\Application\OrderService();
        $db = \Core\Database::getInstance();

        $db->exec("INSERT INTO `order` (id, user_id, order_number, status, total_amount, shipping_address, placed_at) VALUES (888807, 1, 'ORD-888807', 'pending', 100, 'Test', NOW())");

        try {
            $service->transitionStatus(888807, 'shipped', 1); // Nhảy cóc
            $this->fail("Expected Exception was not thrown");
        } catch (\Exception $e) {
            $this->assertStringContainsString("Invalid status transition from pending to shipped.", $e->getMessage());
        } finally {
            $db->exec("DELETE FROM `order` WHERE id = 888807");
        }
    }
    // ==========================================
    // 11. CATALOG SERVICE (PRICE BVA)
    // ==========================================

    public function test_catalog_price_bva_01_min_minus_1_throws_exception(): void
    {
        $service = new \App\Application\CatalogService();
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Invalid price boundaries.");

        $service->createProduct(['name' => 'BVA1', 'base_price' => -1]);
    }

    public function test_catalog_price_bva_02_min_succeeds(): void
    {
        $service = new \App\Application\CatalogService();
        $product = $service->createProduct(['name' => 'BVA2', 'base_price' => 0]);
        $this->assertIsArray($product);
    }

    public function test_catalog_price_bva_03_min_plus_1_succeeds(): void
    {
        $service = new \App\Application\CatalogService();
        $product = $service->createProduct(['name' => 'BVA3', 'base_price' => 1]);
        $this->assertIsArray($product);
    }

    public function test_catalog_price_bva_04_nominal_succeeds(): void
    {
        $service = new \App\Application\CatalogService();
        $product = $service->createProduct(['name' => 'BVA4', 'base_price' => 50000000]);
        $this->assertIsArray($product);
    }

    public function test_catalog_price_bva_05_max_minus_1_succeeds(): void
    {
        $service = new \App\Application\CatalogService();
        $product = $service->createProduct(['name' => 'BVA5', 'base_price' => 99999999]);
        $this->assertIsArray($product);
    }

    public function test_catalog_price_bva_06_max_succeeds(): void
    {
        $service = new \App\Application\CatalogService();
        $product = $service->createProduct(['name' => 'BVA6', 'base_price' => 100000000]);
        $this->assertIsArray($product);
    }

    public function test_catalog_price_bva_07_max_plus_1_throws_exception(): void
    {
        $service = new \App\Application\CatalogService();
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Invalid price boundaries.");

        $service->createProduct(['name' => 'BVA7', 'base_price' => 100000001]);
    }
}
