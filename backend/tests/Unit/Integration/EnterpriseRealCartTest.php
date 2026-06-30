<?php

namespace Tests\Unit\Integration;

use PHPUnit\Framework\TestCase;
use App\Application\CartService;
use Core\Database;

class EnterpriseRealCartTest extends TestCase
{
    private $db;
    private $userId = 1111;

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

        // Seed basic user, product, variant, inventory
        $this->db->exec("INSERT IGNORE INTO `user` (id, full_name, email, password_hash, status) VALUES ({$this->userId}, 'Cart User', 'cart@example.com', 'hash', 'active')");
        $this->db->exec("INSERT IGNORE INTO category (id, name, slug) VALUES (1111, 'Cat', 'cat')");
        $this->db->exec("INSERT IGNORE INTO product (id, name, slug, base_price, category_id, is_active) VALUES (1111, 'Prod', 'prod', 150, 1111, 1)");
        $this->db->exec("INSERT IGNORE INTO productvariant (id, product_id, sku, price_override, additional_price, stock_quantity) VALUES (1111, 1111, 'SKU1111', NULL, 0, 100)");
        $this->db->exec("INSERT IGNORE INTO inventory (id, productvariant_id, quantity) VALUES (1111, 1111, 100)");
        
        $this->db->exec("INSERT IGNORE INTO promotion (id, code, discount_type, discount_value, starts_at, ends_at, is_active) VALUES (1111, 'SALE50', 'fixed', 50, '2000-01-01', '2100-01-01', 1)");
        $this->db->exec("INSERT IGNORE INTO lens (id, name, type, price) VALUES (1111, 'Lens', 'vision', 50)");
    }

    private function cleanupData()
    {
        $this->db->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $this->db->exec("DELETE FROM cartitem WHERE cart_id IN (SELECT id FROM cart WHERE user_id = {$this->userId})");
        $this->db->exec("DELETE FROM cart WHERE user_id = {$this->userId}");
        $this->db->exec("DELETE FROM inventory WHERE id = 1111");
        $this->db->exec("DELETE FROM productvariant WHERE id = 1111");
        $this->db->exec("DELETE FROM product WHERE id = 1111");
        $this->db->exec("DELETE FROM category WHERE id = 1111");
        $this->db->exec("DELETE FROM promotion WHERE id = 1111");
        $this->db->exec("DELETE FROM lens WHERE id = 1111");
        $this->db->exec("DELETE FROM prescription WHERE user_id = {$this->userId}");
        $this->db->exec("DELETE FROM `user` WHERE id = {$this->userId}");
        $this->db->exec("SET FOREIGN_KEY_CHECKS = 1;");
    }

    protected function tearDown(): void
    {
        $this->cleanupData();
        parent::tearDown();
    }

    public function test_add_item_and_get_totals()
    {
        $service = new CartService();
        
        // Add normal item
        $itemId = $service->addItem($this->userId, [
            'variant_id' => 1111,
            'quantity' => 2
        ]);
        
        $this->assertIsNumeric($itemId);
        
        // Get totals (150 * 2 = 300) -> Wait, initially not selected? Yes, wait, `is_selected` is 0 by default? 
        // Need to check DB default, let's select it
        $service->toggleSelection($this->userId, $itemId, true);
        
        $totals = $service->getCartTotals($this->userId);
        $this->assertEquals(300, $totals['subtotal']); // 150 * 2
        
        // Add another to increment quantity
        $itemId2 = $service->addItem($this->userId, [
            'variant_id' => 1111,
            'quantity' => 1
        ]);
        $this->assertTrue($itemId == $itemId2 || $itemId2 === true); // Actually updateQuantity returns true
        
        // Apply voucher
        $service->applyVoucher($this->userId, 'SALE50');
        $totalsWithPromo = $service->getCartTotals($this->userId);
        $this->assertEquals(50, $totalsWithPromo['discount']);
        
        // Update quantity
        $service->updateQuantity($this->userId, $itemId, 5);
        
        // Remove item
        $service->removeItem($this->userId, $itemId);
        
        // Remove voucher
        $service->removeVoucher($this->userId);
    }

    public function test_add_item_with_prescription_and_lens()
    {
        $service = new CartService();
        
        $itemId = $service->addItem($this->userId, [
            'variant_id' => 1111,
            'quantity' => 1,
            'lens_id' => 1111,
            'prescription' => [
                'sph_od' => -1.5,
                'sph_os' => -1.0,
                'cyl_od' => 0,
                'cyl_os' => 0,
                'axis_od' => 0,
                'axis_os' => 0,
                'pd' => 60
            ]
        ]);
        
        $this->assertIsNumeric($itemId);
        $service->selectAll($this->userId, true);
    }
}
