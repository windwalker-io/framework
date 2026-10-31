<?php

declare(strict_types=1);

namespace Windwalker\Query\Test\Clause;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Query\Clause\JoinClause;
use Windwalker\Query\Grammar\BaseGrammar;
use Windwalker\Query\Query;
use Windwalker\Query\Test\Mock\MockEscaper;
use Windwalker\Query\Test\Stub\SortEnumStub;
use Windwalker\Test\Traits\QueryTestTrait;

use function Windwalker\raw;
use function Windwalker\Query\val;

class JoinClauseTest extends TestCase
{
    use QueryTestTrait;

    private static ?MockEscaper $escaper = null;

    private static function createQuery(): Query
    {
        return (new Query(self::$escaper ??= new MockEscaper(), new BaseGrammar()))
            ->select('*')
            ->from('a');
    }

    private static function join(\Closure $callback): Query
    {
        return self::createQuery()->join('LEFT', 'b', 'b', $callback);
    }

    #[Test]
    public function onAcceptsArrayOfConditions(): void
    {
        $query = self::join(fn(JoinClause $j) => $j->on([['a.id', '=', 'b.aid'], ['a.x', 'b.x']]));

        self::assertSqlEquals(
            'SELECT * FROM "a" LEFT JOIN "b" AS "b" ON "a"."id" = "b"."aid" AND "a"."x" = "b"."x"',
            $query
        );
    }

    #[Test]
    public function onAcceptsNestedClosureWithOrGlue(): void
    {
        $query = self::join(
            fn(JoinClause $j) => $j->on(fn(JoinClause $j2) => $j2->on('a.x', 'b.x')->on('a.y', '>', 'b.y'), 'OR')
        );

        self::assertSqlEquals(
            'SELECT * FROM "a" LEFT JOIN "b" AS "b" ON ("a"."x" = "b"."x" OR "a"."y" > "b"."y")',
            $query
        );
    }

    #[Test]
    public function onRejectsInvalidNestedGlue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        self::join(fn(JoinClause $j) => $j->on(fn(JoinClause $j2) => $j2->on('a.x', 'b.x'), 'XOR'));
    }

    #[Test]
    public function emptyNestedClosureAddsNothing(): void
    {
        $query = self::join(fn(JoinClause $j) => $j->on('a.id', 'b.aid')->on(static fn() => null));

        self::assertSqlEquals('SELECT * FROM "a" LEFT JOIN "b" AS "b" ON "a"."id" = "b"."aid"', $query);
    }

    #[Test]
    public function orOnWrapsArrayConditionsInOrGroup(): void
    {
        $query = self::join(fn(JoinClause $j) => $j->on('a.id', 'b.aid')->orOn([['a.x', 'b.x'], ['a.y', 'b.y']]));

        self::assertSqlEquals(
            'SELECT * FROM "a" LEFT JOIN "b" AS "b" ON "a"."id" = "b"."aid" '
            . 'AND ("a"."x" = "b"."x" OR "a"."y" = "b"."y")',
            $query
        );
    }

    #[Test]
    public function nullValueConvertsOperatorToIsNull(): void
    {
        $query = self::join(fn(JoinClause $j) => $j->on('a.id', '=', null)->on('b.s', '!=', null));

        self::assertSqlEquals(
            'SELECT * FROM "a" LEFT JOIN "b" AS "b" ON "a"."id" IS NULL AND "b"."s" IS NOT NULL',
            $query
        );
    }

    #[Test]
    public function arrayValueConvertsOperatorToIn(): void
    {
        $query = self::join(fn(JoinClause $j) => $j->on('a.id', [1, 2])->on('a.id', '!=', [3]));

        self::assertSqlEquals(
            'SELECT * FROM "a" LEFT JOIN "b" AS "b" ON "a"."id" IN (1, 2) AND "a"."id" NOT IN (3)',
            $query
        );
    }

    #[Test]
    public function closureValueBuildsSubQuery(): void
    {
        $query = self::join(
            fn(JoinClause $j) => $j->on('a.id', '>', fn(Query $sub) => $sub->select('x')->from('c'))
        );

        self::assertSqlEquals(
            'SELECT * FROM "a" LEFT JOIN "b" AS "b" ON "a"."id" > (SELECT "x" FROM "c")',
            $query
        );
    }

    #[Test]
    public function rawValueIsNotQuoted(): void
    {
        $query = self::join(fn(JoinClause $j) => $j->on('a.id', '=', raw('NOW()')));

        self::assertSqlEquals('SELECT * FROM "a" LEFT JOIN "b" AS "b" ON "a"."id" = NOW()', $query);
    }

    #[Test]
    public function valueClauseIsBoundAsParameter(): void
    {
        $query = self::join(fn(JoinClause $j) => $j->on('a.id', '=', val(5)));

        self::assertSqlEquals('SELECT * FROM "a" LEFT JOIN "b" AS "b" ON "a"."id" = 5', $query);
        self::assertCount(1, $query->getBounded());
    }

    #[Test]
    public function backedEnumValueIsBoundAsItsValue(): void
    {
        $query = self::join(fn(JoinClause $j) => $j->on('a.id', '=', SortEnumStub::Asc));

        self::assertSqlEquals("SELECT * FROM \"a\" LEFT JOIN \"b\" AS \"b\" ON \"a\".\"id\" = 'ASC'", $query);
    }

    #[Test]
    public function nullOperatorThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('operator should not be NULL');

        self::join(fn(JoinClause $j) => $j->on('a.id', null, 'x'));
    }

    #[Test]
    public function onRawAppendsRawCondition(): void
    {
        $query = self::join(fn(JoinClause $j) => $j->onRaw('a.id = 1'));

        self::assertSqlEquals('SELECT * FROM "a" LEFT JOIN "b" AS "b" ON a.id = 1', $query);
    }
}
