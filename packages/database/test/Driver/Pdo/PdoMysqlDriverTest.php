<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver\Pdo;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Driver\AbstractDriverTestCase;

/**
 * The PdoMysqlDriverTest class.
 */
class PdoMysqlDriverTest extends AbstractDriverTestCase
{
    protected static DbPlatform $platform = DbPlatform::MySQL;

    protected static string $driverName = 'pdo_mysql';

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
