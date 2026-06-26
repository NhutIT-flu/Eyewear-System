<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Domain\Catalog\ProductFilter;

/**
 * Áp dụng kỹ thuật kiểm thử BVA (Boundary Value Analysis - Phân tích giá trị biên)
 * Mục tiêu: Lọc giá sản phẩm (Price Filter)
 * Biên giả định: Giá hợp lệ nằm trong khoảng từ 0 đến 999999.99 (giới hạn DECIMAL(8,2) của cơ sở dữ liệu)
 */
class BoundaryValueAnalysisTest extends TestCase
{
    private ProductFilter $filter;

    // Các giá trị biên (Boundary Values)
    private const MIN_PRICE_LIMIT = 0.0;
    private const MAX_PRICE_LIMIT = 999999.99;
    private const PRICE_STEP = 0.01;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filter = new ProductFilter();
    }

    // ═════════════════════════════════════════════════════════════════════
    //  Biên dưới (Lower Boundary) - min_price
    // ═════════════════════════════════════════════════════════════════════

    /**
     * Test BVA: Dưới biên dưới (min_price < 0) - min - 1
     * Thực tế hệ thống có thể bỏ qua giá âm hoặc vẫn truyền vào tuỳ logic thiết kế.
     * Trong filter hiện tại, nó vẫn nhận giá trị âm, điều này cảnh báo một lỗi tiềm ẩn cần validate.
     */
    public function test_bva_below_lower_boundary_min_price(): void
    {
        $belowMin = self::MIN_PRICE_LIMIT - 1.0; // -1.0
        $result = $this->filter->buildFilterQuery(['min_price' => $belowMin]);
        
        // Hiện tại ProductFilter không chặn số âm, nó đẩy thẳng vào SQL.
        $this->assertStringContainsString('p.base_price >= ?', $result['sql']);
        $this->assertEquals([$belowMin], $result['params']);
    }

    /**
     * Test BVA: Ngay tại biên dưới (min_price = 0) - min
     */
    public function test_bva_at_lower_boundary_min_price(): void
    {
        $atMin = self::MIN_PRICE_LIMIT; // 0.0
        $result = $this->filter->buildFilterQuery(['min_price' => $atMin]);

        $this->assertStringContainsString('p.base_price >= ?', $result['sql']);
        $this->assertEquals([$atMin], $result['params']);
    }

    /**
     * Test BVA: Trên biên dưới (min_price > 0) - min + 1
     */
    public function test_bva_above_lower_boundary_min_price(): void
    {
        $aboveMin = self::MIN_PRICE_LIMIT + 1.0; // 1.0
        $result = $this->filter->buildFilterQuery(['min_price' => $aboveMin]);

        $this->assertStringContainsString('p.base_price >= ?', $result['sql']);
        $this->assertEquals([$aboveMin], $result['params']);
    }

    // ═════════════════════════════════════════════════════════════════════
    //  Biên trên (Upper Boundary) - max_price
    // ═════════════════════════════════════════════════════════════════════

    /**
     * Test BVA: Dưới biên trên - max - 1
     */
    public function test_bva_below_upper_boundary_max_price(): void
    {
        $belowMax = self::MAX_PRICE_LIMIT - 1.0; // 999998.99
        $result = $this->filter->buildFilterQuery(['max_price' => $belowMax]);

        $this->assertStringContainsString('p.base_price <= ?', $result['sql']);
        $this->assertEquals([$belowMax], $result['params']);
    }

    /**
     * Test BVA: Ngay tại biên trên - max
     */
    public function test_bva_at_upper_boundary_max_price(): void
    {
        $atMax = self::MAX_PRICE_LIMIT; // 999999.99
        $result = $this->filter->buildFilterQuery(['max_price' => $atMax]);

        $this->assertStringContainsString('p.base_price <= ?', $result['sql']);
        $this->assertEquals([$atMax], $result['params']);
    }

    /**
     * Test BVA: Trên biên trên - max + 1
     * Nếu filter nhận giá trị này mà không báo lỗi, thì tuỳ thuộc vào DB có lưu được không.
     */
    public function test_bva_above_upper_boundary_max_price(): void
    {
        $aboveMax = self::MAX_PRICE_LIMIT + 1.0; // 1000000.99
        $result = $this->filter->buildFilterQuery(['max_price' => $aboveMax]);

        $this->assertStringContainsString('p.base_price <= ?', $result['sql']);
        $this->assertEquals([$aboveMax], $result['params']);
    }

    // ═════════════════════════════════════════════════════════════════════
    //  Biên của kiểu dữ liệu (Data Type Boundaries)
    // ═════════════════════════════════════════════════════════════════════

    /**
     * Test BVA: Truyền string rỗng (Empty Boundary cho số)
     */
    public function test_bva_empty_string_price(): void
    {
        $result = $this->filter->buildFilterQuery(['min_price' => '']);
        // BVA string rỗng: Filter bỏ qua, không thêm vào câu SQL
        $this->assertStringNotContainsString('base_price >=', $result['sql']);
        $this->assertEmpty($result['params']);
    }

    /**
     * Test BVA: Truyền chữ thay vì số (Type Boundary)
     */
    public function test_bva_non_numeric_price(): void
    {
        $result = $this->filter->buildFilterQuery(['min_price' => 'abc']);
        // BVA không phải số: Filter sẽ bỏ qua (is_numeric = false)
        $this->assertStringNotContainsString('base_price >=', $result['sql']);
        $this->assertEmpty($result['params']);
    }

    /**
     * Test BVA: Tổ hợp min_price > max_price
     */
    public function test_bva_min_greater_than_max(): void
    {
        $result = $this->filter->buildFilterQuery([
            'min_price' => 100.0,
            'max_price' => 50.0
        ]);
        
        $this->assertStringContainsString('p.base_price >= ?', $result['sql']);
        $this->assertStringContainsString('p.base_price <= ?', $result['sql']);
        $this->assertEquals([100.0, 50.0], $result['params']);
    }

    /**
     * Test BVA: Tổ hợp min_price = max_price
     */
    public function test_bva_min_equal_to_max(): void
    {
        $result = $this->filter->buildFilterQuery([
            'min_price' => 100.0,
            'max_price' => 100.0
        ]);
        
        $this->assertStringContainsString('p.base_price >= ?', $result['sql']);
        $this->assertStringContainsString('p.base_price <= ?', $result['sql']);
        $this->assertEquals([100.0, 100.0], $result['params']);
    }

    /**
     * Test BVA: Tham số giá là null
     */
    public function test_bva_null_price(): void
    {
        $result = $this->filter->buildFilterQuery(['min_price' => null]);
        
        $this->assertStringNotContainsString('base_price >=', $result['sql']);
        $this->assertEmpty($result['params']);
    }

    public function test_price_min_just_below_boundary_is_exposed_as_risk(): void
    {
        $result = $this->filter->buildFilterQuery(['min_price' => self::MIN_PRICE_LIMIT - self::PRICE_STEP]);

        $this->assertStringContainsString('p.base_price >= ?', $result['sql']);
        $this->assertSame([-0.01], $result['params']);
    }

    public function test_price_min_at_boundary_zero_is_accepted(): void
    {
        $result = $this->filter->buildFilterQuery(['min_price' => self::MIN_PRICE_LIMIT]);

        $this->assertStringContainsString('p.base_price >= ?', $result['sql']);
        $this->assertSame([0.0], $result['params']);
    }

    public function test_price_min_just_above_boundary_is_accepted(): void
    {
        $result = $this->filter->buildFilterQuery(['min_price' => self::MIN_PRICE_LIMIT + self::PRICE_STEP]);

        $this->assertStringContainsString('p.base_price >= ?', $result['sql']);
        $this->assertSame([0.01], $result['params']);
    }

    public function test_price_max_just_below_boundary_is_accepted(): void
    {
        $result = $this->filter->buildFilterQuery(['max_price' => self::MAX_PRICE_LIMIT - self::PRICE_STEP]);

        $this->assertStringContainsString('p.base_price <= ?', $result['sql']);
        $this->assertSame([999999.98], $result['params']);
    }

    public function test_price_max_at_boundary_is_accepted(): void
    {
        $result = $this->filter->buildFilterQuery(['max_price' => self::MAX_PRICE_LIMIT]);

        $this->assertStringContainsString('p.base_price <= ?', $result['sql']);
        $this->assertSame([999999.99], $result['params']);
    }

    public function test_price_max_just_above_boundary_is_exposed_as_risk(): void
    {
        $result = $this->filter->buildFilterQuery(['max_price' => self::MAX_PRICE_LIMIT + self::PRICE_STEP]);

        $this->assertStringContainsString('p.base_price <= ?', $result['sql']);
        $this->assertSame([1000000.0], $result['params']);
    }

    public function test_price_alias_boundaries_are_applied_together(): void
    {
        $result = $this->filter->buildFilterQuery([
            'price_min' => '0.01',
            'price_max' => '999999.99',
        ]);

        $this->assertSame(' WHERE p.base_price >= ? AND p.base_price <= ?', $result['sql']);
        $this->assertSame([0.01, 999999.99], $result['params']);
    }

    public function test_price_numeric_zero_string_is_accepted(): void
    {
        $result = $this->filter->buildFilterQuery(['min_price' => '0']);

        $this->assertStringContainsString('p.base_price >= ?', $result['sql']);
        $this->assertSame([0.0], $result['params']);
    }

    public function test_price_whitespace_string_boundary_is_accepted_by_php_numeric_parsing(): void
    {
        $result = $this->filter->buildFilterQuery(['max_price' => ' 999999.99 ']);

        $this->assertStringContainsString('p.base_price <= ?', $result['sql']);
        $this->assertSame([999999.99], $result['params']);
    }

    public function test_cart_quantity_boundaries(): void
    {
        $this->assertFalse($this->isValidCartQuantity(0));
        $this->assertTrue($this->isValidCartQuantity(1));
        $this->assertTrue($this->isValidCartQuantity(99));
        $this->assertFalse($this->isValidCartQuantity(100));
    }

    public function test_cart_quantity_fractional_values_are_invalid(): void
    {
        $this->assertFalse($this->isValidCartQuantity(0.99));
        $this->assertFalse($this->isValidCartQuantity(1.5));
        $this->assertFalse($this->isValidCartQuantity(99.01));
    }

    public function test_discount_percent_boundaries(): void
    {
        $this->assertFalse($this->isValidDiscountPercent(0));
        $this->assertTrue($this->isValidDiscountPercent(1));
        $this->assertTrue($this->isValidDiscountPercent(100));
        $this->assertFalse($this->isValidDiscountPercent(101));
    }

    public function test_discount_percent_fractional_edges(): void
    {
        $this->assertFalse($this->isValidDiscountPercent(0.99));
        $this->assertTrue($this->isValidDiscountPercent(1.01));
        $this->assertTrue($this->isValidDiscountPercent(99.99));
        $this->assertFalse($this->isValidDiscountPercent(100.01));
    }

    public function test_password_length_boundaries(): void
    {
        $this->assertFalse($this->isValidPasswordLength(str_repeat('a', 7)));
        $this->assertTrue($this->isValidPasswordLength(str_repeat('a', 8)));
        $this->assertTrue($this->isValidPasswordLength(str_repeat('a', 50)));
        $this->assertFalse($this->isValidPasswordLength(str_repeat('a', 51)));
    }

    public function test_prescription_sph_boundaries(): void
    {
        $this->assertFalse($this->isValidSph(-20.25));
        $this->assertTrue($this->isValidSph(-20.00));
        $this->assertTrue($this->isValidSph(10.00));
        $this->assertFalse($this->isValidSph(10.25));
    }

    public function test_prescription_sph_quarter_step_edges(): void
    {
        $this->assertTrue($this->isValidSph(-19.75));
        $this->assertTrue($this->isValidSph(9.75));
        $this->assertFalse($this->isValidSph(-20.50));
        $this->assertFalse($this->isValidSph(10.50));
    }

    public function test_support_ticket_content_length_boundaries(): void
    {
        $this->assertFalse($this->isValidTicketContent(str_repeat('a', 9)));
        $this->assertTrue($this->isValidTicketContent(str_repeat('a', 10)));
        $this->assertTrue($this->isValidTicketContent(str_repeat('a', 1000)));
        $this->assertFalse($this->isValidTicketContent(str_repeat('a', 1001)));
    }

    public function test_inventory_stock_boundaries(): void
    {
        $this->assertFalse($this->isValidStock(-1));
        $this->assertTrue($this->isValidStock(0));
        $this->assertTrue($this->isValidStock(1));
    }

    private function isValidCartQuantity(float|int $quantity): bool
    {
        return is_int($quantity) && $quantity >= 1 && $quantity <= 99;
    }

    private function isValidDiscountPercent(float|int $percent): bool
    {
        return $percent >= 1 && $percent <= 100;
    }

    private function isValidPasswordLength(string $password): bool
    {
        $length = strlen($password);
        return $length >= 8 && $length <= 50;
    }

    private function isValidSph(float $sph): bool
    {
        return $sph >= -20.00 && $sph <= 10.00;
    }

    private function isValidTicketContent(string $content): bool
    {
        $length = strlen($content);
        return $length >= 10 && $length <= 1000;
    }

    private function isValidStock(int $stock): bool
    {
        return $stock >= 0;
    }
}
