<?php

declare(strict_types=1);

namespace Windwalker\ORM\Test\Upsert;

use Windwalker\Database\Platform\DbPlatform;

final class OrmUpsertSQLServerTest extends AbstractOrmUpsertTestCase
{
    protected static DbPlatform $platform = DbPlatform::SQLServer;
    protected static string $driver = 'pdo_sqlsrv';
}
