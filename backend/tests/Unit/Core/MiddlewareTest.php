<?php

namespace Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use App\Http\Middleware\CorsMiddleware;
use App\Http\Middleware\RoleMiddleware;

/**
 * Unit tests for CorsMiddleware and RoleMiddleware.
 */
class MiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        unset(
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['AUTH_USER_ROLE']
        );
    }

    protected function tearDown(): void
    {
        unset(
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['AUTH_USER_ROLE']
        );
        parent::tearDown();
    }

    // ─────────────────────────────────────────────────────────
    // CorsMiddleware
    // ─────────────────────────────────────────────────────────

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_cors_returns_true_for_non_options(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $result = CorsMiddleware::handle();
        $this->assertTrue($result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_cors_returns_false_for_options(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';

        $result = CorsMiddleware::handle();
        $this->assertFalse($result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_cors_returns_true_when_no_method(): void
    {
        unset($_SERVER['REQUEST_METHOD']);

        $result = CorsMiddleware::handle();
        $this->assertTrue($result);
    }

    // ─────────────────────────────────────────────────────────
    // RoleMiddleware
    // ─────────────────────────────────────────────────────────

    public function test_role_allows_when_no_roles_required(): void
    {
        $result = RoleMiddleware::handle([]);
        $this->assertTrue($result);
    }

    public function test_role_rejects_when_no_auth_role(): void
    {
        unset($_SERVER['AUTH_USER_ROLE']);

        ob_start();
        $result = RoleMiddleware::handle(['admin']);
        $output = ob_get_clean();

        $this->assertFalse($result);
        $decoded = json_decode($output, true);
        $this->assertFalse($decoded['success']);
    }

    public function test_role_allows_matching_role(): void
    {
        $_SERVER['AUTH_USER_ROLE'] = 'admin';

        ob_start();
        $result = RoleMiddleware::handle(['admin', 'staff']);
        ob_get_clean();

        $this->assertTrue($result);
    }

    public function test_role_rejects_non_matching_role(): void
    {
        $_SERVER['AUTH_USER_ROLE'] = 'customer';

        ob_start();
        $result = RoleMiddleware::handle(['admin', 'staff']);
        $output = ob_get_clean();

        $this->assertFalse($result);
        $decoded = json_decode($output, true);
        $this->assertFalse($decoded['success']);
        $this->assertStringContainsString('permission', $decoded['message']);
    }

    public function test_role_allows_with_comma_separated_roles(): void
    {
        $_SERVER['AUTH_USER_ROLE'] = 'customer,admin';

        ob_start();
        $result = RoleMiddleware::handle(['admin']);
        ob_get_clean();

        $this->assertTrue($result);
    }

    public function test_role_rejects_all_comma_separated_not_matching(): void
    {
        $_SERVER['AUTH_USER_ROLE'] = 'customer,viewer';

        ob_start();
        $result = RoleMiddleware::handle(['admin', 'staff']);
        ob_get_clean();

        $this->assertFalse($result);
    }
}
