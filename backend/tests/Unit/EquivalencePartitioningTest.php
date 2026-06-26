<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Domain\Catalog\ProductFilter;

/**
 * Áp dụng kỹ thuật kiểm thử EP (Equivalence Partitioning - Phân vùng tương đương)
 * Mục tiêu: Kiểm tra bộ lọc Giới tính (Gender) và Trạng thái (Active)
 * 
 * LÝ THUYẾT PHÂN VÙNG TƯƠNG ĐƯƠNG:
 * Thay vì test mọi giá trị, ta chia input thành các vùng (partitions).
 * Một giá trị đại diện trong vùng pass/fail thì coi như cả vùng đó pass/fail.
 */
class EquivalencePartitioningTest extends TestCase
{
    private ProductFilter $filter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filter = new ProductFilter();
    }

    // ═════════════════════════════════════════════════════════════════════
    //  Phân vùng tương đương cho Giới tính (Gender)
    // ═════════════════════════════════════════════════════════════════════

    /**
     * Vùng hợp lệ 1 (Valid Class): Giới tính cụ thể hợp lệ (ví dụ: 'male', 'female', 'unisex')
     */
    public function test_ep_valid_specific_gender(): void
    {
        $result = $this->filter->buildFilterQuery(['gender' => 'male']);
        
        $this->assertStringContainsString('p.gender = ?', $result['sql']);
        $this->assertEquals(['male'], $result['params']);
    }

    /**
     * Vùng hợp lệ 2 (Valid Class): Nhiều giới tính cùng lúc (Mảng các string)
     */
    public function test_ep_valid_array_of_genders(): void
    {
        $result = $this->filter->buildFilterQuery(['gender' => ['male', 'unisex']]);
        
        $this->assertStringContainsString('p.gender IN (?,?)', $result['sql']);
        $this->assertEquals(['male', 'unisex'], $result['params']);
    }

    /**
     * Vùng đặc biệt (Special Class): Giới tính 'all' (Tất cả)
     * Hệ thống thiết kế để bỏ qua từ khoá này và không đưa vào câu SQL.
     */
    public function test_ep_special_gender_all(): void
    {
        $result = $this->filter->buildFilterQuery(['gender' => 'all']);
        
        $this->assertStringNotContainsString('p.gender', $result['sql']);
        $this->assertEmpty($result['params']);
    }

    /**
     * Vùng không hợp lệ (Invalid Class): Kiểu dữ liệu không mong muốn (vd: số, mảng rỗng)
     */
    public function test_ep_invalid_gender_type_empty_array(): void
    {
        $result = $this->filter->buildFilterQuery(['gender' => []]);
        
        // Filter nên bỏ qua mảng rỗng thay vì sinh ra lỗi SQL "IN ()"
        $this->assertStringNotContainsString('p.gender IN', $result['sql']);
    }

    // ═════════════════════════════════════════════════════════════════════
    //  Phân vùng tương đương cho Trạng thái (Active)
    // ═════════════════════════════════════════════════════════════════════

    /**
     * Vùng hợp lệ 1 (Valid Class): Boolean True (Lọc sản phẩm đang hoạt động)
     */
    public function test_ep_valid_active_true(): void
    {
        $result = $this->filter->buildFilterQuery(['active' => true]);
        
        $this->assertStringContainsString('p.is_active = 1', $result['sql']);
    }

    /**
     * Vùng hợp lệ 2 (Valid Class): Boolean False (Bỏ qua lọc trạng thái hoặc lọc inactive)
     * Logic hiện tại: Nếu false thì KHÔNG filter (cho phép hiển thị tất cả, hoặc do mặc định lấy active)
     */
    public function test_ep_valid_active_false(): void
    {
        $result = $this->filter->buildFilterQuery(['active' => false]);
        
        $this->assertStringNotContainsString('p.is_active', $result['sql']);
    }

    /**
     * Vùng không hợp lệ (Invalid Class): Giá trị Truthy nhưng không phải boolean (vd: string "1", "true")
     * Lưu ý: Tuỳ thuộc vào ngôn ngữ, PHP có thể tự ép kiểu. Ta test để hiểu rõ hành vi của filter.
     */
    public function test_ep_invalid_active_string_truthy(): void
    {
        $result = $this->filter->buildFilterQuery(['active' => 'true']);
        
        // ProductFilter hiện tại sử dụng empty() hoặc kiểm tra strict boolean?
        // Nếu nó dùng `if (!empty($filters['active']))`, chuỗi 'true' sẽ tính là có filter.
        $this->assertStringContainsString('p.is_active = 1', $result['sql']);
    }

    public function test_gender_valid_specific_partition(): void
    {
        $result = $this->filter->buildFilterQuery(['gender' => 'Men']);

        $this->assertSame(' WHERE p.gender = ?', $result['sql']);
        $this->assertSame(['men'], $result['params']);
    }

    public function test_gender_valid_multiple_values_partition(): void
    {
        $result = $this->filter->buildFilterQuery(['gender' => ['men', 'women', 'unisex']]);

        $this->assertSame(' WHERE p.gender IN (?,?,?)', $result['sql']);
        $this->assertSame(['men', 'women', 'unisex'], $result['params']);
    }

    public function test_gender_special_all_partition_is_ignored(): void
    {
        $result = $this->filter->buildFilterQuery(['gender' => 'all']);

        $this->assertSame('', $result['sql']);
        $this->assertSame([], $result['params']);
    }

    public function test_brand_valid_single_partition(): void
    {
        $result = $this->filter->buildFilterQuery(['brand' => 'EVELENS']);

        $this->assertSame(' WHERE p.brand = ?', $result['sql']);
        $this->assertSame(['EVELENS'], $result['params']);
    }

    public function test_brand_valid_multiple_partition(): void
    {
        $result = $this->filter->buildFilterQuery(['brands' => ['EVELENS', 'Chemi']]);

        $this->assertSame(' WHERE p.brand IN (?,?)', $result['sql']);
        $this->assertSame(['EVELENS', 'Chemi'], $result['params']);
    }

    public function test_search_empty_partition_is_ignored(): void
    {
        $result = $this->filter->buildFilterQuery(['search' => '   ']);

        $this->assertSame('', $result['sql']);
        $this->assertSame([], $result['params']);
    }

    public function test_search_non_empty_partition_adds_like_conditions(): void
    {
        $result = $this->filter->buildFilterQuery(['search' => ' aviator ']);

        $this->assertStringContainsString('p.name LIKE ?', $result['sql']);
        $this->assertSame(['%aviator%', '%aviator%', '%aviator%', '%aviator%'], $result['params']);
    }

    public function test_payment_method_partitions(): void
    {
        $this->assertTrue($this->isSupportedPaymentMethod('cod'));
        $this->assertTrue($this->isSupportedPaymentMethod('bank_transfer'));
        $this->assertFalse($this->isSupportedPaymentMethod('crypto'));
        $this->assertFalse($this->isSupportedPaymentMethod(''));
    }

    public function test_voucher_status_partitions(): void
    {
        $this->assertSame('valid', $this->classifyVoucher(10, 2, strtotime('+1 day')));
        $this->assertSame('used_up', $this->classifyVoucher(10, 10, strtotime('+1 day')));
        $this->assertSame('expired', $this->classifyVoucher(10, 2, strtotime('-1 day')));
    }

    public function test_email_format_partitions(): void
    {
        $this->assertTrue($this->isValidEmail('user@example.com'));
        $this->assertFalse($this->isValidEmail('userexample.com'));
        $this->assertFalse($this->isValidEmail('user@.com'));
        $this->assertFalse($this->isValidEmail(''));
    }

    public function test_order_state_transition_partitions(): void
    {
        $this->assertTrue($this->isValidTransition('pending', 'paid'));
        $this->assertTrue($this->isValidTransition('pending', 'cancelled'));
        $this->assertFalse($this->isValidTransition('processing', 'paid'));
        $this->assertFalse($this->isValidTransition('pending', 'shipped'));
        $this->assertFalse($this->isValidTransition('unknown', 'paid'));
    }

    public function test_ticket_priority_partitions(): void
    {
        $this->assertTrue($this->isValidTicketPriority('low'));
        $this->assertTrue($this->isValidTicketPriority('normal'));
        $this->assertTrue($this->isValidTicketPriority('high'));
        $this->assertFalse($this->isValidTicketPriority('urgent'));
        $this->assertFalse($this->isValidTicketPriority(''));
    }

    private function isSupportedPaymentMethod(string $method): bool
    {
        return in_array($method, ['cod', 'bank_transfer'], true);
    }

    private function classifyVoucher(int $limit, int $used, int $endsAt): string
    {
        if ($used >= $limit) {
            return 'used_up';
        }

        if (time() > $endsAt) {
            return 'expired';
        }

        return 'valid';
    }

    private function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function isValidTransition(string $currentState, string $nextState): bool
    {
        $allowedTransitions = [
            'pending' => ['paid', 'cancelled'],
            'paid' => ['processing', 'cancelled'],
            'processing' => ['shipped'],
            'shipped' => ['completed'],
        ];

        return in_array($nextState, $allowedTransitions[$currentState] ?? [], true);
    }

    private function isValidTicketPriority(string $priority): bool
    {
        return in_array($priority, ['low', 'normal', 'high'], true);
    }
}
