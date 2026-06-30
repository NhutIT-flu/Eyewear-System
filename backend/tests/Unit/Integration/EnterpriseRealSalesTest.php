<?php

namespace Tests\Unit\Integration;

use PHPUnit\Framework\TestCase;
use App\Application\SalesVerificationService;
use Core\Database;

class EnterpriseRealSalesTest extends TestCase
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
        $this->cleanupData();

        $this->db->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $this->db->exec("INSERT IGNORE INTO `user` (id, full_name, email, password_hash, status) VALUES (5555, 'Sales User', 'sales@example.com', 'hash', 'active')");
        
        $this->db->exec("INSERT IGNORE INTO `order` (id, user_id, order_number, status, total_amount) VALUES (5555, 5555, 'ORD-5555', 'pending', 100)");
        $this->db->exec("INSERT IGNORE INTO `payment` (id, order_id, payment_method, amount, status) VALUES (5555, 5555, 'cod', 100, 'pending')");
        
        $this->db->exec("INSERT IGNORE INTO `orderitem` (id, order_id, productvariant_id, quantity, unit_price) VALUES (5555, 5555, 999, 1, 100)");
        $this->db->exec("SET FOREIGN_KEY_CHECKS = 1;");
    }

    private function cleanupData()
    {
        $this->db->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $this->db->exec("DELETE FROM `orderitem` WHERE order_id = 5555");
        $this->db->exec("DELETE FROM `payment` WHERE order_id = 5555");
        $this->db->exec("DELETE FROM supportticket WHERE order_id = 5555");
        $this->db->exec("DELETE FROM `order` WHERE id = 5555");
        $this->db->exec("DELETE FROM prescription WHERE user_id = 5555");
        $this->db->exec("DELETE FROM `user` WHERE id = 5555");
        $this->db->exec("SET FOREIGN_KEY_CHECKS = 1;");
    }

    protected function tearDown(): void
    {
        $this->cleanupData();
        parent::tearDown();
    }

    public function test_get_all_orders()
    {
        $service = new SalesVerificationService();
        $orders = $service->getAllOrders(['status' => 'pending', 'search' => '5555']);
        $this->assertIsArray($orders);
    }

    public function test_verify_order()
    {
        $service = new SalesVerificationService();
        $result = $service->verifyOrder(5555, 1);
        
        // Order should transition to processing
        $this->assertEquals('processing', $result['status']);
        
        $payment = $this->db->query("SELECT status FROM payment WHERE id = 5555")->fetch(\PDO::FETCH_ASSOC);
        $this->assertEquals('paid', $payment['status']);
    }

    public function test_process_complaint()
    {
        $service = new SalesVerificationService();
        $result = $service->processComplaint(5555, 'refund', 'Broken item', 1);
        
        $this->assertEquals('refunded', $result['order']['status']);
        $this->assertArrayHasKey('ticket', $result);
    }

    public function test_get_order_complaints()
    {
        $service = new SalesVerificationService();
        $complaints = $service->getOrderComplaints();
        $this->assertIsArray($complaints);
    }

    public function test_update_prescription()
    {
        $service = new SalesVerificationService();
        $result = $service->updatePrescription(5555, [
            'sph_od' => -1.25,
            'sph_os' => -1.00
        ]);
        
        $this->assertTrue($result);
        
        $item = $this->db->query("SELECT prescription_id FROM orderitem WHERE id = 5555")->fetch(\PDO::FETCH_ASSOC);
        $this->assertNotNull($item['prescription_id']);
    }
}
