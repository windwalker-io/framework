<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Schema;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Schema\Ddl\Column;
use Windwalker\Database\Schema\Schema;
use Windwalker\Database\Test\AbstractDatabaseTestCase;

use function Windwalker\raw;

/**
 * The SchemaTest class.
 */
class SchemaTest extends AbstractDatabaseTestCase
{
    protected ?Schema $instance;

    /**
     * @see  Schema::getNullDate
     */
    #[Test]
    public function getNullDate(): void
    {
        self::assertEquals(
            $this->instance->getNullDate(),
            self::$db->getNullDate()
        );
    }

    /**
     * @see  Schema::addIndex()
     */
    #[Test]
    public function addIndex(): void
    {
        $this->instance->addIndex('id');
        $this->instance->addIndex(['foo', 'bar']);

        $keys = $this->instance->getIndexes();

        self::assertEquals(['id'], array_keys($keys['idx_ww_flower_id']->getColumns()));
        self::assertEquals(['foo', 'bar'], array_keys($keys['idx_ww_flower_foo_bar']->getColumns()));
    }

    /**
     * @see  Schema::addCheck()
     */
    #[Test]
    public function addCheck(): void
    {
        $this->instance->addCheck('price >= 0', 'ck_price');

        $constraints = $this->instance->getConstraints();

        self::assertTrue($constraints['ck_price']->isCheck());
        self::assertEquals('price >= 0', $constraints['ck_price']->checkClause);
    }

    /**
     * @see  Schema::__call
     */
    #[Test]
    public function magicCall(): void
    {
        $this->instance->primary('id');
        $this->instance->integer('category_id')->length(11);
        $this->instance->double('pos')->length('20,4');
        $this->instance->decimal('price')->length('20,4');
        $this->instance->varchar('intro')->length(512);

        $cols = [];

        foreach ($this->instance->getColumns() as $column) {
            $cols[] = sprintf(
                '%s %s',
                $column->getColumnName(),
                $column->getTypeExpression()
            );
        }

        self::assertStringSafeEquals(
            <<<EOT
            id integer
            category_id integer(11)
            pos double(20,4)
            price decimal(20,4)
            intro varchar(512)
            EOT,
            implode("\n", $cols)
        );
    }

    /**
     * @see  Schema::getDateFormat
     */
    #[Test]
    public function getDateFormat(): void
    {
        self::assertEquals(
            self::$db->getDateFormat(),
            $this->instance->getDateFormat()
        );
    }

    /**
     * @see  Schema::addColumn
     */
    #[Test]
    public function addColumn(): void
    {
        $column = $this->instance->addColumn('new_column', 'varchar');

        self::assertSame($column, $this->instance->columns['new_column']);
        self::assertSame('varchar', $column->getDataType());

        $existingColumn = new Column('existing_column', 'integer');

        self::assertSame(
            $existingColumn,
            $this->instance->addColumn($existingColumn)
        );
        self::assertSame($existingColumn, $this->instance->columns['existing_column']);
    }

    /**
     * @see  Schema::addPrimaryKey()
     */
    #[Test]
    public function addPrimaryKey(): void
    {
        $constraint = $this->instance->addPrimaryKey(['id', 'category_id']);

        self::assertTrue($constraint->isPrimary());
        self::assertSame('pk_ww_flower', $constraint->constraintName);
        self::assertSame(['id', 'category_id'], $constraint->getColumnNames());
        self::assertSame($constraint, $this->instance->constraints['pk_ww_flower']);
    }

    /**
     * @see  Schema::primary
     */
    #[Test]
    public function primary(): void
    {
        $column = $this->instance->primary('id');

        self::assertSame($column, $this->instance->columns['id']);
        self::assertSame('integer', $column->getDataType());
        self::assertTrue($column->isPrimary());
        self::assertTrue($column->isAutoIncrement());
    }

    /**
     * @see  Schema::addUniqueKey
     */
    #[Test]
    public function addUniqueKey(): void
    {
        $constraint = $this->instance->addUniqueKey(['email', 'username']);

        self::assertTrue($constraint->isUnique());
        self::assertSame('idx_ww_flower_email_username', $constraint->constraintName);
        self::assertSame(['email', 'username'], $constraint->getColumnNames());
        self::assertSame(
            $constraint,
            $this->instance->constraints['idx_ww_flower_email_username']
        );
    }

