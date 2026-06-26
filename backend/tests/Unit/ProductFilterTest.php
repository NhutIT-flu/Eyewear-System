<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Domain\Catalog\ProductFilter;

/**
 * Unit tests for ProductFilter::buildFilterQuery()
 * Tests the SQL condition builder which is a pure function (no DB needed).
 */
class ProductFilterTest extends TestCase
{
    private ProductFilter $filter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filter = new ProductFilter();
    }

    // ─────────────────────────────────────────────────────────
    // Empty / no filters
    // ─────────────────────────────────────────────────────────

    public function test_empty_filters_returns_empty_sql(): void
    {
        $result = $this->filter->buildFilterQuery([]);
        $this->assertEquals('', $result['sql']);
        $this->assertEmpty($result['params']);
    }

    public function test_no_argument_returns_empty_sql(): void
    {
        $result = $this->filter->buildFilterQuery();
        $this->assertEquals('', $result['sql']);
        $this->assertEmpty($result['params']);
    }

    // ─────────────────────────────────────────────────────────
    // Category filter
    // ─────────────────────────────────────────────────────────

    public function test_single_category_id_filter(): void
    {
        $result = $this->filter->buildFilterQuery(['category_id' => 5]);
        $this->assertStringContainsString('p.category_id = ?', $result['sql']);
        $this->assertEquals([5], $result['params']);
    }

    public function test_multiple_category_ids_filter(): void
    {
        $result = $this->filter->buildFilterQuery(['category_id' => [1, 2, 3]]);
        $this->assertStringContainsString('p.category_id IN (?,?,?)', $result['sql']);
        $this->assertEquals([1, 2, 3], $result['params']);
    }

    // ─────────────────────────────────────────────────────────
    // Brand filter
    // ─────────────────────────────────────────────────────────

    public function test_single_brand_filter(): void
    {
        $result = $this->filter->buildFilterQuery(['brand' => 'Ray-Ban']);
        $this->assertStringContainsString('p.brand = ?', $result['sql']);
        $this->assertEquals(['Ray-Ban'], $result['params']);
    }

    public function test_multiple_brands_filter(): void
    {
        $result = $this->filter->buildFilterQuery(['brands' => ['Ray-Ban', 'Oakley']]);
        $this->assertStringContainsString('p.brand IN (?,?)', $result['sql']);
        $this->assertEquals(['Ray-Ban', 'Oakley'], $result['params']);
    }

    public function test_brands_key_alias_works(): void
    {
        $result = $this->filter->buildFilterQuery(['brands' => 'Gucci']);
        $this->assertStringContainsString('p.brand = ?', $result['sql']);
        $this->assertEquals(['Gucci'], $result['params']);
    }

    // ─────────────────────────────────────────────────────────
    // Gender filter
    // ─────────────────────────────────────────────────────────

    public function test_gender_filter_with_single_value(): void
    {
        $result = $this->filter->buildFilterQuery(['gender' => 'male']);
        $this->assertStringContainsString('p.gender = ?', $result['sql']);
        $this->assertEquals(['male'], $result['params']);
    }

    public function test_gender_filter_all_is_ignored(): void
    {
        $result = $this->filter->buildFilterQuery(['gender' => 'all']);
        $this->assertStringNotContainsString('gender', $result['sql']);
    }

    public function test_gender_filter_with_multiple_values(): void
    {
        $result = $this->filter->buildFilterQuery(['gender' => ['male', 'female']]);
        $this->assertStringContainsString('p.gender IN (?,?)', $result['sql']);
        $this->assertEquals(['male', 'female'], $result['params']);
    }

    // ─────────────────────────────────────────────────────────
    // Price range filter
    // ─────────────────────────────────────────────────────────

    public function test_min_price_filter(): void
    {
        $result = $this->filter->buildFilterQuery(['min_price' => 100]);
        $this->assertStringContainsString('p.base_price >= ?', $result['sql']);
        $this->assertEquals([100.0], $result['params']);
    }

    public function test_max_price_filter(): void
    {
        $result = $this->filter->buildFilterQuery(['max_price' => 500]);
        $this->assertStringContainsString('p.base_price <= ?', $result['sql']);
        $this->assertEquals([500.0], $result['params']);
    }

    public function test_price_range_filter(): void
    {
        $result = $this->filter->buildFilterQuery(['min_price' => 100, 'max_price' => 500]);
        $this->assertStringContainsString('p.base_price >= ?', $result['sql']);
        $this->assertStringContainsString('p.base_price <= ?', $result['sql']);
        $this->assertEquals([100.0, 500.0], $result['params']);
    }

    public function test_price_min_alias(): void
    {
        $result = $this->filter->buildFilterQuery(['price_min' => 50]);
        $this->assertStringContainsString('p.base_price >= ?', $result['sql']);
    }

    public function test_price_max_alias(): void
    {
        $result = $this->filter->buildFilterQuery(['price_max' => 999]);
        $this->assertStringContainsString('p.base_price <= ?', $result['sql']);
    }

    public function test_non_numeric_price_is_ignored(): void
    {
        $result = $this->filter->buildFilterQuery(['min_price' => 'abc']);
        $this->assertStringNotContainsString('base_price', $result['sql']);
    }

    public function test_empty_string_price_is_ignored(): void
    {
        $result = $this->filter->buildFilterQuery(['min_price' => '']);
        $this->assertStringNotContainsString('base_price', $result['sql']);
    }

    // ─────────────────────────────────────────────────────────
    // Search filter
    // ─────────────────────────────────────────────────────────

    public function test_search_filter(): void
    {
        $result = $this->filter->buildFilterQuery(['search' => 'aviator']);
        $this->assertStringContainsString('p.name LIKE ?', $result['sql']);
        $this->assertStringContainsString('p.model_name LIKE ?', $result['sql']);
        $this->assertStringContainsString('p.slug LIKE ?', $result['sql']);
        $this->assertStringContainsString('p.brand LIKE ?', $result['sql']);
        $this->assertEquals(['%aviator%', '%aviator%', '%aviator%', '%aviator%'], $result['params']);
    }

    public function test_empty_search_is_ignored(): void
    {
        $result = $this->filter->buildFilterQuery(['search' => '']);
        $this->assertStringNotContainsString('LIKE', $result['sql']);
    }

    public function test_whitespace_search_is_ignored(): void
    {
        $result = $this->filter->buildFilterQuery(['search' => '   ']);
        $this->assertStringNotContainsString('LIKE', $result['sql']);
    }

    // ─────────────────────────────────────────────────────────
    // Active filter
    // ─────────────────────────────────────────────────────────

    public function test_active_filter(): void
    {
        $result = $this->filter->buildFilterQuery(['active' => true]);
        $this->assertStringContainsString('p.is_active = 1', $result['sql']);
    }

    public function test_active_false_is_ignored(): void
    {
        $result = $this->filter->buildFilterQuery(['active' => false]);
        $this->assertStringNotContainsString('is_active', $result['sql']);
    }

    // ─────────────────────────────────────────────────────────
    // Combined filters
    // ─────────────────────────────────────────────────────────

    public function test_multiple_filters_combined(): void
    {
        $result = $this->filter->buildFilterQuery([
            'category_id' => 1,
            'brand' => 'Ray-Ban',
            'gender' => 'male',
            'min_price' => 100,
            'max_price' => 500,
            'search' => 'aviator',
            'active' => true,
        ]);

        $this->assertStringContainsString('WHERE', $result['sql']);
        $this->assertStringContainsString('AND', $result['sql']);
        $this->assertStringContainsString('p.category_id = ?', $result['sql']);
        $this->assertStringContainsString('p.brand = ?', $result['sql']);
        $this->assertStringContainsString('p.gender = ?', $result['sql']);
        $this->assertStringContainsString('p.base_price >= ?', $result['sql']);
        $this->assertStringContainsString('p.base_price <= ?', $result['sql']);
        $this->assertStringContainsString('p.is_active = 1', $result['sql']);
    }

    public function test_sql_starts_with_where_keyword(): void
    {
        $result = $this->filter->buildFilterQuery(['active' => true]);
        $this->assertStringStartsWith(' WHERE', $result['sql']);
    }

    // ─────────────────────────────────────────────────────────
    // Database Execution
    // ─────────────────────────────────────────────────────────

    public function test_get_filtered_products_executes_query(): void
    {
        $pdoMock = $this->createMock(\PDO::class);
        $stmtMock = $this->createMock(\PDOStatement::class);
        
        $stmtMock->expects($this->once())
                 ->method('execute')
                 ->with([1]);
                 
        $stmtMock->expects($this->once())
                 ->method('fetchAll')
                 ->with(\PDO::FETCH_ASSOC)
                 ->willReturn([['id' => 10, 'name' => 'Ray-Ban Aviator', 'category_name' => 'Kính mát']]);

        $pdoMock->expects($this->once())
                ->method('prepare')
                ->with($this->stringContains('p.category_id = ?'))
                ->willReturn($stmtMock);

        \Core\Database::setInstance($pdoMock);

        $result = $this->filter->getFilteredProducts(['category_id' => 1]);

        $this->assertCount(1, $result);
        $this->assertEquals('Ray-Ban Aviator', $result[0]['name']);
        $this->assertEquals('Kính mát', $result[0]['category_name']);
    }
}
