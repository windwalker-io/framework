<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver\Pdo;

use Windwalker\Database\Platform\AbstractPlatform;
use Windwalker\Database\Test\Driver\AbstractDriverTest;

/**
 * The PdoMysqlDriverTest class.
 */
class PdoMysqlDriverTest extends AbstractDriverTest
{
    protected static string $platform = AbstractPlatform::MYSQL;

    protected static string $driverName = 'pdo_mysql';

    /**
     * @see  AbstractDriver::quote
     */
    public function testQuote(): void
    {
        self::assertEquals(
            "'foo\'s #hello --options'",
            static::$driver->quote("foo's #hello --options")
        );
    }

    /**
     * @see  AbstractDriver::escape
     */
    public function testEscape(): void
    {
        self::assertEquals(
            "foo\'s #hello --options",
            static::$driver->escape("foo's #hello --options")
        );
    }
}
