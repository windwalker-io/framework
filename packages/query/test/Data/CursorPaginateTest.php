<?php

declare(strict_types=1);

namespace Windwalker\Query\Test\Data;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Query\Data\CursorPaginate;
use Windwalker\Query\Grammar\BaseGrammar;
use Windwalker\Query\Query;
use Windwalker\Query\Test\Mock\MockEscaper;

class CursorPaginateTest extends TestCase
{
    private function query(): Query
    {
        return new Query(new MockEscaper(), new BaseGrammar())->select('*')->from('a');
    }

    private function sql(Query $query): string
    {
        return preg_replace('/:wqp__\d+/', '?', (string) $query);
    }

    private function values(Query $query): array
    {
        return array_map(static fn($b) => $b['value']->getValue(), array_values($query->getBounded()));
    }

    #[Test]
    public function stringIsAscending(): void
    {
        $handler = Query::cursorPaginate(10, 'id')->cursorHandler;
        $query = $handler($this->query(), ['id' => 5]);

        self::assertSame(
            'SELECT * FROM "a" WHERE "id" > ? ORDER BY "id" ASC',
            $this->sql($query)
        );
        self::assertSame([5], $this->values($query));
    }

    #[Test]
    public function singleColumnDescending(): void
    {
        $handler = Query::cursorPaginate(10, ['id' => 'desc'])->cursorHandler;
        $query = $handler($this->query(), ['id' => 5]);

        self::assertSame(
            'SELECT * FROM "a" WHERE "id" < ? ORDER BY "id" DESC',
            $this->sql($query)
        );
    }

    #[Test]
    public function acceptsSortDirectionEnum(): void
    {
        $handler = Query::cursorPaginate(
            10,
            ['created' => \SortDirection::Descending, 'id' => \SortDirection::Ascending]
        )->cursorHandler;
        $query = $handler($this->query(), ['created' => 3, 'id' => 7]);

        self::assertSame(
            'SELECT * FROM "a" WHERE (("created" < ?) OR ("created" = ? AND "id" > ?))'
            . ' ORDER BY "created" DESC, "id" ASC',
            $this->sql($query)
        );
        self::assertSame([3, 3, 7], $this->values($query));
    }

    #[Test]
    public function multipleColumnsMixedDirections(): void
    {
        $handler = Query::cursorPaginate(10, ['a' => 'ASC', 'b' => 'DESC', 'c' => 'ASC'])->cursorHandler;
        $query = $handler($this->query(), (object) ['a' => 1, 'b' => 2, 'c' => 3]);

        self::assertSame(
            'SELECT * FROM "a" WHERE (("a" > ?) OR ("a" = ? AND "b" < ?) OR ("a" = ? AND "b" = ? AND "c" > ?))'
            . ' ORDER BY "a" ASC, "b" DESC, "c" ASC',
            $this->sql($query)
        );
        self::assertSame([1, 1, 2, 1, 2, 3], $this->values($query));
    }

    #[Test]
    public function listOfColumnsIsAscending(): void
    {
        $handler = Query::cursorPaginate(10, ['a', 'b'])->cursorHandler;
        $query = $handler($this->query(), ['a' => 1, 'b' => 2]);

        self::assertStringContainsString('ORDER BY "a" ASC, "b" ASC', $this->sql($query));
    }

    #[Test]
    public function replacesExistingOrderAndKeepsExistingWhere(): void
    {
        $handler = Query::cursorPaginate(10, ['id' => 'DESC'])->cursorHandler;
        $query = $handler($this->query()->where('x', '=', 1)->order('zz'), ['id' => 5]);

        self::assertSame(
            'SELECT * FROM "a" WHERE "x" = ? AND "id" < ? ORDER BY "id" DESC',
            $this->sql($query)
        );
    }

    #[Test]
    public function aliasPrefixIsStrippedWhenReadingItem(): void
    {
        $handler = Query::cursorPaginate(10, ['a.id' => 'ASC'])->cursorHandler;
        $query = $handler($this->query(), ['id' => 9]);

        self::assertSame([9], $this->values($query));
        self::assertStringContainsString('"a"."id" > ?', $this->sql($query));
    }

    #[Test]
    public function closureIsUnchanged(): void
    {
        $closure = static fn(Query $query, array $item) => $query->offset($item['id']);

        self::assertSame($closure, Query::cursorPaginate(10, $closure)->cursorHandler);
    }

