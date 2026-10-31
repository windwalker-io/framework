<?php

declare(strict_types=1);

namespace Windwalker\Query\Test\Concern;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Traversable;
use Windwalker\Data\Collection;
use Windwalker\Query\Data\CursorPaginate;
use Windwalker\Query\Grammar\BaseGrammar;
use Windwalker\Query\Query;
use Windwalker\Query\Test\Mock\MockEscaper;
use Windwalker\Utilities\Iterator\PaginateIterator;

class IterateConcernTraitTest extends TestCase
{
    /**
     * @var  list<array{offset: ?int, limit: ?int, sql: string}>
     */
    private array $calls = [];

    private MockEscaper $escaper;

    /**
     * Backing "table" shared with cloned queries.
     */
    private array $dbRows = [];

    #[Before]
    public function resetCalls(): void
    {
        $this->calls = [];
        $this->escaper = new MockEscaper();
    }

    /**
     * Create a query whose "DB" is the given rows, applying offset and limit like a real table.
     *
     * @param  list<array<string, int>>  $rows
     */
    private function createQuery(array $rows, bool $honorOffset = true): Query
    {
        $test = $this;

        $query = new class ($this->escaper, new BaseGrammar()) extends Query {
            public ?\Closure $source = null;

            public bool $honorOffset = true;

            public ?\Closure $recorder = null;

            public function all(?string $class = null, array $args = []): Collection
            {
                return new Collection($this->fetchRows());
            }

            public function getIterator(
                ?string $class = null,
                array $args = [],
                \Windwalker\Query\Data\CursorPaginate|int|null $paginate = null
            ): Traversable {
                if ($paginate !== null) {
                    return $this->getPaginatedIterator($class, $paginate, $args);
                }

                return new \ArrayIterator($this->fetchRows());
            }

            private function fetchRows(): array
            {
                ($this->recorder)($this);

                $rows = ($this->source)();


                $offset = $this->honorOffset ? ($this->getOffset() ?? 0) : 0;

                return array_slice($rows, $offset, $this->getLimit());
            }
        };

        $this->dbRows = $rows;
        $query->source = static fn() => $test->dbRows;
        $query->honorOffset = $honorOffset;
        $query->recorder = function (Query $q) use ($test) {
            $test->calls[] = ['offset' => $q->getOffset(), 'limit' => $q->getLimit()];
        };

        return $query;
    }

    private static function rows(int $count): array
    {
        return array_map(static fn(int $i) => ['id' => $i], range(1, $count));
    }

    private static function ids(iterable $items): array
    {
        $ids = [];

        foreach ($items as $item) {
            $ids[] = $item['id'];
        }

        return $ids;
    }

    #[Test]
    public function iterateBatchedYieldsAllItemsAcrossPages(): void
    {
        $query = $this->createQuery(self::rows(5));

        self::assertSame([1, 2, 3, 4, 5], self::ids($query->iterateBatched(2)));
        self::assertSame([0, 2, 4], array_column($this->calls, 'offset'));
    }

    #[Test]
    public function iterateBatchedStopsWhenLastPageIsExactlyFull(): void
    {
        $query = $this->createQuery(self::rows(4));

        self::assertSame([1, 2, 3, 4], self::ids($query->iterateBatched(2)));
        self::assertCount(3, $this->calls);
    }

    #[Test]
    public function iterateBatchedRespectsQueryLimitAndOffset(): void
    {
        $query = $this->createQuery(self::rows(10))->offset(1)->limit(5);

        self::assertSame([2, 3, 4, 5, 6], self::ids($query->iterateBatched(2)));
        self::assertSame([2, 2, 1], array_column($this->calls, 'limit'));
    }

    private function idCursor(int $length): CursorPaginate
    {
        return Query::cursorPaginate(
            $length,
            static fn(Query $query, array $item) => $query->offset($item['id'])
        );
    }

    #[Test]
    public function stringCursorAddsGreaterThanConditionFromLastItem(): void
    {
        $query = $this->createQuery(self::rows(5))->select('*')->from('a');
        $handler = Query::cursorPaginate(2, 'id')->cursorHandler;

        $result = $handler($query, ['id' => 2]);

        self::assertStringContainsString('"id" >', (string) $result);
        self::assertSame(2, $result->getBounded()[0]['value']->getValue());
    }

