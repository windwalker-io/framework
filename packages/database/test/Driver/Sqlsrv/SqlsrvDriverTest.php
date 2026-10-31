<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver\Sqlsrv;

use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Driver\AbstractDriverTestCase;

/**
 * The SqlsrvDriverTest class.
 */
class SqlsrvDriverTest extends AbstractDriverTestCase
{
    protected static DbPlatform $platform = DbPlatform::SQLServer;

    protected static string $driverName = 'sqlsrv';
}
