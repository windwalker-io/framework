<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Platform\DbPlatform;

/**
 * DatabaseManager create/drop tests for platforms that support CREATE/DROP DATABASE.
 */
abstract class AbstractCreatableDatabaseManagerTestCase extends AbstractDatabaseManagerTestCase
{
    abstract protected static function dropDatabaseIfExists(string $name): void;

    /**
     * SQLite treats the database name as a file path, so never use a bare name there
     * or it will create a file in the current working directory.
     */
    protected static function tmpDatabaseName(string $name): string
    {
        if (static::$platform === DbPlatform::SQLite) {
            return sys_get_temp_dir() . DIRECTORY_SEPARATOR . $name;
        }

        return $name;
    }

    #[Test]
    public function databaseCreateAndDrop(): void
    {
        $manager = static::$db->getDatabase(static::tmpDatabaseName('ww_tmp_manager_db'), true);

        try {
            static::dropDatabaseIfExists(static::tmpDatabaseName('ww_tmp_manager_db'));
            $manager->create();
            self::assertTrue($manager->exists());

            // Existing DB with ifNotExists must not fail
            $manager->create(true);
            self::assertTrue($manager->exists());

            $manager->drop();
            self::assertFalse($manager->exists());
        } finally {
            static::dropDatabaseIfExists(static::tmpDatabaseName('ww_tmp_manager_db'));
        }
    }

    #[Test]
    public function databaseDropIfExistsDropsExistingDatabase(): void
    {
        $manager = static::$db->getDatabase(static::tmpDatabaseName('ww_tmp_manager_db2'), true);

        try {
            $manager->create();
            $manager->drop(true);

            self::assertFalse($manager->exists());
        } finally {
            static::dropDatabaseIfExists(static::tmpDatabaseName('ww_tmp_manager_db2'));
        }
    }
}
