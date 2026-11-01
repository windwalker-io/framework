<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\MySQL;

use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Manager\AbstractTableDiffTestCase;

class MySQLTableDiffTest extends AbstractTableDiffTestCase
{
    protected static DbPlatform $platform = DbPlatform::MySQL;

    protected static string $driver = 'pdo_mysql';
}
