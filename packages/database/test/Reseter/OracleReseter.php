<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Reseter;

use PDO;
use Windwalker\Database\Platform\DbPlatform;

/**
 * The OracleReseter class.
 *
 * An Oracle "database" is the connected user's schema, so create/drop database are no-ops.
 */
class OracleReseter extends AbstractReseter
{
    protected static DbPlatform $platform = DbPlatform::Oracle;

    public function createDatabase(PDO $pdo, string $dbname): void
    {
        //
    }

    public function dropDatabase(PDO $pdo, string $dbname): void
    {
        //
    }

    public function clearAllTables(PDO $pdo, string $dbname): void
    {
        foreach ($pdo->query('SELECT view_name FROM user_views')->fetchAll(PDO::FETCH_COLUMN) as $view) {
            $pdo->exec('DROP VIEW ' . '"' . $view . '"');
        }

        foreach ($pdo->query('SELECT table_name FROM user_tables')->fetchAll(PDO::FETCH_COLUMN) as $table) {
            $pdo->exec('DROP TABLE ' . '"' . $table . '"' . ' CASCADE CONSTRAINTS PURGE');
        }
    }
}
