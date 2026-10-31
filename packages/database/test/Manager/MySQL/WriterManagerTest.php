<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\MySQL;

use Windwalker\Database\Test\Manager\AbstractWriterManagerTestCase;

class WriterManagerTest extends AbstractWriterManagerTestCase
{
    protected static function createWriterTable(): void
    {
        self::$baseConn->exec(
            <<<SQL
            CREATE TABLE IF NOT EXISTS `writer_items` (
                `id` int NOT NULL AUTO_INCREMENT,
                `title` varchar(255) NOT NULL DEFAULT '',
                `state` int NULL DEFAULT 0,
                `params` text NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_writer_items_title` (`title`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            SQL
        );
    }

    protected static function resetWriterTableData(): void
    {
        self::$baseConn->exec('TRUNCATE TABLE `writer_items`');
    }
}
