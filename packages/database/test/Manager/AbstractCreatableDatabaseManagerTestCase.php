<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager;

use PHPUnit\Framework\Attributes\Test;

/**
 * DatabaseManager create/drop tests for platforms that support CREATE/DROP DATABASE.
 */
abstract class AbstractCreatableDatabaseManagerTestCase extends AbstractDatabaseManagerTestCase
{
    abstract protected static function dropDatabaseIfExists(string $name): void;

    #[Test]
    public function databaseCreateAndDrop(): void
    {
        $manager = static::$db->getDatabase('ww_tmp_manager_db', true);

        try {
            static::dropDatabaseIfExists('ww_tmp_manager_db');
            $manager->create();
            self::assertTrue($manager->exists());

            // Existing DB with ifNotExists must not fail
            $manager->create(true);
            self::assertTrue($manager->exists());

            $manager->drop();
            self::assertFalse($manager->exists());
        } finally {
            static::dropDatabaseIfExists('ww_tmp_manager_db');
        }
    }

    #[Test]
    public function databaseDropIfExistsDropsExistingDatabase(): void
    {
        $manager = static::$db->getDatabase('ww_tmp_manager_db2', true);

        try {
            $manager->create();
            $manager->drop(true);

            self::assertFalse($manager->exists());
        } finally {
            static::dropDatabaseIfExists('ww_tmp_manager_db2');
        }
    }
}
