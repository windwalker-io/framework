<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver\Sqlsrv;

use Windwalker\Database\Test\Driver\AbstractDriverTest;

/**
 * The SqlsrvDriverTest class.
 */
class SqlsrvDriverTest extends AbstractDriverTest
{
    protected static string $platform = 'SQLServer';

    protected static string $driverName = 'sqlsrv';
}
