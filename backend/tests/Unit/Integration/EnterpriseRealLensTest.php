<?php

namespace Tests\Unit\Integration;

use PHPUnit\Framework\TestCase;
use App\Application\LensService;
use Core\Database;

class EnterpriseRealLensTest extends TestCase
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

        $this->db->exec("INSERT IGNORE INTO category (id, name, slug) VALUES (1234, 'Cat', 'cat')");
        // Standard Frame
        $this->db->exec("INSERT IGNORE INTO product (id, name, slug, base_price, category_id, is_active, gender) VALUES (1234, 'Prod', 'prod', 150, 1234, 1, 'adult')");
        $this->db->exec("INSERT IGNORE INTO productvariant (id, product_id, sku, size_code, stock_quantity) VALUES (1234, 1234, 'SKU1234', 'L', 10)");
        
        // Kids Frame
        $this->db->exec("INSERT IGNORE INTO product (id, name, slug, base_price, category_id, is_active, gender) VALUES (1235, 'Prod Kids', 'prodk', 100, 1234, 1, 'kids')");
        $this->db->exec("INSERT IGNORE INTO productvariant (id, product_id, sku, size_code, stock_quantity) VALUES (1235, 1235, 'SKU1235', 'S', 10)");

        // Lenses
        $this->db->exec("INSERT IGNORE INTO lens (id, name, type, price) VALUES (1234, 'Lens SV', 'single_vision', 50)");
        $this->db->exec("INSERT IGNORE INTO lens (id, name, type, price) VALUES (1235, 'Lens Bi', 'bifocal', 100)");
        $this->db->exec("INSERT IGNORE INTO lens (id, name, type, price) VALUES (1236, 'Lens Pro', 'progressive', 150)");
    }

    private function cleanupData()
    {
        $this->db->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $this->db->exec("DELETE FROM productvariant WHERE id IN (1234, 1235)");
        $this->db->exec("DELETE FROM product WHERE id IN (1234, 1235)");
        $this->db->exec("DELETE FROM category WHERE id = 1234");
        $this->db->exec("DELETE FROM lens WHERE id IN (1234, 1235, 1236)");
        $this->db->exec("SET FOREIGN_KEY_CHECKS = 1;");
    }

    protected function tearDown(): void
    {
        $this->cleanupData();
        parent::tearDown();
    }

    public function test_get_all_lenses()
    {
        $service = new LensService();
        $lenses = $service->getAllLenses();
        
        $this->assertIsArray($lenses);
        $this->assertGreaterThan(0, count($lenses));
    }

    public function test_get_available_lenses_for_standard_variant()
    {
        $service = new LensService();
        $result = $service->getAvailableLensesForVariant(1234);
        
        $this->assertArrayHasKey('available_lenses', $result);
        $this->assertEquals('standard', $result['compatibility_profile']['support_level']);
    }

    public function test_get_available_lenses_for_kids_variant()
    {
        $service = new LensService();
        $result = $service->getAvailableLensesForVariant(1235);
        
        $this->assertArrayHasKey('available_lenses', $result);
        $this->assertEquals('kids', $result['compatibility_profile']['support_level']);
    }

    public function test_variant_not_found_throws_exception()
    {
        $service = new LensService();
        $this->expectException(\RuntimeException::class);
        $service->getAvailableLensesForVariant(99999);
    }
}
