<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\PostgreSQL;

use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Manager\AbstractTableDiffTestCase;

class PostgreSQLTableDiffTest extends AbstractTableDiffTestCase
{
    protected static DbPlatform $platform = DbPlatform::PostgreSQL;

    protected static string $driver = 'pdo_pgsql';
}