    #[Test]
    public function iterateBatchedUsesCursorHandler(): void
    {
        $query = $this->createQuery(self::rows(5));

        $ids = self::ids($query->iterateBatched($this->idCursor(2)));

        self::assertSame([1, 2, 3, 4, 5], $ids);
        // The cursor handler overrides the offset after the first request
        self::assertSame([0, 2, 4], array_column($this->calls, 'offset'));
    }

    #[Test]
    public function iterateChunksYieldsCollectionPerPage(): void
    {
        $query = $this->createQuery(self::rows(5));

        $chunks = [];

        foreach ($query->iterateChunks(2) as $chunk) {
            $chunks[] = self::ids($chunk);
        }

        self::assertSame([[1, 2], [3, 4], [5]], $chunks);
    }

    #[Test]
    public function iterateChunksTrimsLastChunkByQueryLimit(): void
    {
        $query = $this->createQuery(self::rows(10))->limit(3);

        $chunks = [];

        foreach ($query->iterateChunks(2) as $chunk) {
            $chunks[] = self::ids($chunk);
        }

        self::assertSame([[1, 2], [3]], $chunks);
    }

    #[Test]
    public function iterateChunksWithCursorYieldsGeneratorPerPage(): void
    {
        $query = $this->createQuery(self::rows(5));

        $chunks = [];

        foreach ($query->iterateChunks($this->idCursor(2)) as $chunk) {
            self::assertInstanceOf(\Generator::class, $chunk);
            $chunks[] = self::ids($chunk);
        }

        self::assertSame([[1, 2], [3, 4], [5]], $chunks);
    }

    #[Test]
    public function iterateChunksReturnsNothingForEmptyResult(): void
    {
        $query = $this->createQuery([]);

        self::assertSame([], iterator_to_array($query->iterateChunks(2)));
    }

    #[Test]
    public function iterateWhileRestartsFromZeroUntilSourceIsConsumed(): void
    {
        $query = $this->createQuery(self::rows(5));
        $seen = [];

        foreach ($query->iterateWhile(2) as $item) {
            $seen[] = $item['id'];
            // Consume the item so the next query will not return it again
            $this->dbRows = array_values(array_filter($this->dbRows, static fn($r) => $r['id'] !== $item['id']));
        }

        self::assertSame([1, 2, 3, 4, 5], $seen);
        self::assertSame([0, 0, 0], array_column($this->calls, 'offset'));
    }

    #[Test]
    public function iterateWhileStopsAtQueryLimit(): void
    {
        $query = $this->createQuery(self::rows(10), false)->limit(3);

        self::assertSame([1, 2, 1], self::ids($query->iterateWhile(2)));
    }

    #[Test]
    public function iterateBatchWhileStopsAtQueryLimit(): void
    {
        $query = $this->createQuery(self::rows(10), false)->limit(3);

        self::assertSame([1, 2, 1], self::ids($query->iterateBatchWhile(2)));
    }

    #[Test]
    public function iterateBatchWhileStopsOnShortPage(): void
    {
        $query = $this->createQuery(self::rows(3), false);

        self::assertSame([1, 2, 3], self::ids($query->iterateBatchWhile(5)));
        self::assertCount(1, $this->calls);
    }

    #[Test]
    public function getPaginatedIteratorReturnsPaginateIteratorYieldingAllItems(): void
    {
        $query = $this->createQuery(self::rows(5));

        $iterator = $query->getPaginatedIterator(null, 2);

        self::assertInstanceOf(PaginateIterator::class, $iterator);
        self::assertSame([1, 2, 3, 4, 5], self::ids($iterator));
    }

    #[Test]
    public function getPaginatedIteratorRespectsQueryLimit(): void
    {
        $query = $this->createQuery(self::rows(10))->limit(3);

        self::assertSame([1, 2, 3], self::ids($query->getPaginatedIterator(null, 2)));
    }

    #[Test]
    public function getPaginatedIteratorWorksWithCursor(): void
    {
        $query = $this->createQuery(self::rows(5));

        self::assertSame(
            [1, 2, 3, 4, 5],
            self::ids($query->getPaginatedIterator(null, $this->idCursor(2)))
        );
    }

    #[Test]
    public function getIteratorDelegatesToPaginatedIteratorWhenPaginateGiven(): void
    {
        $query = $this->createQuery(self::rows(3));

        self::assertSame([1, 2, 3], self::ids($query->getIterator(null, [], 2)));
    }
}
