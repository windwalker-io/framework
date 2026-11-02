<?php

declare(strict_types=1);

namespace Windwalker\Cache\Test\Storage;

use Windwalker\Database\Platform\DbPlatform;

final class DatabaseStorageSQLServerTest extends AbstractDatabaseStoragePlatformTestCase
{
    protected static DbPlatform $platform = DbPlatform::SQLServer;
    protected static string $driver = 'pdo_sqlsrv';
}
