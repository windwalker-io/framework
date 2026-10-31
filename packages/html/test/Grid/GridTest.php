<?php

declare(strict_types=1);

namespace Windwalker\Html\Test\Grid;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Html\Grid\Grid;

/**
 * The GridTest class.
 */
class GridTest extends TestCase
{
    protected Grid $instance;

    #[Before]
    public function createGrid(): void
    {
        $this->instance = new Grid(['class' => 'table']);
    }

    #[Test]
    public function createReturnsGridWithAttributes(): void
    {
        $grid = Grid::create(['id' => 'g']);

        self::assertSame(['id' => 'g'], $grid->getTableAttributes());
    }

    #[Test]
    public function setTableAttributesMergesUnlessReplacing(): void
    {
        $this->instance->setTableAttributes(['id' => 'a']);

        self::assertSame(['class' => 'table', 'id' => 'a'], $this->instance->getTableAttributes());

        $this->instance->setTableAttributes(['id' => 'b'], true);

        self::assertSame(['id' => 'b'], $this->instance->getTableAttributes());
    }

    #[Test]
    public function columnsCanBeAddedDeletedAndReplaced(): void
    {
        $this->instance->addColumn('a')->addColumn('b')->addColumn('c');

        self::assertSame(['a', 'b', 'c'], $this->instance->getColumns());

        $this->instance->deleteColumn('b')->deleteColumn('missing');

        self::assertSame(['a', 'c'], $this->instance->getColumns());

        $this->instance->setColumns(['x' => 'z', 'y' => 'w']);

        self::assertSame(['z', 'w'], $this->instance->getColumns());
    }

    #[Test]
    public function addRowSetsActiveRowAndAttributes(): void
    {
        $this->instance->addRow(['class' => 'r1'])->addRow(['class' => 'r2']);

        self::assertSame(1, $this->instance->getActiveRow());
        self::assertSame(['class' => 'r2'], $this->instance->getRowAttributes());

        $this->instance->setActiveRow(0);

        self::assertSame(['class' => 'r1'], $this->instance->getRowAttributes());

        $this->instance->setRowAttributes(['id' => 'x']);

        self::assertSame(['id' => 'x'], $this->instance->getRowAttributes());
    }

    #[Test]
    public function getRowsSeparatesHeaderFooterAndNormalRows(): void
    {
        $this->instance->addHeaderRow()->addRow()->addFooterRow()->addRow();

        self::assertSame([0], $this->instance->getRows(Grid::ROW_HEAD));
        self::assertSame([2], $this->instance->getRows(Grid::ROW_FOOT));
        self::assertSame([1, 3], array_values($this->instance->getRows()));
    }

    #[Test]
    public function getRowReturnsActiveRowByDefaultOrNullIfMissing(): void
    {
        $this->instance->addRow(['id' => 'a'])->setRowCell('col', 'v');

        self::assertSame('v', $this->instance->getRow()['col']->content);
        self::assertSame(['id' => 'a'], $this->instance->getRow(0)['_row']);
        self::assertNull($this->instance->getRow(99));
    }

    #[Test]
    public function setRowCellAppendsWhenNotReplacing(): void
    {
        $this->instance->addRow()
            ->setRowCell('col', 'foo', ['a' => '1'])
            ->setRowCell('col', 'bar', ['b' => '2'], false);

        $cell = $this->instance->getRow()['col'];

        self::assertSame('foobar', $cell->content);
        self::assertSame(['b' => '2'], $cell->attribs);

        $this->instance->setRowCell('col', 'new');

        self::assertSame('new', $this->instance->getRow()['col']->content);
    }

    #[Test]
    public function setRowCellCreatesCellWhenNotReplacingAndMissing(): void
    {
        $this->instance->addRow()->setRowCell('col', 'foo', [], false);

        self::assertSame('foo', $this->instance->getRow()['col']->content);
    }

    #[Test]
    public function setDirectlyRowCellWritesToGivenRow(): void
    {
        $this->instance->addRow()->addRow();
        $this->instance->setDirectlyRowCell(0, 'col', 'zero');

        self::assertSame('zero', $this->instance->getRow(0)['col']->content);
        self::assertArrayNotHasKey('col', $this->instance->getRow(1));
    }

    #[Test]
    public function deleteRowRemovesRowAndSpecialRegistrations(): void
    {
        $this->instance->addHeaderRow()->addFooterRow()->addRow();

        $this->instance->deleteRow(0)->deleteRow(1);

        self::assertSame([], $this->instance->getRows(Grid::ROW_HEAD));
        self::assertSame([], $this->instance->getRows(Grid::ROW_FOOT));
        self::assertNull($this->instance->getRow(0));
        self::assertSame(2, $this->instance->getActiveRow());
    }

    #[Test]
    public function deleteRowMovesActiveRowToLastWhenActiveDeleted(): void
    {
        $this->instance->addRow()->addRow()->addRow();

        $this->instance->deleteRow(2);

        self::assertSame(1, $this->instance->getActiveRow());
    }

    #[Test]
    public function toStringRendersEmptyTable(): void
    {
        self::assertSame('<table class="table"></table>', $this->instance->toString());
    }

    #[Test]
    public function toStringRendersTableWithoutAttributes(): void
    {
        self::assertSame('<table></table>', (string) new Grid());
    }

    #[Test]
    public function toStringRendersHeaderBodyAndFooter(): void
    {
        $this->instance->setColumns(['a', 'b']);

        $this->instance->addHeaderRow(['class' => 'h'])
            ->setRowCell('a', 'A', ['width' => '10'])
            ->setRowCell('b', 'B');
        $this->instance->addRow()
            ->setRowCell('a', '1')
            ->setRowCell('missing', 'ignored');
        $this->instance->addFooterRow()
            ->setRowCell('b', 'F');

        $expected = '<table class="table">'
            . "<thead>\n\t<tr class=\"h\">\n\t\t<th width=\"10\">A</th>\n\t\t<th>B</th>\n\t</tr>\n</thead>"
            . "<tbody>\n\t<tr>\n\t\t<td>1</td>\n\t</tr>\n</tbody>"
            . "<tfoot>\n\t<tr>\n\t\t<td>F</td>\n\t</tr>\n</tfoot>"
            . '</table>';

        self::assertSame($expected, (string) $this->instance);
    }
}
