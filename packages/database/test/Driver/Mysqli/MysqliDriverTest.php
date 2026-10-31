<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver\Mysqli;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Driver\AbstractDriverTestCase;

/**
 * The MysqliDriverTest class.
 */
class MysqliDriverTest extends AbstractDriverTestCase
{
    protected static DbPlatform $platform = DbPlatform::MySQL;

    protected static string $driverName = 'mysqli';

    /**
     * @see  AbstractDriver::quote
     */
    #[Test]
    public function quote(): void
    {
        self::assertEquals(
            "'foo\'s #hello --options'",
            static::$driver->quote("foo's #hello --options")
        );
    }

    /**
     * @see  AbstractDriver::escape
     */
    #[Test]
    public function escape(): void
    {
        self::assertEquals(
            "foo\'s #hello --options",
            static::$driver->escape("foo's #hello --options")
        );
    }
}
