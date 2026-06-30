<?php

namespace Tests\Unit\Integration;

use PHPUnit\Framework\TestCase;
use App\Application\OperationsService;
use Core\Database;

class EnterpriseRealOperationsTest extends TestCase
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

        // Seed data cho Operations
        $this->db->exec("INSERT IGNORE INTO `user` (id, full_name, email, password_hash, status) VALUES (888, 'Ops User', 'ops888@example.com', 'hash', 'active')");
        $this->db->exec("INSERT IGNORE INTO `order` (id, user_id, order_number, status, total_amount, production_step) VALUES (888, 888, 'ORD-888', 'processing', 100, NULL)");
        $this->db->exec("INSERT IGNORE INTO `orderitem` (id, order_id, productvariant_id, quantity, unit_price) VALUES (888, 888, 999, 1, 100)");
        
        // Đơn hàng cần Lab (có lens_id)
        $this->db->exec("INSERT IGNORE INTO `order` (id, user_id, order_number, status, total_amount, production_step) VALUES (889, 888, 'ORD-889', 'processing', 200, NULL)");
        $this->db->exec("INSERT IGNORE INTO `orderitem` (id, order_id, productvariant_id, lens_id, quantity, unit_price) VALUES (889, 889, 999, 1, 1, 200)");
        
        // Đơn hàng có shipment
        $this->db->exec("INSERT IGNORE INTO `order` (id, user_id, order_number, status, total_amount) VALUES (890, 888, 'ORD-890', 'shipped', 100)");
        $this->db->exec("INSERT IGNORE INTO `shipment` (id, order_id, tracking_number, shipping_status) VALUES (890, 890, 'TRK-890', 'shipping')");
    }

    protected function tearDown(): void
    {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        parent::tearDown();
    }

    public function test_list_production_queue()
    {
        $service = new OperationsService();
        $list = $service->listProductionQueue();
        
        $this->assertIsArray($list);
    }

    public function test_advance_production_step_no_lab()
    {
        $service = new OperationsService();
        
        // Order 888 không có lens/prescription -> không cần lab
        $result = $service->advanceProductionStep(888);
        $this->assertEquals('packaging', $result['production_step']);
        
        // Bước tiếp theo
        $result2 = $service->advanceProductionStep(888);
        $this->assertEquals('ready_to_ship', $result2['production_step']);
    }

    public function test_advance_production_step_with_lab()
    {
        $service = new OperationsService();
        
        // Order 889 có lens -> cần lab (lens_cutting)
        $result = $service->advanceProductionStep(889);
        $this->assertEquals('lens_cutting', $result['production_step']);
        
        // Bước tiếp theo
        $result2 = $service->advanceProductionStep(889);
        $this->assertEquals('frame_mounting', $result2['production_step']);
    }

    public function test_create_shipment()
    {
        $service = new OperationsService();
        
        $result = $service->createShipment(888, [
            'courier' => 'GHTK',
            'shipping_status' => 'shipping'
        ]);
        
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals('GHTK', $result['courier']);
        $this->assertEquals('shipping', $result['shipping_status']);
    }

    public function test_update_shipment_to_delivered()
    {
        $service = new OperationsService();
        
        $result = $service->updateShipment(890, [
            'shipping_status' => 'delivered'
        ]);
        
        $this->assertEquals('delivered', $result['shipping_status']);
        $this->assertNotNull($result['delivered_at']);
        
        // Order status nên tự động cập nhật
        $order = \App\Models\Order::find(890);
        $this->assertEquals('delivered', $order->status);
    }
}
