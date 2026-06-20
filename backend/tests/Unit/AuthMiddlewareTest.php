<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Http\Middleware\AuthMiddleware;

/**
 * Unit tests for AuthMiddleware
 * Comprehensive coverage of token extraction, validation, and guard logic.
 */
class AuthMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->clearAuthState();
    }

    protected function tearDown(): void
    {
        $this->clearAuthState();
        parent::tearDown();
    }

    private function clearAuthState(): void
    {
        unset(
            $_SERVER['HTTP_AUTHORIZATION'],
            $_SERVER['REDIRECT_HTTP_AUTHORIZATION'],
            $_SERVER['AUTH_TOKEN'],
            $_SERVER['AUTH_USER_ID'],
            $_SERVER['AUTH_USER_ROLE'],
            $_SERVER['AUTH_TOKEN_GUARD']
        );
    }

    // ═════════════════════════════════════════════════════════════
    //  Missing / empty token
    // ═════════════════════════════════════════════════════════════

    public function test_returns_false_when_no_authorization_header(): void
    {
        ob_start();
        $result = AuthMiddleware::handle();
        $output = ob_get_clean();

        $this->assertFalse($result);
        $decoded = json_decode($output, true);
        $this->assertFalse($decoded['success']);
        $this->assertStringContainsString('Missing', $decoded['message']);
    }

    public function test_returns_false_when_empty_authorization_header(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = '';

        ob_start();
        $result = AuthMiddleware::handle();
        ob_get_clean();

        $this->assertFalse($result);
    }

    // ═════════════════════════════════════════════════════════════
    //  Invalid tokens
    // ═════════════════════════════════════════════════════════════

    public function test_returns_false_for_non_base64_token(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer !!!invalid-base64!!!';

        ob_start();
        $result = AuthMiddleware::handle();
        ob_get_clean();

        $this->assertFalse($result);
    }

    public function test_returns_false_for_token_without_colon(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . base64_encode('justtext');

        ob_start();
        $result = AuthMiddleware::handle();
        $output = ob_get_clean();

        $this->assertFalse($result);
        $decoded = json_decode($output, true);
        $this->assertStringContainsString('Invalid', $decoded['message']);
    }

    public function test_returns_false_for_non_numeric_user_id(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . base64_encode('abc:admin');

        ob_start();
        $result = AuthMiddleware::handle();
        ob_get_clean();

        $this->assertFalse($result);
    }

    public function test_returns_false_for_basic_auth_header(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic dXNlcjpwYXNz';

        ob_start();
        $result = AuthMiddleware::handle();
        ob_get_clean();

        $this->assertFalse($result);
    }

    // ═════════════════════════════════════════════════════════════
    //  Valid tokens — various roles
    // ═════════════════════════════════════════════════════════════

    public function test_valid_admin_token(): void
    {
        $token = base64_encode('1:admin');
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

        ob_start();
        $result = AuthMiddleware::handle();
        ob_get_clean();

        $this->assertTrue($result);
        $this->assertEquals(1, $_SERVER['AUTH_USER_ID']);
        $this->assertEquals('admin', $_SERVER['AUTH_USER_ROLE']);
        $this->assertEquals($token, $_SERVER['AUTH_TOKEN']);
    }

    public function test_valid_customer_token(): void
    {
        $token = base64_encode('42:customer');
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

        ob_start();
        $result = AuthMiddleware::handle();
        ob_get_clean();

        $this->assertTrue($result);
        $this->assertEquals(42, $_SERVER['AUTH_USER_ID']);
        $this->assertEquals('customer', $_SERVER['AUTH_USER_ROLE']);
    }

    public function test_valid_staff_token(): void
    {
        $token = base64_encode('7:staff');
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

        ob_start();
        $result = AuthMiddleware::handle();
        ob_get_clean();

        $this->assertTrue($result);
        $this->assertEquals(7, $_SERVER['AUTH_USER_ID']);
        $this->assertEquals('staff', $_SERVER['AUTH_USER_ROLE']);
    }

    public function test_valid_token_with_three_parts(): void
    {
        $token = base64_encode('5:staff:extra');
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

        ob_start();
        $result = AuthMiddleware::handle();
        ob_get_clean();

        $this->assertTrue($result);
        $this->assertEquals(5, $_SERVER['AUTH_USER_ID']);
        $this->assertEquals('staff', $_SERVER['AUTH_USER_ROLE']);
    }

    // ═════════════════════════════════════════════════════════════
    //  Guard parameter
    // ═════════════════════════════════════════════════════════════

    public function test_stores_guard_parameter(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . base64_encode('1:admin');

        ob_start();
        AuthMiddleware::handle('sanctum');
        ob_get_clean();

        $this->assertEquals('sanctum', $_SERVER['AUTH_TOKEN_GUARD']);
    }

    public function test_null_guard_by_default(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . base64_encode('1:admin');

        ob_start();
        AuthMiddleware::handle();
        ob_get_clean();

        $this->assertNull($_SERVER['AUTH_TOKEN_GUARD']);
    }

    // ═════════════════════════════════════════════════════════════
    //  REDIRECT_HTTP_AUTHORIZATION fallback
    // ═════════════════════════════════════════════════════════════

    public function test_reads_from_redirect_http_authorization(): void
    {
        $token = base64_encode('10:customer');
        $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

        ob_start();
        $result = AuthMiddleware::handle();
        ob_get_clean();

        $this->assertTrue($result);
        $this->assertEquals(10, $_SERVER['AUTH_USER_ID']);
    }

    // ═════════════════════════════════════════════════════════════
    //  Combined / multiple Bearer tokens
    // ═════════════════════════════════════════════════════════════

    public function test_combined_bearer_tokens_uses_last(): void
    {
        $token1 = base64_encode('1:admin');
        $token2 = base64_encode('2:staff');
        $_SERVER['HTTP_AUTHORIZATION'] = "Bearer $token1, Bearer $token2";

        ob_start();
        $result = AuthMiddleware::handle();
        ob_get_clean();

        $this->assertTrue($result);
        // Should use the LAST Bearer token
        $this->assertEquals(2, $_SERVER['AUTH_USER_ID']);
        $this->assertEquals('staff', $_SERVER['AUTH_USER_ROLE']);
    }

    public function test_single_bearer_via_regex(): void
    {
        $token = base64_encode('99:admin');
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

        ob_start();
        $result = AuthMiddleware::handle();
        ob_get_clean();

        $this->assertTrue($result);
        $this->assertEquals(99, $_SERVER['AUTH_USER_ID']);
    }

    // ═════════════════════════════════════════════════════════════
    //  Edge cases
    // ═════════════════════════════════════════════════════════════

    public function test_bearer_lowercase_still_works(): void
    {
        $token = base64_encode('3:admin');
        $_SERVER['HTTP_AUTHORIZATION'] = 'bearer ' . $token;

        ob_start();
        $result = AuthMiddleware::handle();
        ob_get_clean();

        $this->assertTrue($result);
    }

    public function test_user_id_zero_is_not_numeric_enough(): void
    {
        $token = base64_encode('0:guest');
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

        ob_start();
        $result = AuthMiddleware::handle();
        ob_get_clean();

        // 0 is numeric, so should pass
        $this->assertTrue($result);
        $this->assertEquals(0, $_SERVER['AUTH_USER_ID']);
    }

    public function test_large_user_id(): void
    {
        $token = base64_encode('999999:admin');
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

        ob_start();
        $result = AuthMiddleware::handle();
        ob_get_clean();

        $this->assertTrue($result);
        $this->assertEquals(999999, $_SERVER['AUTH_USER_ID']);
    }
}
