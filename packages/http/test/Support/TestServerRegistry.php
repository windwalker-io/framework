<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Support;

/**
 * Process-level singleton that ensures exactly one PHP built-in test server
 * is started for the entire PHPUnit process lifecycle.
 *
 * The server is started on the first call to ensureStarted() and is cleaned up
 * automatically by the shutdown function registered inside PhpBuiltinServer.
 */
final class TestServerRegistry
{
    private static ?PhpBuiltinTestServer $server = null;

    private function __construct()
    {
    }

    public static function ensureStarted(): PhpBuiltinTestServer
    {
        if (static::$server === null) {
            $server = PhpBuiltinTestServer::create(
                documentRoot: dirname(__DIR__) . '/bin',
                routerScript: dirname(__DIR__) . '/bin/router.php',
            );

            $server->start();

            static::$server = $server;
        }

        return static::$server;
    }

    public static function getServer(): ?PhpBuiltinTestServer
    {
        return static::$server;
    }
}
