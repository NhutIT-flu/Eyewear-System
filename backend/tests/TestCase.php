<?php

namespace Tests;

/**
 * Base test case for the custom PHP framework.
 * Provides common assertion, HTTP helper, and env loading logic for all tests.
 */
abstract class TestCase
{
    protected static int $passed = 0;
    protected static int $failed = 0;

    // -------------------------------------------------------------------------
    // Lifecycle
    // -------------------------------------------------------------------------

    protected function setUp(): void {}
    protected function tearDown(): void {}

    // -------------------------------------------------------------------------
    // Assertions
    // -------------------------------------------------------------------------

    protected function assertTrue(bool $condition, string $message = ''): void
    {
        if (!$condition) {
            throw new \RuntimeException($message ?: 'Expected true but got false.');
        }
    }

    protected function assertFalse(bool $condition, string $message = ''): void
    {
        if ($condition) {
            throw new \RuntimeException($message ?: 'Expected false but got true.');
        }
    }

    protected function assertEquals($expected, $actual, string $message = ''): void
    {
        if ($expected != $actual) {
            throw new \RuntimeException(
                $message ?: "Expected " . var_export($expected, true) . " but got " . var_export($actual, true)
            );
        }
    }

    protected function assertSame($expected, $actual, string $message = ''): void
    {
        if ($expected !== $actual) {
            throw new \RuntimeException(
                $message ?: "Expected (strict) " . var_export($expected, true) . " but got " . var_export($actual, true)
            );
        }
    }

    protected function assertNotEmpty($value, string $message = ''): void
    {
        if (empty($value)) {
            throw new \RuntimeException($message ?: 'Expected non-empty value.');
        }
    }

    protected function assertArrayKey(array $array, string $key, string $message = ''): void
    {
        if (!array_key_exists($key, $array)) {
            throw new \RuntimeException($message ?: "Expected array to have key '{$key}'.");
        }
    }

    protected function assertStatus(int $expected, int $actual, string $context = ''): void
    {
        if ($expected !== $actual) {
            $ctx = $context ? " [{$context}]" : '';
            throw new \RuntimeException("Expected HTTP {$expected}, got {$actual}.{$ctx}");
        }
    }

    // -------------------------------------------------------------------------
    // HTTP helpers
    // -------------------------------------------------------------------------

    /**
     * Send a JSON POST request.
     */
    protected function postJson(string $url, array $payload, array $headers = []): array
    {
        return $this->sendRequest('POST', $url, $payload, $headers);
    }

    /**
     * Send a JSON GET request.
     */
    protected function getJson(string $url, array $headers = []): array
    {
        return $this->sendRequest('GET', $url, null, $headers);
    }

    /**
     * Send a JSON PUT request.
     */
    protected function putJson(string $url, array $payload, array $headers = []): array
    {
        return $this->sendRequest('PUT', $url, $payload, $headers);
    }

    protected function sendRequest(string $method, string $url, ?array $payload, array $extraHeaders = []): array
    {
        $body = $payload !== null ? json_encode($payload) : '';

        $defaultHeaders = [
            'Content-Type: application/json',
            'Accept: application/json',
        ];
        if ($body !== '') {
            $defaultHeaders[] = 'Content-Length: ' . strlen($body);
        }
        $allHeaders = array_merge($defaultHeaders, $extraHeaders);

        $context = stream_context_create([
            'http' => [
                'method'        => $method,
                'header'        => implode("\r\n", $allHeaders),
                'content'       => $body,
                'ignore_errors' => true,
                'timeout'       => 15,
            ],
        ]);

        $responseBody = @file_get_contents($url, false, $context);
        $statusLine   = $http_response_header[0] ?? 'HTTP/1.1 500 Internal Server Error';

        preg_match('/\s(\d{3})\s/', $statusLine, $matches);
        $statusCode = isset($matches[1]) ? (int) $matches[1] : 500;

        $decoded = json_decode((string) $responseBody, true);

        return [
            'status' => $statusCode,
            'body'   => is_array($decoded) ? $decoded : ['raw' => $responseBody],
        ];
    }

    // -------------------------------------------------------------------------
    // Env helpers
    // -------------------------------------------------------------------------

    protected function loadEnv(): array
    {
        $candidates = [
            dirname(__DIR__) . '/.env.local',
            dirname(__DIR__) . '/.env',
            dirname(__DIR__, 2) . '/.env',
        ];
        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $this->parseEnvFile($path);
            }
        }
        return [];
    }

    protected function parseEnvFile(string $path): array
    {
        $result = [];
        $lines  = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return [];
        }
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || $line[0] === ';') {
                continue;
            }
            if (!str_contains($line, '=')) {
                continue;
            }
            [$name, $value] = explode('=', $line, 2);
            $name  = trim($name);
            $value = trim($value);
            if ($value !== '' && (($value[0] === '"' && str_ends_with($value, '"')) || ($value[0] === "'" && str_ends_with($value, "'")))) {
                $value = substr($value, 1, -1);
            }
            $result[$name] = $value;
        }
        return $result;
    }

    protected function envValue(array $config, array $keys, string $default = ''): string
    {
        foreach ($keys as $key) {
            if (isset($config[$key]) && $config[$key] !== '') {
                return $config[$key];
            }
        }
        return $default;
    }

    // -------------------------------------------------------------------------
    // Runner helper (called by subclasses at bottom of file)
    // -------------------------------------------------------------------------

    public static function runAll(string $className): void
    {
        $instance = new $className();
        $methods  = get_class_methods($instance);
        $testMethods = array_filter($methods, fn($m) => str_starts_with($m, 'test_'));

        $pass = 0;
        $fail = 0;

        echo "\n--- {$className} ---\n";

        foreach ($testMethods as $method) {
            try {
                $instance->setUp();
                $instance->$method();
                $instance->tearDown();
                echo "  PASS: {$method}\n";
                $pass++;
            } catch (\Throwable $e) {
                echo "  FAIL: {$method} — " . $e->getMessage() . "\n";
                $fail++;
            }
        }

        echo "  Result: {$pass} passed, {$fail} failed\n";
    }
}