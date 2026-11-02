<?php

declare(strict_types=1);

namespace Windwalker\ORM\Test\Upsert;

use Windwalker\Database\Platform\DbPlatform;

final class OrmUpsertPostgreSQLTest extends AbstractOrmUpsertTestCase
{
    protected static DbPlatform $platform = DbPlatform::PostgreSQL;
    protected static string $driver = 'pdo_pgsql';
}
