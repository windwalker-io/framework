<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Helper;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\Helper\HttpHelper;

class HttpHelperTest extends TestCase
{
    #[Test]
    public function legacyGetIpUsesSafeRemoteAddressByDefault(): void
    {
        $server = [
            'REMOTE_ADDR' => '203.0.113.10',
            'HTTP_X_FORWARDED_FOR' => '198.51.100.30',
        ];

        self::assertSame('203.0.113.10', HttpHelper::getIp($server));
    }
}
