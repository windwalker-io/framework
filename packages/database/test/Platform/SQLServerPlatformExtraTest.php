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
}
