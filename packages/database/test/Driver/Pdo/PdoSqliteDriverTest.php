<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver\Pdo;

use Windwalker\Database\Platform\AbstractPlatform;
use Windwalker\Database\Test\Driver\AbstractDriverTestCase;

/**
 * The PdoSqliteDriverTest class.
 */
class PdoSqliteDriverTest extends AbstractDriverTestCase
{
    protected static string $platform = AbstractPlatform::SQLITE;

    protected static string $driverName = 'pdo_sqlite';
}
