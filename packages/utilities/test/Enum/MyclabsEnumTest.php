<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Enum;

use Windwalker\Utilities\Test\Stub\Enum\StubMyclabsEnum;

/**
 * The NativeEnumTest class.
 */
class MyclabsEnumTest extends AbstractEnumTest
{
    protected static function getEnumClass(): string
    {
        return StubMyclabsEnum::class;
    }
}
