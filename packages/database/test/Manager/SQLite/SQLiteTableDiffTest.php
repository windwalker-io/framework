<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\SQLite;

use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Manager\AbstractTableDiffTestCase;

class SQLiteTableDiffTest extends AbstractTableDiffTestCase
{
    protected static DbPlatform $platform = DbPlatform::SQLite;

    protected static string $driver = 'pdo_sqlite';
}
