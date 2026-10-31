<?php

declare(strict_types=1);

namespace Windwalker\Html\Test\Grid;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Html\Grid\KeyValueGrid;

/**
 * The KeyValueGridTest class.
 */
class KeyValueGridTest extends TestCase
{
    #[Test]
    public function constructorSetsKeyValueColumns(): void
    {
        $grid = KeyValueGrid::create(['id' => 'kv']);

        self::assertSame(['key', 'value'], $grid->getColumns());
        self::assertSame(['id' => 'kv'], $grid->getTableAttributes());
    }

    #[Test]
    public function addHeaderRendersHeadRowWithTitles(): void
    {
        $grid = (new KeyValueGrid())->addHeader('K', 'V', ['key' => ['width' => '5'], 'row' => ['class' => 'h']]);

        self::assertSame(
            "<table><thead>\n\t<tr class=\"h\">\n\t\t<th width=\"5\">K</th>\n\t\t<th>V</th>\n\t</tr>\n</thead></table>",
            (string) $grid
        );
    }

    #[Test]
    public function addHeaderUsesDefaultTitles(): void
    {
        $grid = (new KeyValueGrid())->addHeader();

        self::assertStringContainsString('<th>Key</th>', (string) $grid);
        self::assertStringContainsString('<th>Value</th>', (string) $grid);
    }

    #[Test]
    public function addItemRendersKeyAndValueWithAttributes(): void
    {
        $grid = (new KeyValueGrid())->addItem('foo', 'bar', ['row' => ['id' => 'r'], 'value' => ['class' => 'v']]);

        self::assertSame(
            "<table><tbody>\n\t<tr id=\"r\">\n\t\t<td>foo</td>\n\t\t<td class=\"v\">bar</td>\n\t</tr>\n</tbody></table>",
            (string) $grid
        );
    }

    #[Test]
    public function addItemConvertsArrayAndObjectValuesToPrintedString(): void
    {
        $grid = (new KeyValueGrid())->addItem('arr', ['a' => 1]);

        self::assertSame(print_r(['a' => 1], true), $grid->getRow()['value']->content);

        $object = (object) ['x' => 1];
        $grid->addItem('obj', $object);

        self::assertSame(print_r($object, true), $grid->getRow()['value']->content);
    }

    #[Test]
    public function addItemSkipsValueCellWhenValueIsFalse(): void
    {
        $grid = (new KeyValueGrid())->addItem('foo', false);

        self::assertArrayNotHasKey('value', $grid->getRow());
        self::assertSame('foo', $grid->getRow()['key']->content);
    }

    #[Test]
    public function addTitleSpansKeyCellAcrossTwoColumns(): void
    {
        $grid = (new KeyValueGrid())->addTitle('Section');

        self::assertSame(['colspan' => 2], $grid->getRow()['key']->attribs);
        self::assertArrayNotHasKey('value', $grid->getRow());
    }

    #[Test]
    public function addItemsAddsEveryItemFromArray(): void
    {
        $grid = (new KeyValueGrid())->addItems(['a' => '1', 'b' => '2']);

        self::assertCount(2, $grid->getRows());
        self::assertSame('b', $grid->getRow()['key']->content);
        self::assertSame('2', $grid->getRow()['value']->content);
    }

    #[Test]
    public function configurePassesGridKeyAndValueToHandler(): void
    {
        $grid = new KeyValueGrid();
        $calls = [];

        $result = $grid->configure(
            ['a' => 1, 'b' => 2],
            function (KeyValueGrid $g, $key, $value) use (&$calls, $grid) {
                self::assertSame($grid, $g);
                $calls[$key] = $value;
            }
        );

        self::assertSame($grid, $result);
        self::assertSame(['a' => 1, 'b' => 2], $calls);
    }
}
