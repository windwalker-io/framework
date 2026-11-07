<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Test\Swoole;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Reactor\Protocol;
use Windwalker\Reactor\Swoole\SwooleServer;

class SwooleServerTest extends TestCase
{
    #[Test]
    public function createSubServerAppliesProtocolsOnlyToSubServer(): void
    {
        if (!extension_loaded('swoole')) {
            self::markTestSkipped('The Swoole extension is required.');
        }

        $server = new SwooleServer();
        $subServer = $server->createSubServer(Protocol::HTTP | Protocol::WEBSOCKET);

        self::assertSame([], $server->getConfig());
        self::assertSame(
            [
                'open_http_protocol' => true,
                'open_http2_protocol' => false,
                'open_websocket_protocol' => true,
                'open_mqtt_protocol' => false,
            ],
            $subServer->getConfig(),
        );
    }
}