    #[Test]
    public function invalidDirectionThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Query::cursorPaginate(10, ['id' => 'up'])->cursorHandler;
    }

    #[Test]
    public function emptyColumnsThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Query::cursorPaginate(10, [])->cursorHandler;
    }

    #[Test]
    public function withCursorFirstPageAppliesLimitAndOrder(): void
    {
        $paginate = Query::cursorPaginate(20, ['created' => 'DESC', 'id' => 'DESC']);

        $query = $this->query()->where('state', 1)->limit(5)->withCursor($paginate);

        self::assertSame(
            'SELECT * FROM "a" WHERE "state" = ? ORDER BY "created" DESC, "id" DESC LIMIT 20',
            $this->sql($query)
        );
    }

    #[Test]
    public function withCursorNextPageAppliesLimitAndKeysetCondition(): void
    {
        $paginate = Query::cursorPaginate(20, ['created' => 'DESC', 'id' => 'DESC']);

        $query = $this->query()->where('state', 1)
            ->withCursor($paginate, ['created' => '2026-01-01', 'id' => 5]);

        self::assertSame(
            'SELECT * FROM "a" WHERE "state" = ? AND (("created" < ?) OR ("created" = ? AND "id" < ?)) '
            . 'ORDER BY "created" DESC, "id" DESC LIMIT 20',
            $this->sql($query)
        );
        self::assertSame([1, '2026-01-01', '2026-01-01', 5], $this->values($query));
    }

    #[Test]
    public function withCursorAcceptsObjectItem(): void
    {
        $paginate = Query::cursorPaginate(10, 'id');

        $query = $this->query()->withCursor($paginate, (object) ['id' => 7]);

        self::assertSame('SELECT * FROM "a" WHERE "id" > ? ORDER BY "id" ASC LIMIT 10', $this->sql($query));
    }

    #[Test]
    public function withCursorWithNullItemIsFirstPage(): void
    {
        $paginate = Query::cursorPaginate(10, 'id');

        self::assertSame(
            'SELECT * FROM "a" ORDER BY "id" ASC LIMIT 10',
            $this->sql($this->query()->withCursor($paginate, null))
        );
    }

    #[Test]
    public function withCursorClosureFirstPageAppliesLimitOnly(): void
    {
        $paginate = Query::cursorPaginate(10, static fn(Query $q, array $item) => $q);

        self::assertSame('SELECT * FROM "a" LIMIT 10', $this->sql($this->query()->withCursor($paginate)));
    }

    #[Test]
    public function withCursorRejectsInvalidPageArguments(): void
    {
        $paginate = Query::cursorPaginate(10, 'id');

        $this->expectException(\InvalidArgumentException::class);

        $this->query()->withCursor($paginate, 5);
    }

    #[Test]
    public function missingItemColumnThrows(): void
    {
        $handler = Query::cursorPaginate(10, ['id'])->cursorHandler;

        $this->expectException(\InvalidArgumentException::class);

        $handler($this->query(), ['other' => 1]);
    }

    #[Test]
    public function conditionsFromReadsArrayAndObjectByColumnName(): void
    {
        $paginate = Query::cursorPaginate(10, ['a.created' => 'DESC', 'id' => \SortDirection::Ascending]);

        self::assertSame(
            [['a.created', 'DESC', '2026-01-01'], ['id', 'ASC', 5]],
            $paginate->conditionsFrom(['created' => '2026-01-01', 'id' => 5])
        );
        self::assertSame(
            [['a.created', 'DESC', '2026-01-01'], ['id', 'ASC', 5]],
            $paginate->conditionsFrom((object) ['created' => '2026-01-01', 'id' => 5])
        );
    }

    #[Test]
    public function conditionsFromRejectsClosureCursor(): void
    {
        $paginate = Query::cursorPaginate(10, static fn(Query $q, array $item) => $q);

        $this->expectException(\InvalidArgumentException::class);

        $paginate->conditionsFrom(['id' => 1]);
    }

    #[Test]
    public function conditionsFromThrowsOnMissingColumn(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Query::cursorPaginate(10, ['id'])->conditionsFrom(['other' => 1]);
    }

    #[Test]
    public function applyToKeepsExistingWhereAndReplacesOrder(): void
    {
        $query = $this->query()->where('x', 1)->order('x', 'ASC');

        CursorPaginate::applyTo($query, [['id', \SortDirection::Descending, 5]]);

        self::assertSame('SELECT * FROM "a" WHERE "x" = ? AND "id" < ? ORDER BY "id" DESC', $this->sql($query));
    }

    #[Test]
    public function applyToRejectsInvalidConditions(): void
    {
        $query = $this->query();

        foreach (
            [
                [],
                [['id', 'ASC']],
                [['', 'ASC', 1]],
                [['id', 'ASC', null]],
            ] as $conditions
        ) {
            try {
                CursorPaginate::applyTo($query, $conditions);
                self::fail('Expected InvalidArgumentException.');
            } catch (\InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }
}
