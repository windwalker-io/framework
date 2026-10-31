<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Database\Driver\DriverOptions;

class DriverOptionsTest extends TestCase
{
    #[Test]
    public function tlsOptionsFiltersNullValues(): void
    {
        self::assertSame(
            ['ca' => '/ca.pem', 'verify' => false],
            DriverOptions::tlsOptions(ca: '/ca.pem', verify: false)
        );
        self::assertSame([], DriverOptions::tlsOptions());
    }
}
