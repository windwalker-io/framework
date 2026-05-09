<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Transport;

use Windwalker\Http\Test\Support\TestServerRegistry;

trait TestServerExistsTrait
{
    /**
     * Ensure the test HTTP server is reachable.
     *
     * When WINDWALKER_TEST_HTTP_URL is defined the tests use that external server.
     * Otherwise a PHP built-in server is started automatically via TestServerRegistry
     * and kept alive for the entire PHPUnit process (cleaned up by PHP's shutdown handler).
     */
    public static function checkTestServerRunningOrSkip(): void
    {
        if (
            function_exists('swoole_version')
            && version_compare(swoole_version(), '6.0.0', '<')
        ) {
            throw new \RuntimeException(
                'Swoole version must be 6.0.0 or higher to run Windwalker HTTP with Swoole.' .
                ' Please update your Swoole extension.'
            );
        }

        if (!defined('WINDWALKER_TEST_HTTP_URL')) {
            $server = TestServerRegistry::ensureStarted();

            define('WINDWALKER_TEST_HTTP_URL', $server->baseUrl());
        }
    }
}
