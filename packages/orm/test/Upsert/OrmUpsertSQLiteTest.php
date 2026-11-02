<?php

declare(strict_types=1);

namespace Windwalker\ORM\Test\Upsert;

use Windwalker\Database\Platform\DbPlatform;

final class OrmUpsertSQLiteTest extends AbstractOrmUpsertTestCase
{
    protected static DbPlatform $platform = DbPlatform::SQLite;
    protected static string $driver = 'pdo_sqlite';
}
