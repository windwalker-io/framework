<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Enum;

use PHPUnit\Framework\Attributes\Before;
use Windwalker\Utilities\Test\Stub\Enum\StubBackedEnum;

class NativeEnumTest extends AbstractEnumTestCase
{
    #[Before]
    protected function skipWhenNativeEnumIsUnsupported(): void
    {
        if (PHP_VERSION_ID < 80100) {
            self::markTestSkipped('native enum must test in 8.1');
        }
    }

    protected static function getEnumClass(): string
    {
        return StubBackedEnum::class;
    }
}
