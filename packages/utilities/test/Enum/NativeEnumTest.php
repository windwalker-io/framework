<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Enum;

use Windwalker\Utilities\Test\Stub\Enum\StubBackedEnum;

/**
 * The NativeEnumTest class.
 */
class NativeEnumTest extends AbstractEnumTestCase
{
    protected function setUp(): void
    {
        if (PHP_VERSION_ID < 80100) {
            self::markTestSkipped('native enum must test in 8.1');
        }

        parent::setUp();
    }

    protected static function getEnumClass(): string
    {
        return StubBackedEnum::class;
    }
}
