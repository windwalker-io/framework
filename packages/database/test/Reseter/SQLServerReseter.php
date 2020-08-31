<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Test\Reseter;

use phpDocumentor\Reflection\DocBlock\Tags\Reference\Url;

use function Windwalker\raw;

/**
 * The SQLServerReseter class.
 */
class SQLServerReseter extends AbstractReseter
{
    protected static string $platform = 'SQLServer';

    public function createDatabase(\PDO $pdo, string $dbname): void
    {
        $dbs = $pdo->query(
            $this->createQuery()
                ->select('name')
                ->from('master.dbo.sysdatabases')
                ->render(true)
        )
            ->fetchAll(\PDO::FETCH_COLUMN) ?: [];

        if (!in_array($dbname, $dbs, true)) {
            $pdo->exec('CREATE DATABASE ' . static::qn($dbname));
        }
    }

    public function clearAllTables(\PDO $pdo, string $dbname): void
    {
        // Drop Tables
        $tables = $pdo->query(
            $this->createQuery()
                ->select('TABLE_NAME')
                ->from('INFORMATION_SCHEMA.TABLES')
                ->where('TABLE_TYPE', 'BASE TABLE')
                ->where('TABLE_SCHEMA', '!=', 'INFORMATION_SCHEMA')
                ->render(true)
        )->fetchAll(\PDO::FETCH_COLUMN) ?: [];

        if ($tables) {
            foreach ($tables as $table) {
                $pdo->exec(
                    $this->dropTableSQL($table)
                );
            }
        }

        // Drop Views
        $tables = $pdo->query(
            $this->createQuery()
                ->select('TABLE_NAME')
                ->from('INFORMATION_SCHEMA.TABLES')
                ->where('TABLE_TYPE', 'VIEW')
                ->where('TABLE_SCHEMA', '!=', 'INFORMATION_SCHEMA')
                ->render(true)
        )->fetchAll(\PDO::FETCH_COLUMN) ?: [];

        if ($tables) {
            foreach ($tables as $table) {
                $pdo->exec(
                    $this->dropTableSQL($table, 'VIEW')
                );
            }
        }
    }

    protected function dropTableSQL(string $table, string $type = 'TABLE'): string
    {
        // Drop all foreign key reference to this table
        // @see https://social.msdn.microsoft.com/Forums/sqlserver/en-US/219f8a19-0026-49a1-a086-11c5d57d9c97/tsql-to-drop-all-constraints?forum=transactsql
        $sql = <<<SQL
declare @str varchar(max)
declare cur cursor for

    SELECT 'ALTER TABLE ' + '[' + s.name + '].[' + t.name + '] DROP CONSTRAINT ['+ f.name + ']'
    FROM sys.foreign_keys AS f
    LEFT JOIN sys.objects AS t ON f.parent_object_id = t.object_id
    LEFT JOIN sys.schemas AS s ON t.schema_id = s.schema_id
    WHERE s.name = 'dbo' AND f.referenced_object_id = object_id(%q)
    ORDER BY t.type

open cur
FETCH NEXT FROM cur INTO @str
WHILE (@@fetch_status = 0) BEGIN
    PRINT @str
    EXEC (@str)
    FETCH NEXT FROM cur INTO @str
END

close cur
deallocate cur;

DROP %r IF EXISTS %n
SQL;

        return $this->createQuery()->format(
            $sql,
            $table,
            $type,
            $table
        );
    }
}
