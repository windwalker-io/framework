<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\MySQL;

use Windwalker\Database\Test\Manager\AbstractTableManagerExtraTestCase;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Platform\MySQLPlatform;
use Windwalker\Database\Schema\Ddl\Index;

class MySQLTableManagerExtraTest extends AbstractTableManagerExtraTestCase
{
    protected static function dropTable(string $table): void
    {
        self::$baseConn->exec("DROP TABLE IF EXISTS $table");
    }

    /**
     * MySQL 8.0.13+ (non-MariaDB) reads back the real expression text from
     * `INFORMATION_SCHEMA.STATISTICS.EXPRESSION` instead of a placeholder like `(expression 1)`.
     * MySQL normalizes the stored expression (lower-cased function name, backtick-quoted columns),
     * so only substance is asserted rather than an exact string match.
     */
    #[Test]
    public function functionalIndexExposesRealExpressionText(): void
    {
        $platform = self::$db->getPlatform();

        if (!$platform instanceof MySQLPlatform || !$platform->supportsIndexExpression()) {
            self::markTestSkipped('Requires MySQL 8.0.13+ (non-MariaDB).');
        }

        $this->table->addIndex(new Index('idx_ww_lower_title')->expression('LOWER(title)'));
        $this->table->reset();

        $index = $this->table->getIndex('idx_ww_lower_title');
        $columnNames = $index->getColumnNames();

        self::assertCount(1, $columnNames);
        self::assertDoesNotMatchRegularExpression('/^\(expression \d+\)$/', $columnNames[0]);
        self::assertMatchesRegularExpression('/title/i', $columnNames[0]);
    }
}
