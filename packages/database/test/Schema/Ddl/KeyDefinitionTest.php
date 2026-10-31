<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Schema\Ddl;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Database\Schema\Ddl\Column;
use Windwalker\Database\Schema\Ddl\Constraint;
use Windwalker\Database\Schema\Ddl\Index;

class KeyDefinitionTest extends TestCase
{
    #[Test]
    public function referencedColumnsNormalizesNamesAndClonesColumns(): void
    {
        $existing = new Column('b');
        $constraint = new Constraint(Constraint::TYPE_FOREIGN_KEY, 'fk');

        $constraint->referencedColumns(['a', $existing]);

        $refs = $constraint->getReferencedColumns();

        self::assertSame(['a', 'b'], array_keys($refs));
        self::assertNotSame($existing, $refs['b']);
    }

    #[Test]
    public function referencedToSetsTableAndColumns(): void
    {
        $constraint = new Constraint(Constraint::TYPE_FOREIGN_KEY, 'fk');

        self::assertSame($constraint, $constraint->referencedTo('categories', 'id'));
        self::assertSame('categories', $constraint->referencedTableName);
        self::assertSame(['id'], array_keys($constraint->getReferencedColumns()));
    }

    #[Test]
    public function constraintTypePredicatesAndColumns(): void
    {
        $fk = new Constraint(Constraint::TYPE_FOREIGN_KEY, 'fk');
        $check = new Constraint(Constraint::TYPE_CHECK, 'ck');
        $fk->columns(['x', 'y']);

        self::assertTrue($fk->isForeignKey());
        self::assertFalse($fk->isCheck());
        self::assertTrue($check->isCheck());
        self::assertFalse($check->isForeignKey());
        self::assertSame(2, $fk->columnsCount());
        self::assertSame('x', $fk->getFirstColumn()->getColumnName());
    }

    #[Test]
    public function indexColumnHelpers(): void
    {
        $index = new Index('idx');
        $index->columns(['a', 'b']);

        self::assertSame(2, $index->columnsCount());
        self::assertSame('a', $index->getFirstColumn()->getColumnName());
        self::assertSame(['a', 'b'], $index->getColumnNames());
    }

    #[Test]
    public function wrapReturnsSameInstanceOrFillsFromArray(): void
    {
        $column = new Column('a');

        self::assertSame($column, Column::wrap($column));
        self::assertSame('b', Column::wrap(['columnName' => 'b'])->getColumnName());
    }
}
