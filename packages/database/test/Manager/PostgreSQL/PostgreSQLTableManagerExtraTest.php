<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\PostgreSQL;

use LogicException;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Schema\Ddl\Column;
use Windwalker\Database\Test\Manager\AbstractTableManagerExtraTestCase;

class PostgreSQLTableManagerExtraTest extends AbstractTableManagerExtraTestCase
{
    protected static DbPlatform $platform = DbPlatform::PostgreSQL;

    protected static string $driver = 'pdo_pgsql';

    protected static string $schema = 'public';

    protected static string $collation = 'C';

    protected static function dropTable(string $table): void
    {
        self::$baseConn->exec("DROP TABLE IF EXISTS $table");
    }

    #[Test]
    public function virtualGeneratedColumnDependsOnServerVersion(): void
    {
        $column = (new Column('double_price', 'integer'))->virtual('price * 2');
        $platform = self::$db->getPlatform();

        if (version_compare(self::$db->getDriver()->getVersion(), '18.0', '<')) {
            $this->expectException(LogicException::class);

            $platform->getColumnExpression($column);

            return;
        }

        self::assertStringContainsString('VIRTUAL', (string) $platform->getColumnExpression($column));
    }
}
