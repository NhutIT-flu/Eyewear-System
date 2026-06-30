<?php

namespace Tests\Unit\Integration;

use PHPUnit\Framework\TestCase;
use App\Application\PaymentService;
use Core\Database;

class EnterpriseRealPaymentTest extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        parent::setUp();
        if (!defined('APP_ROOT')) {
            define('APP_ROOT', dirname(__DIR__, 3));
        }
        require_once APP_ROOT . '/app/Infrastructure/env.php';
        require_once APP_ROOT . '/app/Infrastructure/database.php';
        try {
            connect_application_database();
        } catch (\Throwable $e) {}

        $this->db = Database::getInstance();
        $this->db->beginTransaction();

        $this->db->exec("INSERT IGNORE INTO `user` (id, full_name, email, password_hash, status) VALUES (777, 'Pay User', 'pay777@example.com', 'hash', 'active')");
        $this->db->exec("INSERT IGNORE INTO `order` (id, user_id, order_number, status, total_amount) VALUES (777, 777, 'ORD-777', 'pending', 500)");
        $this->db->exec("INSERT IGNORE INTO `order` (id, user_id, order_number, status, total_amount) VALUES (778, 777, 'ORD-778', 'pending', 500)");
        
        $this->db->exec("INSERT IGNORE INTO `payment` (id, order_id, payment_method, amount, status) VALUES (777, 778, 'cod', 500, 'pending')");
    }

    protected function tearDown(): void
    {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        parent::tearDown();
    }

    public function test_process_payment_new()
    {
        $service = new PaymentService();
        $result = $service->processPayment(777, 'cod', 500, 777);
        
        $this->assertEquals('cod', $result['payment_method']);
        $this->assertEquals('pending', $result['status']);
    }

    public function test_process_payment_update_existing()
    {
        $service = new PaymentService();
        // Cập nhật phương thức thanh toán
        $result = $service->processPayment(778, 'card', 500, 777);
        
        $this->assertEquals('card', $result['payment_method']);
        $this->assertEquals('paid', $result['status']); // Card sẽ chuyển thành paid
    }

    public function test_confirm_payment()
    {
        $service = new PaymentService();
        $result = $service->confirmPayment(777);
        
        $this->assertEquals('paid', $result['status']);
        $this->assertNotNull($result['paid_at']);
        
        $order = \App\Models\Order::find(778); // payment 777 linked to order 778
        $this->assertEquals('paid', $order->status);
    }

    public function test_get_pending_payments()
    {
        $service = new PaymentService();
        $list = $service->getPendingPayments();
        $this->assertIsArray($list);
    }
}
