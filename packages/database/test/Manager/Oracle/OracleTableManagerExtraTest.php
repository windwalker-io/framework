<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\Oracle;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Manager\AbstractTableManagerExtraTestCase;
use Windwalker\Database\Schema\Ddl\Constraint;

class OracleTableManagerExtraTest extends AbstractTableManagerExtraTestCase
{
    protected static DbPlatform $platform = DbPlatform::Oracle;

    protected static string $driver = 'pdo_oci';

    protected static bool $supportsFunctionalIndex = true;

    protected static string $storedKeyword = 'VIRTUAL';

    protected static string $collation = 'BINARY';

    protected static function dropTable(string $table): void
    {
        self::$baseConn->exec(
            "BEGIN EXECUTE IMMEDIATE 'DROP TABLE $table CASCADE CONSTRAINTS PURGE'; "
            . 'EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;'
        );
    }

    #[Test]
    public function createAndModifyColumnWithCollation(): void
    {
        self::markTestSkipped('Column collation requires MAX_STRING_SIZE=EXTENDED.');
    }

    #[Test]
    public function addIndexAndConstraintAreIdempotent(): void
    {
        $this->table->addIndex('title');
        $this->table->reset();
        $this->table->addIndex('title');

        // Oracle rejects a second unique key on the same column set, so use another column.
        $this->table->addConstraint('title', Constraint::TYPE_UNIQUE);
        $this->table->reset();
        $this->table->addConstraint('title', Constraint::TYPE_UNIQUE);

        self::assertTrue($this->table->hasIndex('idx_ww_tm_extra_title'));
    }
}
