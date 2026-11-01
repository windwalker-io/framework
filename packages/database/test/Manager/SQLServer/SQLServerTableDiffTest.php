<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\SQLServer;

use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Manager\AbstractTableDiffTestCase;

class SQLServerTableDiffTest extends AbstractTableDiffTestCase
{
    protected static DbPlatform $platform = DbPlatform::SQLServer;

    protected static string $driver = 'pdo_sqlsrv';
}
