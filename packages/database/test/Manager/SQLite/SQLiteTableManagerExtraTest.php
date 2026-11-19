<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\SQLite;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Schema\Ddl\Column;
use Windwalker\Database\Schema\Ddl\Index;
use Windwalker\Database\Schema\Schema;
use Windwalker\Database\Test\Manager\AbstractTableManagerExtraTestCase;

class SQLiteTableManagerExtraTest extends AbstractTableManagerExtraTestCase
{
    protected static DbPlatform $platform = DbPlatform::SQLite;

    protected static string $driver = 'pdo_sqlite';

    protected static string $collation = 'NOCASE';

    protected static bool $supportsColumnAlter = false;

    protected static bool $supportsAddConstraint = false;

    protected static function dropTable(string $table): void
    {
        self::$baseConn->exec("DROP TABLE IF EXISTS $table");
    }

    /**
     * SQLite stores the `CREATE INDEX` statement verbatim in `sqlite_master`, so the real
     * expression text can be parsed back exactly instead of a placeholder like `(expression 1)`.
     */
    #[Test]
    public function functionalIndexExposesRealExpressionText(): void
    {
        $this->table->addIndex(new Index('idx_ww_lower_title')->expression('LOWER(title)'));
        $this->table->reset();

        $index = $this->table->getIndex('idx_ww_lower_title');

        self::assertSame(['LOWER(title)'], $index->getColumnNames());
    }

    #[Test]
    public function generatedColumnsAreListedAndNotAddedTwice(): void
    {
        $name = 'ww_tm_generated_list';

        static::dropTable($name);

        $define = static function (Schema $schema) {
            $schema->integer('price');
            $schema->integer('double_price')->stored('price * 2');
            $schema->integer('triple_price')->virtual('price * 3');
        };

        try {
            $table = self::$db->getTableManager($name, true);
            $table->create($define);
            $table->reset();

            $double = $table->getColumn('double_price');

            self::assertTrue($double->isGeneratedStored());
            self::assertSame('price * 2', $double->getGenerationExpression());
            self::assertFalse($table->getColumn('triple_price')->isGeneratedStored());

            $table->addColumn(new Column('triple_price', 'integer')->virtual('price * 3'));

            self::assertTrue($table->diff($define)->isEmpty());
        } finally {
            static::dropTable($name);
        }
    }
}
