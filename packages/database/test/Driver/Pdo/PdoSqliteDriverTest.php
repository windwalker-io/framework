<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver\Pdo;

use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Driver\AbstractDriverTestCase;

/**
 * The PdoSqliteDriverTest class.
 */
class PdoSqliteDriverTest extends AbstractDriverTestCase
{
    protected static DbPlatform $platform = DbPlatform::SQLite;

    protected static string $driverName = 'pdo_sqlite';
}
