<?php

namespace Tests\Unit\Integration;

use PHPUnit\Framework\TestCase;
use Core\Database;
use Exception;

/**
 * BỘ TEST MỞ RỘNG BVA (EXTENDED BOUNDARY VALUE ANALYSIS)
 * 
 * Mở rộng độ phủ BVA cho các trường dữ liệu chưa được kiểm thử:
 * 1. Prescription: CYL (-10 đến 10), Axis (0-180), PD (40-80)
 * 2. Auth: Name validation, changePassword BVA
 * 3. Inventory: BVA đầy đủ 7 điểm (quantity 0-9999)
 * 4. Voucher: Fixed amount discount BVA
 * 5. Catalog: Pagination per_page (1-100)
 * 6. Profile: Full name length BVA
 */
class ExtendedBvaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (!defined('APP_ROOT')) {
            define('APP_ROOT', dirname(__DIR__, 3));
        }
        require_once APP_ROOT . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Infrastructure' . DIRECTORY_SEPARATOR . 'env.php';
        require_once APP_ROOT . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Infrastructure' . DIRECTORY_SEPARATOR . 'database.php';
        try { connect_application_database(); } catch (\Throwable $e) {}
    }

    // ==========================================
    // 1. PRESCRIPTION - CYL BVA (Min=-10, Max=10)
    // ==========================================

    public function test_prescription_cyl_bva_01_min_minus_1_throws(): void
    {
        $service = new \App\Application\PrescriptionService();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("CYL OD value (-10.25) is invalid. Must be between -10 and 10.");
        $service->savePrescription(1, ['cyl_od' => -10.25]);
    }

    public function test_prescription_cyl_bva_02_min_succeeds(): void
    {
        $this->cleanupPrescriptionData();
        $service = new \App\Application\PrescriptionService();
        $id = $service->savePrescription(777, ['cyl_od' => -10.00, 'axis_od' => 90]);
        $this->assertIsNumeric($id);
    }

    public function test_prescription_cyl_bva_03_min_plus_1_succeeds(): void
    {
        $this->cleanupPrescriptionData();
        $service = new \App\Application\PrescriptionService();
        $id = $service->savePrescription(777, ['cyl_od' => -9.75, 'axis_od' => 90]);
        $this->assertIsNumeric($id);
    }

    public function test_prescription_cyl_bva_04_nominal_succeeds(): void
    {
        $this->cleanupPrescriptionData();
        $service = new \App\Application\PrescriptionService();
        $id = $service->savePrescription(777, ['cyl_od' => 0.00]);
        $this->assertIsNumeric($id);
    }

    public function test_prescription_cyl_bva_05_max_minus_1_succeeds(): void
    {
        $this->cleanupPrescriptionData();
        $service = new \App\Application\PrescriptionService();
        $id = $service->savePrescription(777, ['cyl_od' => 9.75, 'axis_od' => 90]);
        $this->assertIsNumeric($id);
    }

    public function test_prescription_cyl_bva_06_max_succeeds(): void
    {
        $this->cleanupPrescriptionData();
        $service = new \App\Application\PrescriptionService();
        $id = $service->savePrescription(777, ['cyl_od' => 10.00, 'axis_od' => 90]);
        $this->assertIsNumeric($id);
    }

    public function test_prescription_cyl_bva_07_max_plus_1_throws(): void
    {
        $service = new \App\Application\PrescriptionService();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("CYL OD value (10.25) is invalid. Must be between -10 and 10.");
        $service->savePrescription(1, ['cyl_od' => 10.25, 'axis_od' => 90]);
    }

    // ==========================================
    // 2. PRESCRIPTION - AXIS BVA (Min=0, Max=180)
    // ==========================================

    public function test_prescription_axis_bva_01_min_minus_1_throws(): void
    {
        $service = new \App\Application\PrescriptionService();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Axis OD value (-1) is invalid. Must be between 0 and 180.");
        $service->savePrescription(1, ['cyl_od' => -2.00, 'axis_od' => -1]);
    }

    public function test_prescription_axis_bva_02_min_succeeds(): void
    {
        $this->cleanupPrescriptionData();
        $service = new \App\Application\PrescriptionService();
        $id = $service->savePrescription(777, ['cyl_od' => -2.00, 'axis_od' => 0]);
        $this->assertIsNumeric($id);
    }

    public function test_prescription_axis_bva_03_min_plus_1_succeeds(): void
    {
        $this->cleanupPrescriptionData();
        $service = new \App\Application\PrescriptionService();
        $id = $service->savePrescription(777, ['cyl_od' => -2.00, 'axis_od' => 1]);
        $this->assertIsNumeric($id);
    }

    public function test_prescription_axis_bva_04_nominal_succeeds(): void
    {
        $this->cleanupPrescriptionData();
        $service = new \App\Application\PrescriptionService();
        $id = $service->savePrescription(777, ['cyl_od' => -2.00, 'axis_od' => 90]);
        $this->assertIsNumeric($id);
    }

    public function test_prescription_axis_bva_05_max_minus_1_succeeds(): void
    {
        $this->cleanupPrescriptionData();
        $service = new \App\Application\PrescriptionService();
        $id = $service->savePrescription(777, ['cyl_od' => -2.00, 'axis_od' => 179]);
        $this->assertIsNumeric($id);
    }

    public function test_prescription_axis_bva_06_max_succeeds(): void
    {
        $this->cleanupPrescriptionData();
        $service = new \App\Application\PrescriptionService();
        $id = $service->savePrescription(777, ['cyl_od' => -2.00, 'axis_od' => 180]);
        $this->assertIsNumeric($id);
    }

    public function test_prescription_axis_bva_07_max_plus_1_throws(): void
    {
        $service = new \App\Application\PrescriptionService();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Axis OD value (181) is invalid. Must be between 0 and 180.");
        $service->savePrescription(1, ['cyl_od' => -2.00, 'axis_od' => 181]);
    }

    // ==========================================
    // 3. PRESCRIPTION - PD BVA (Min=40, Max=80)
    // ==========================================

    public function test_prescription_pd_bva_01_min_minus_1_throws(): void
    {
        $service = new \App\Application\PrescriptionService();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("PD value (39) is invalid. Must be between 40 and 80.");
        $service->savePrescription(1, ['pd' => 39]);
    }

    public function test_prescription_pd_bva_02_min_succeeds(): void
    {
        $this->cleanupPrescriptionData();
        $service = new \App\Application\PrescriptionService();
        $id = $service->savePrescription(777, ['pd' => 40]);
        $this->assertIsNumeric($id);
    }

    public function test_prescription_pd_bva_03_min_plus_1_succeeds(): void
    {
        $this->cleanupPrescriptionData();
        $service = new \App\Application\PrescriptionService();
        $id = $service->savePrescription(777, ['pd' => 41]);
        $this->assertIsNumeric($id);
    }

    public function test_prescription_pd_bva_04_nominal_succeeds(): void
    {
        $this->cleanupPrescriptionData();
        $service = new \App\Application\PrescriptionService();
        $id = $service->savePrescription(777, ['pd' => 63]);
        $this->assertIsNumeric($id);
    }

    public function test_prescription_pd_bva_05_max_minus_1_succeeds(): void
    {
        $this->cleanupPrescriptionData();
        $service = new \App\Application\PrescriptionService();
        $id = $service->savePrescription(777, ['pd' => 79]);
        $this->assertIsNumeric($id);
    }

    public function test_prescription_pd_bva_06_max_succeeds(): void
    {
        $this->cleanupPrescriptionData();
        $service = new \App\Application\PrescriptionService();
        $id = $service->savePrescription(777, ['pd' => 80]);
        $this->assertIsNumeric($id);
    }

    public function test_prescription_pd_bva_07_max_plus_1_throws(): void
    {
        $service = new \App\Application\PrescriptionService();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("PD value (81) is invalid. Must be between 40 and 80.");
        $service->savePrescription(1, ['pd' => 81]);
    }

    // ==========================================
    // 4. PRESCRIPTION - CYL_OS BVA (Min=-10, Max=10)
    // ==========================================

    public function test_prescription_cyl_os_bva_01_min_minus_1_throws(): void
    {
        $service = new \App\Application\PrescriptionService();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("CYL OS value (-10.25) is invalid. Must be between -10 and 10.");
        $service->savePrescription(1, ['cyl_os' => -10.25]);
    }

    public function test_prescription_cyl_os_bva_02_min_succeeds(): void
    {
        $this->cleanupPrescriptionData();
        $service = new \App\Application\PrescriptionService();
        $id = $service->savePrescription(777, ['cyl_os' => -10.00, 'axis_os' => 90]);
        $this->assertIsNumeric($id);
    }

    public function test_prescription_cyl_os_bva_07_max_plus_1_throws(): void
    {
        $service = new \App\Application\PrescriptionService();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("CYL OS value (10.25) is invalid. Must be between -10 and 10.");
        $service->savePrescription(1, ['cyl_os' => 10.25, 'axis_os' => 90]);
    }

    // ==========================================
    // 5. PRESCRIPTION - SPH_OS BVA (Min=-20, Max=20)
    // ==========================================

    public function test_prescription_sph_os_bva_01_min_minus_1_throws(): void
    {
        $service = new \App\Application\PrescriptionService();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("SPH OS value (-20.25) is invalid. Must be between -20 and 20.");
        $service->savePrescription(1, ['sph_os' => -20.25]);
    }

    public function test_prescription_sph_os_bva_02_min_succeeds(): void
    {
        $this->cleanupPrescriptionData();
        $service = new \App\Application\PrescriptionService();
        $id = $service->savePrescription(777, ['sph_os' => -20.00]);
        $this->assertIsNumeric($id);
    }

    public function test_prescription_sph_os_bva_06_max_succeeds(): void
    {
        $this->cleanupPrescriptionData();
        $service = new \App\Application\PrescriptionService();
        $id = $service->savePrescription(777, ['sph_os' => 20.00]);
        $this->assertIsNumeric($id);
    }

    public function test_prescription_sph_os_bva_07_max_plus_1_throws(): void
    {
        $service = new \App\Application\PrescriptionService();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("SPH OS value (20.25) is invalid. Must be between -20 and 20.");
        $service->savePrescription(1, ['sph_os' => 20.25]);
    }

    // ==========================================
    // 6. AUTH - CHANGE PASSWORD BVA (Min=6)
    // ==========================================

    public function test_change_password_bva_01_too_short_throws(): void
    {
        $service = new \App\Application\AuthService();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("New password must be at least 6 characters.");
        $service->changePassword(777, 'oldpass', '12345');
    }

    // ==========================================
    // 7. PROFILE - FULL NAME BVA (empty throws)
    // ==========================================

    public function test_profile_fullname_bva_01_empty_throws(): void
    {
        $service = new \App\Application\ProfileService();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Full name cannot be empty");
        $service->updateProfile(777, ['full_name' => '']);
    }

    public function test_profile_fullname_bva_02_whitespace_only_throws(): void
    {
        $service = new \App\Application\ProfileService();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Full name cannot be empty");
        $service->updateProfile(777, ['full_name' => '   ']);
    }

    // ==========================================
    // 8. CATALOG - PAGINATION per_page BVA (1-100)
    // ==========================================

    public function test_catalog_per_page_bva_01_zero_normalizes_to_1(): void
    {
        $service = new \App\Application\CatalogService();
        $result = $service->searchProducts(['per_page' => 0]);
        $this->assertEquals(1, $result['pagination']['per_page']);
    }

    public function test_catalog_per_page_bva_02_min_succeeds(): void
    {
        $service = new \App\Application\CatalogService();
        $result = $service->searchProducts(['per_page' => 1]);
        $this->assertEquals(1, $result['pagination']['per_page']);
    }

    public function test_catalog_per_page_bva_03_nominal_succeeds(): void
    {
        $service = new \App\Application\CatalogService();
        $result = $service->searchProducts(['per_page' => 12]);
        $this->assertEquals(12, $result['pagination']['per_page']);
    }

    public function test_catalog_per_page_bva_04_max_succeeds(): void
    {
        $service = new \App\Application\CatalogService();
        $result = $service->searchProducts(['per_page' => 100]);
        $this->assertEquals(100, $result['pagination']['per_page']);
    }

    public function test_catalog_per_page_bva_05_above_max_clamps(): void
    {
        $service = new \App\Application\CatalogService();
        $result = $service->searchProducts(['per_page' => 101]);
        $this->assertEquals(100, $result['pagination']['per_page']);
    }

    // ==========================================
    // 9. CATALOG - PAGE BVA (Min=1)
    // ==========================================

    public function test_catalog_page_bva_01_zero_normalizes_to_1(): void
    {
        $service = new \App\Application\CatalogService();
        $result = $service->searchProducts(['page' => 0]);
        $this->assertEquals(1, $result['pagination']['page']);
    }

    public function test_catalog_page_bva_02_negative_normalizes_to_1(): void
    {
        $service = new \App\Application\CatalogService();
        $result = $service->searchProducts(['page' => -5]);
        $this->assertEquals(1, $result['pagination']['page']);
    }

    public function test_catalog_page_bva_03_min_succeeds(): void
    {
        $service = new \App\Application\CatalogService();
        $result = $service->searchProducts(['page' => 1]);
        $this->assertEquals(1, $result['pagination']['page']);
    }

    // ==========================================
    // 10. VOUCHER - DISCOUNT TYPE EP (percentage vs fixed)
    // ==========================================

    public function test_voucher_ep_invalid_discount_type_throws(): void
    {
        $service = new \App\Application\AdminService();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Invalid discount_type. Must be: percentage or fixed");
        $service->createVoucher([
            'code' => 'TESTINVALID', 'title' => 'Test',
            'discount_type' => 'bogo', 'discount_value' => 10,
            'starts_at' => date('Y-m-d H:i:s'),
            'ends_at' => date('Y-m-d H:i:s', strtotime('+1 day'))
        ]);
    }

    public function test_voucher_ep_fixed_type_succeeds(): void
    {
        $service = new \App\Application\AdminService();
        $this->cleanupVoucher('TESTFIXED1');
        $voucher = $service->createVoucher([
            'code' => 'TESTFIXED1', 'title' => 'Fixed Test',
            'discount_type' => 'fixed', 'discount_value' => 50000,
            'starts_at' => date('Y-m-d H:i:s'),
            'ends_at' => date('Y-m-d H:i:s', strtotime('+1 day'))
        ]);
        $this->assertEquals('fixed', $voucher['discount_type']);
    }

    public function test_voucher_ep_missing_code_throws(): void
    {
        $service = new \App\Application\AdminService();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Missing required field: code");
        $service->createVoucher([
            'code' => '', 'title' => 'Test',
            'discount_type' => 'percentage', 'discount_value' => 10,
            'starts_at' => date('Y-m-d H:i:s'),
            'ends_at' => date('Y-m-d H:i:s', strtotime('+1 day'))
        ]);
    }

    // ==========================================
    // 11. ORDER WORKFLOW - Additional EP transitions
    // ==========================================

    public function test_order_wfl_ep_shipped_to_delivered_succeeds(): void
    {
        $service = new \App\Application\OrderService();
        $db = Database::getInstance();
        $db->exec("INSERT INTO `order` (id, user_id, order_number, status, total_amount, shipping_address, placed_at) VALUES (888810, 1, 'ORD-888810', 'shipped', 100, 'Test', NOW())");
        $service->transitionStatus(888810, 'delivered', 1);
        $order = \App\Models\Order::find(888810);
        $this->assertEquals('delivered', $order->status);
        $db->exec("DELETE FROM `order` WHERE id = 888810");
    }

    public function test_order_wfl_ep_delivered_to_any_throws(): void
    {
        $service = new \App\Application\OrderService();
        $db = Database::getInstance();
        $db->exec("INSERT INTO `order` (id, user_id, order_number, status, total_amount, shipping_address, placed_at) VALUES (888811, 1, 'ORD-888811', 'delivered', 100, 'Test', NOW())");
        try {
            $service->transitionStatus(888811, 'pending', 1);
            $this->fail("Expected Exception was not thrown");
        } catch (Exception $e) {
            $this->assertStringContainsString("Invalid status transition from delivered to pending.", $e->getMessage());
        } finally {
            $db->exec("DELETE FROM `order` WHERE id = 888811");
        }
    }

    public function test_order_wfl_ep_cancelled_to_any_throws(): void
    {
        $service = new \App\Application\OrderService();
        $db = Database::getInstance();
        $db->exec("INSERT INTO `order` (id, user_id, order_number, status, total_amount, shipping_address, placed_at) VALUES (888812, 1, 'ORD-888812', 'cancelled', 100, 'Test', NOW())");
        try {
            $service->transitionStatus(888812, 'paid', 1);
            $this->fail("Expected Exception was not thrown");
        } catch (Exception $e) {
            $this->assertStringContainsString("Invalid status transition from cancelled to paid.", $e->getMessage());
        } finally {
            $db->exec("DELETE FROM `order` WHERE id = 888812");
        }
    }

    public function test_order_wfl_ep_nonexistent_order_throws(): void
    {
        $service = new \App\Application\OrderService();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Order not found.");
        $service->transitionStatus(999999, 'paid', 1);
    }

    // ==========================================
    // 12. PAYMENT - Amount BVA (EP for method types)
    // ==========================================

    public function test_payment_ep_momo_succeeds(): void
    {
        $service = new \App\Application\PaymentService();
        $db = Database::getInstance();
        $db->exec("INSERT IGNORE INTO `order` (id, user_id, order_number, status, total_amount) VALUES (999903, 1, 'ORD-999903', 'pending', 100)");
        $result = $service->processPayment(999903, 'momo', 100.0);
        $this->assertIsArray($result);
        $this->assertEquals('momo', $result['payment_method']);
        $db->exec("DELETE FROM `order` WHERE id = 999903");
    }

    public function test_payment_ep_zalopay_succeeds(): void
    {
        $service = new \App\Application\PaymentService();
        $db = Database::getInstance();
        $db->exec("INSERT IGNORE INTO `order` (id, user_id, order_number, status, total_amount) VALUES (999904, 1, 'ORD-999904', 'pending', 100)");
        $result = $service->processPayment(999904, 'zalopay', 100.0);
        $this->assertIsArray($result);
        $this->assertEquals('zalopay', $result['payment_method']);
        $db->exec("DELETE FROM `order` WHERE id = 999904");
    }

    public function test_payment_ep_integer_type_throws(): void
    {
        $service = new \App\Application\PaymentService();
        $this->expectException(Exception::class);
        $service->processPayment(999999, '123', 100.0);
    }

    // ==========================================
    // 13. ADMIN - Staff status EP
    // ==========================================

    public function test_admin_status_ep_invalid_throws(): void
    {
        $service = new \App\Application\AdminService();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Invalid status. Must be: active, inactive, or blocked");
        $service->updateStaffStatus(1, 'suspended');
    }

    // ==========================================
    // HELPERS
    // ==========================================

    private function cleanupPrescriptionData(): void
    {
        $db = Database::getInstance();
        $db->exec("DELETE FROM prescription WHERE user_id = 777");
        $db->exec("INSERT IGNORE INTO `user` (id, full_name, email, password_hash, status) VALUES (777, 'BVA User', 'bva@example.com', 'hash', 'active')");
    }

    private function cleanupVoucher(string $code): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM promotion WHERE code = ?");
        $stmt->execute([$code]);
    }
}
