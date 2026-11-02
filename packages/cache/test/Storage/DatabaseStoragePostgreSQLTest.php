<?php

declare(strict_types=1);

namespace Windwalker\Cache\Test\Storage;

use Windwalker\Database\Platform\DbPlatform;

final class DatabaseStoragePostgreSQLTest extends AbstractDatabaseStoragePlatformTestCase
{
    protected static DbPlatform $platform = DbPlatform::PostgreSQL;
    protected static string $driver = 'pdo_pgsql';
}
