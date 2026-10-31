<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Enum;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Enum\EnumBCTrait;

class EnumBCTraitTest extends TestCase
{
    #[Test]
    public function equalsComparesByIdentity(): void
    {
        $a = new class () {
            use EnumBCTrait;
        };
        $b = new class () {
            use EnumBCTrait;
        };

        self::assertTrue($a->equals($a));
        self::assertFalse($a->equals($b));
        self::assertFalse($a->equals());
    }
}
