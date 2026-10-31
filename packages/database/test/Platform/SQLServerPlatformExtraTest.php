<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Platform;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\AbstractDatabaseTestCase;

class SQLServerPlatformExtraTest extends AbstractDatabaseTestCase
{
    protected static DbPlatform $platform = DbPlatform::SQLServer;

    protected static string $driver = 'pdo_sqlsrv';

    protected static function setupDatabase(): void {}

    #[Test]
    public function setColumnCommentWritesExtendedProperty(): void
    {
        $drop = "IF OBJECT_ID('ww_ss_extra', 'U') IS NOT NULL DROP TABLE ww_ss_extra";
        self::$baseConn->exec($drop);

        try {
            self::$baseConn->exec('CREATE TABLE ww_ss_extra (id INT, title NVARCHAR(50))');

            self::$db->getPlatform()->setColumnComment('ww_ss_extra', 'title', 'Hello comment');

            $value = self::$baseConn->query(
                "SELECT CAST(value AS NVARCHAR(100)) FROM sys.extended_properties
                 WHERE major_id = OBJECT_ID('ww_ss_extra') AND name = 'MS_Description'"
            )->fetchColumn();

            self::assertSame('Hello comment', $value);
        } finally {
            self::$baseConn->exec($drop);
        }
    }

    #[Test]
    public function dropIndexRemovesIndex(): void
    {
        $drop = "IF OBJECT_ID('ww_ss_extra_idx', 'U') IS NOT NULL DROP TABLE ww_ss_extra_idx";
        self::$baseConn->exec($drop);

        try {
            self::$baseConn->exec('CREATE TABLE ww_ss_extra_idx (id INT, title NVARCHAR(50))');
            self::$baseConn->exec('CREATE INDEX ww_ss_extra_title ON ww_ss_extra_idx (title)');

            self::$db->getPlatform()->dropIndex('ww_ss_extra_idx', 'ww_ss_extra_title', 'dbo');

            $count = self::$baseConn->query(
                "SELECT COUNT(*) FROM sys.indexes WHERE name = 'ww_ss_extra_title'"
            )->fetchColumn();

            self::assertSame(0, (int) $count);
        } finally {
            self::$baseConn->exec($drop);
        }
    }

    #[Test]
    public function setColumnCommentUpdatesExistingComment(): void
    {
        $drop = "IF OBJECT_ID('ww_ss_extra2', 'U') IS NOT NULL DROP TABLE ww_ss_extra2";
        self::$baseConn->exec($drop);

        try {
            self::$baseConn->exec('CREATE TABLE ww_ss_extra2 (id INT, title NVARCHAR(50))');

            $platform = self::$db->getPlatform();
            $platform->setColumnComment('ww_ss_extra2', 'title', 'First');
            $platform->setColumnComment('ww_ss_extra2', 'title', 'Second');

            $value = self::$baseConn->query(
                "SELECT CAST(value AS NVARCHAR(100)) FROM sys.extended_properties
                 WHERE major_id = OBJECT_ID('ww_ss_extra2') AND name = 'MS_Description'"
            )->fetchColumn();

            self::assertSame('Second', $value);
        } finally {
            self::$baseConn->exec($drop);
        }
    }
}