    #[Test]
    public function addColumnInstantiatesColumnClassName(): void
    {
        $column = $this->instance->addColumn(Column::class);

        self::assertInstanceOf(Column::class, $column);
        self::assertSame($column, $this->instance->columns['']);
    }

    #[Test]
    public function addIndexAcceptsIndexObjectAndCustomName(): void
    {
        $this->instance->integer('id');
        $index = $this->instance->addIndex(['id'], 'my_idx', 'a comment');

        self::assertSame('my_idx', $index->indexName);
        self::assertSame('a comment', $index->indexComment);
        self::assertSame($index, $this->instance->indexes['my_idx']);
        self::assertSame($this->instance->columns['id'], $index->getColumns()['id']);

        $ready = new \Windwalker\Database\Schema\Ddl\Index('ready_idx');
        $ready->columns(['foo']);

        self::assertSame($ready, $this->instance->addIndex($ready, 'custom_idx'));
        self::assertSame('custom_idx', $ready->indexName);
        self::assertArrayHasKey('custom_idx', $this->instance->indexes);
    }

    #[Test]
    public function addForeignKeySetsReferences(): void
    {
        $fk = $this->instance->addForeignKey('category_id', 'ww_categories', 'id');

        self::assertSame(['category_id'], $fk->getColumnNames());
        self::assertSame('ww_categories', $fk->referencedTableName);
        self::assertSame(['id'], array_keys($fk->getReferencedColumns()));
    }

    #[Test]
    public function dropListsAccumulate(): void
    {
        $this->instance->dropColumns('a');
        $this->instance->dropColumns('b', 'c');
        $this->instance->dropIndexes('i1');
        $this->instance->dropConstraints('c1', 'c2');

        self::assertSame(['a', 'b', 'c'], $this->instance->dropColumns);
        self::assertSame(['i1'], $this->instance->dropIndexes);
        self::assertSame(['c1', 'c2'], $this->instance->dropConstraints);
    }

    #[Test]
    public function magicCallAppliesTypeSpecificDefaults(): void
    {
        $bigint = $this->instance->primaryBigint('id');
        $uuid = $this->instance->uuidChar('uuid');
        $puuid = $this->instance->primaryUuidChar('puuid');
        $bin = $this->instance->uuidBinary('bin');
        $pbin = $this->instance->primaryUuidBinary('pbin');
        $json = $this->instance->json('params');
        $date = $this->instance->date('d');

        self::assertSame('bigint', $bigint->getDataType());
        self::assertTrue($bigint->isAutoIncrement());
        self::assertTrue($bigint->isPrimary());
        self::assertEquals(36, $uuid->getCharacterMaximumLength());
        self::assertSame('ascii_bin', $uuid->getCollationName());
        self::assertTrue($puuid->isPrimary());
        self::assertEquals(16, $bin->getCharacterMaximumLength());
        self::assertTrue($pbin->isPrimary());
        self::assertTrue($json->getIsNullable());
        self::assertTrue($date->getIsNullable());
    }

    #[Test]
    public function addFunctionalIndex(): void
    {
        $this->instance->varchar('title');

        $index = $this->instance->addFunctionalIndex(['title', raw('LOWER(email)')], 'idx_fx');

        self::assertSame($index, $this->instance->indexes['idx_fx']);
        self::assertSame(['title', 'LOWER(email)'], array_keys($index->getColumns()));
        self::assertSame($this->instance->columns['title'], $index->getColumns()['title']);
        self::assertSame('LOWER(email)', $index->getColumns()['LOWER(email)']->getIndexExpression());
    }

    #[Before]
    protected function createSchemaFromTable(): void
    {
        $this->instance = static::$db->getTableManager('ww_flower')->createSchemaObject();
    }

    /**
     * @inheritDoc
     */
    protected static function setupDatabase(): void
    {
        self::importFromFile(__DIR__ . '/../stub/metadata/' . static::platformName() . '.sql');
    }
}
