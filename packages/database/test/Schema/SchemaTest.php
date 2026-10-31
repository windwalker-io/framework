<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Schema;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Schema\Ddl\Column;
use Windwalker\Database\Schema\Schema;
use Windwalker\Database\Test\AbstractDatabaseTestCase;

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
