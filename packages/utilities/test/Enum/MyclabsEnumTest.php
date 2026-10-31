<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Enum;

use Windwalker\Utilities\Test\Stub\Enum\StubMyclabsEnum;

class MyclabsEnumTest extends AbstractEnumTestCase
{
    protected static function getEnumClass(): string
    {
        return StubMyclabsEnum::class;
    }
}
