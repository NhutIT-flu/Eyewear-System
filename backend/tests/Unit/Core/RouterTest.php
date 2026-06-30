<?php

namespace Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Core\Router;

/**
 * Unit tests for Core\Router
 * Tests route registration, grouping, URL matching, dispatch, and response logic.
 */
class RouterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->resetRouter();
    }

    private function resetRouter(): void
    {
        $ref = new \ReflectionClass(Router::class);

        $routes = $ref->getProperty('routes');
        $routes->setAccessible(true);
        $routes->setValue(null, []);

        $prefix = $ref->getProperty('currentPrefix');
        $prefix->setAccessible(true);
        $prefix->setValue(null, '');

        $middleware = $ref->getProperty('currentMiddleware');
        $middleware->setAccessible(true);
        $middleware->setValue(null, []);
    }

    // ═════════════════════════════════════════════════════════════
    //  Route Registration
    // ═════════════════════════════════════════════════════════════

    public function test_get_route_registers_correctly(): void
    {
        Router::get('/products', ['ProductController', 'index']);

        $routes = $this->getRoutes();
        $this->assertCount(1, $routes);
        $this->assertEquals('GET', $routes[0]['method']);
        $this->assertEquals('/products', $routes[0]['uri']);
    }

    public function test_post_route_registers_correctly(): void
    {
        Router::post('/products', ['ProductController', 'store']);
        $routes = $this->getRoutes();
        $this->assertEquals('POST', $routes[0]['method']);
    }

    public function test_put_route_registers_correctly(): void
    {
        Router::put('/products/{id}', ['ProductController', 'update']);
        $routes = $this->getRoutes();
        $this->assertEquals('PUT', $routes[0]['method']);
        $this->assertEquals('/products/{id}', $routes[0]['uri']);
    }

    public function test_patch_route_registers_correctly(): void
    {
        Router::patch('/products/{id}', ['ProductController', 'patch']);
        $routes = $this->getRoutes();
        $this->assertEquals('PATCH', $routes[0]['method']);
    }

    public function test_delete_route_registers_correctly(): void
    {
        Router::delete('/products/{id}', ['ProductController', 'destroy']);
        $routes = $this->getRoutes();
        $this->assertEquals('DELETE', $routes[0]['method']);
    }

    public function test_multiple_routes_registered(): void
    {
        Router::get('/products', ['ProductController', 'index']);
        Router::post('/products', ['ProductController', 'store']);
        Router::get('/products/{id}', ['ProductController', 'show']);

        $this->assertCount(3, $this->getRoutes());
    }

    // ═════════════════════════════════════════════════════════════
    //  Route addRoute — path normalization
    // ═════════════════════════════════════════════════════════════

    public function test_trailing_slashes_trimmed(): void
    {
        Router::get('/products/', ['ProductController', 'index']);
        $routes = $this->getRoutes();
        $this->assertEquals('/products', $routes[0]['uri']);
    }

    public function test_leading_slashes_normalized(): void
    {
        Router::get('products', ['ProductController', 'index']);
        $routes = $this->getRoutes();
        $this->assertEquals('/products', $routes[0]['uri']);
    }

    // ═════════════════════════════════════════════════════════════
    //  Route Grouping
    // ═════════════════════════════════════════════════════════════

    public function test_group_with_prefix(): void
    {
        Router::group(['prefix' => 'api/v1'], function () {
            Router::get('/products', ['ProductController', 'index']);
        });
        $this->assertEquals('/api/v1/products', $this->getRoutes()[0]['uri']);
    }

    public function test_nested_groups(): void
    {
        Router::group(['prefix' => 'api'], function () {
            Router::group(['prefix' => 'v1'], function () {
                Router::get('/users', ['UserController', 'index']);
            });
        });
        $this->assertEquals('/api/v1/users', $this->getRoutes()[0]['uri']);
    }

    public function test_group_with_string_middleware(): void
    {
        Router::group(['middleware' => 'auth'], function () {
            Router::get('/profile', ['ProfileController', 'show']);
        });
        $this->assertContains('auth', $this->getRoutes()[0]['middleware']);
    }

    public function test_group_with_array_middleware(): void
    {
        Router::group(['middleware' => ['auth', 'role:admin']], function () {
            Router::get('/admin', ['AdminController', 'index']);
        });
        $routes = $this->getRoutes();
        $this->assertContains('auth', $routes[0]['middleware']);
        $this->assertContains('role:admin', $routes[0]['middleware']);
    }

    public function test_group_prefix_does_not_leak(): void
    {
        Router::group(['prefix' => 'api'], function () {
            Router::get('/inside', ['Controller', 'inside']);
        });
        Router::get('/outside', ['Controller', 'outside']);

        $routes = $this->getRoutes();
        $this->assertEquals('/api/inside', $routes[0]['uri']);
        $this->assertEquals('/outside', $routes[1]['uri']);
    }

    public function test_group_middleware_does_not_leak(): void
    {
        Router::group(['middleware' => 'auth'], function () {
            Router::get('/protected', ['Controller', 'protected']);
        });
        Router::get('/public', ['Controller', 'public']);

        $routes = $this->getRoutes();
        $this->assertContains('auth', $routes[0]['middleware']);
        $this->assertEmpty($routes[1]['middleware']);
    }

    public function test_group_with_both_prefix_and_middleware(): void
    {
        Router::group(['prefix' => 'api/v1', 'middleware' => 'auth'], function () {
            Router::get('/orders', ['OrderController', 'index']);
        });
        $routes = $this->getRoutes();
        $this->assertEquals('/api/v1/orders', $routes[0]['uri']);
        $this->assertContains('auth', $routes[0]['middleware']);
    }

    // ═════════════════════════════════════════════════════════════
    //  Inline middleware
    // ═════════════════════════════════════════════════════════════

    public function test_inline_string_middleware(): void
    {
        Router::get('/admin', ['AdminController', 'index'])->middleware('auth');
        $this->assertContains('auth', $this->getRoutes()[0]['middleware']);
    }

    public function test_inline_array_middleware(): void
    {
        Router::get('/admin', ['AdminController', 'index'])->middleware(['auth', 'role:admin']);
        $routes = $this->getRoutes();
        $this->assertContains('auth', $routes[0]['middleware']);
        $this->assertContains('role:admin', $routes[0]['middleware']);
    }

    public function test_inline_middleware_merges_with_group(): void
    {
        Router::group(['middleware' => 'cors'], function () {
            Router::get('/data', ['DataController', 'index'])->middleware('auth');
        });
        $routes = $this->getRoutes();
        $this->assertContains('cors', $routes[0]['middleware']);
        $this->assertContains('auth', $routes[0]['middleware']);
    }

    // ═════════════════════════════════════════════════════════════
    //  Route matching (matchRoute via reflection)
    // ═════════════════════════════════════════════════════════════

    public function test_exact_route_match(): void
    {
        $params = $this->invokeMatchRoute('/products', '/products');
        $this->assertIsArray($params);
        $this->assertEmpty($params);
    }

    public function test_single_parameter_match(): void
    {
        $params = $this->invokeMatchRoute('/products/{id}', '/products/42');
        $this->assertEquals('42', $params['id']);
    }

    public function test_multiple_parameters_match(): void
    {
        $params = $this->invokeMatchRoute('/users/{userId}/orders/{orderId}', '/users/5/orders/123');
        $this->assertEquals('5', $params['userId']);
        $this->assertEquals('123', $params['orderId']);
    }

    public function test_no_match_returns_null(): void
    {
        $this->assertNull($this->invokeMatchRoute('/products/{id}', '/categories/42'));
    }

    public function test_no_match_extra_segments(): void
    {
        $this->assertNull($this->invokeMatchRoute('/products/{id}', '/products/42/details'));
    }

    public function test_url_encoded_param_decoded(): void
    {
        $params = $this->invokeMatchRoute('/search/{term}', '/search/hello%20world');
        $this->assertEquals('hello world', $params['term']);
    }

    public function test_underscore_param_name(): void
    {
        $params = $this->invokeMatchRoute('/users/{user_id}', '/users/99');
        $this->assertEquals('99', $params['user_id']);
    }

    // ═════════════════════════════════════════════════════════════
    //  sendResponse (via reflection)
    // ═════════════════════════════════════════════════════════════

    public function test_send_response_null_outputs_nothing(): void
    {
        ob_start();
        $this->invokeSendResponse(null);
        $output = ob_get_clean();
        $this->assertEquals('', $output);
    }

    public function test_send_response_array_outputs_json(): void
    {
        ob_start();
        $this->invokeSendResponse(['success' => true]);
        $output = ob_get_clean();
        $this->assertJson($output);
        $decoded = json_decode($output, true);
        $this->assertTrue($decoded['success']);
    }

    public function test_send_response_object_outputs_json(): void
    {
        $obj = (object)['key' => 'value'];
        ob_start();
        $this->invokeSendResponse($obj);
        $output = ob_get_clean();
        $this->assertJson($output);
        $decoded = json_decode($output, true);
        $this->assertEquals('value', $decoded['key']);
    }

    public function test_send_response_string_outputs_raw(): void
    {
        ob_start();
        $this->invokeSendResponse('Hello World');
        $output = ob_get_clean();
        $this->assertEquals('Hello World', $output);
    }

    public function test_send_response_integer_outputs_raw(): void
    {
        ob_start();
        $this->invokeSendResponse(42);
        $output = ob_get_clean();
        $this->assertEquals('42', $output);
    }

    // ═════════════════════════════════════════════════════════════
    //  dispatch — not found route
    // ═════════════════════════════════════════════════════════════

    public function test_dispatch_unknown_route_returns_not_found(): void
    {
        ob_start();
        Router::dispatch('GET', '/nonexistent');
        $output = ob_get_clean();

        $decoded = json_decode($output, true);
        $this->assertFalse($decoded['success']);
        $this->assertStringContainsString('not found', $decoded['message']);
    }

    public function test_dispatch_normalizes_trailing_slash(): void
    {
        ob_start();
        Router::dispatch('GET', '/some/path/');
        $output = ob_get_clean();

        $decoded = json_decode($output, true);
        $this->assertStringContainsString('not found', $decoded['message']);
    }

    public function test_dispatch_normalizes_query_string(): void
    {
        ob_start();
        Router::dispatch('GET', '/test?foo=bar');
        $output = ob_get_clean();

        $decoded = json_decode($output, true);
        $this->assertStringContainsString('not found', $decoded['message']);
    }

    // ═════════════════════════════════════════════════════════════
    //  runMiddleware (via reflection)
    // ═════════════════════════════════════════════════════════════

    public function test_run_middleware_empty_array(): void
    {
        $result = $this->invokeRunMiddleware([]);
        $this->assertTrue($result);
    }

    public function test_run_middleware_skips_empty_strings(): void
    {
        $result = $this->invokeRunMiddleware(['', '']);
        $this->assertTrue($result);
    }

    public function test_run_middleware_skips_non_string(): void
    {
        $result = $this->invokeRunMiddleware([null, 0, false]);
        $this->assertTrue($result);
    }

    public function test_run_middleware_auth_without_token_fails(): void
    {
        // No auth header set
        unset($_SERVER['HTTP_AUTHORIZATION'], $_SERVER['REDIRECT_HTTP_AUTHORIZATION']);

        ob_start();
        $result = $this->invokeRunMiddleware(['auth']);
        ob_get_clean();

        $this->assertFalse($result);
    }

    public function test_run_middleware_auth_with_valid_token_passes(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . base64_encode('1:admin');

        ob_start();
        $result = $this->invokeRunMiddleware(['auth']);
        ob_get_clean();

        $this->assertTrue($result);

        // Clean up
        unset($_SERVER['HTTP_AUTHORIZATION'], $_SERVER['AUTH_TOKEN'], $_SERVER['AUTH_USER_ID'], $_SERVER['AUTH_USER_ROLE'], $_SERVER['AUTH_TOKEN_GUARD']);
    }

    public function test_run_middleware_role_with_empty_roles_passes(): void
    {
        $result = $this->invokeRunMiddleware(['role:']);
        $this->assertTrue($result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_run_middleware_cors_non_options(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $result = $this->invokeRunMiddleware(['cors']);
        $this->assertTrue($result);
    }

    // ═════════════════════════════════════════════════════════════
    //  Helpers
    // ═════════════════════════════════════════════════════════════

    private function getRoutes(): array
    {
        $ref = new \ReflectionClass(Router::class);
        $prop = $ref->getProperty('routes');
        $prop->setAccessible(true);
        return $prop->getValue(null);
    }

    private function invokeMatchRoute(string $routeUri, string $requestUri): ?array
    {
        $ref = new \ReflectionClass(Router::class);
        $method = $ref->getMethod('matchRoute');
        $method->setAccessible(true);
        return $method->invoke(null, $routeUri, $requestUri);
    }

    private function invokeSendResponse($response): void
    {
        $ref = new \ReflectionClass(Router::class);
        $method = $ref->getMethod('sendResponse');
        $method->setAccessible(true);
        $method->invoke(null, $response);
    }

    private function invokeRunMiddleware(array $middleware): bool
    {
        $ref = new \ReflectionClass(Router::class);
        $method = $ref->getMethod('runMiddleware');
        $method->setAccessible(true);
        return $method->invoke(null, $middleware);
    }
}
