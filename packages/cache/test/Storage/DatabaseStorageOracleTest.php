<?php

declare(strict_types=1);

namespace Windwalker\Cache\Test\Storage;

use Windwalker\Database\Platform\DbPlatform;

final class DatabaseStorageOracleTest extends AbstractDatabaseStoragePlatformTestCase
{
    protected static DbPlatform $platform = DbPlatform::Oracle;
    protected static string $driver = 'pdo_oci';
}
