<?php

/**
 * Part of framework project.
 *
 * @copyright  Copyright (C) 2022 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Enum;

use Windwalker\Utilities\Test\Stub\Enum\StubBackedEnum;

/**
 * The NativeEnumTest class.
 */
class NativeEnumTest extends AbstractEnumTest
{
    protected static function getEnumClass(): string
    {
        return StubBackedEnum::class;
    }
}
