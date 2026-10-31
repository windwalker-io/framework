<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Stub\Enum;

use Windwalker\Utilities\Enum\EnumSingleton;

/**
 * Fixture for EnumSingletonTest. Can be removed together with EnumSingleton (deprecated).
 *
 * @method  static  static  FOO()
 * @method  static  static  BAR()
 */
class StubSingletonEnum extends EnumSingleton
{
    public const FOO = 'foo';

    public const BAR = 'bar';
}
