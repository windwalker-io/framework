<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Enum;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Utilities\Test\Stub\Enum\StubMyclabsEnum;

class MyclabsEnumTest extends AbstractEnumTestCase
{
    protected static function getEnumClass(): string
    {
        return StubMyclabsEnum::class;
    }

    #[Test]
    public function valueAndNameCallsMapToLegacyAccessors(): void
    {
        self::assertSame('bar', StubMyclabsEnum::BAR()->value());
        self::assertSame('YOO', StubMyclabsEnum::YOO()->name());
    }
}
