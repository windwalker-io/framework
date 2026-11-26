<?php

declare(strict_types=1);

namespace Windwalker\Query\Test\Concern;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Query\Grammar\BaseGrammar;
use Windwalker\Query\Query;
use Windwalker\Query\Test\Mock\MockEscaper;
use Windwalker\Query\Test\Stub\SortEnumStub;
use Windwalker\Test\Traits\QueryTestTrait;

class WhereConcernTraitTest extends TestCase
{
    use QueryTestTrait;

    private static ?MockEscaper $escaper = null;

    private static function createQuery(): Query
    {
        return new Query(self::$escaper ??= new MockEscaper(), new BaseGrammar())
            ->select('*')
            ->from('a');
    }

    #[Test]
    public function whereNullValueUsesIsNull(): void
    {
        $query = self::createQuery()->where('x', null)->where('y', '!=', null);

        self::assertSqlEquals('SELECT * FROM "a" WHERE "x" IS NULL AND "y" IS NOT NULL', $query);
    }

    #[Test]
    public function whereBooleanValueRendersLiteral(): void
    {
        $query = self::createQuery()->where('x', true)->where('y', false);

        self::assertSqlEquals('SELECT * FROM "a" WHERE "x" = true AND "y" = false', $query);
    }

    #[Test]
    public function whereBackedEnumUsesEnumValue(): void
    {
        $query = self::createQuery()->where('x', SortEnumStub::Asc);

        self::assertSqlEquals("SELECT * FROM \"a\" WHERE \"x\" = 'ASC'", $query);
    }

    #[Test]
    public function whereBetweenRequiresExactlyTwoValues(): void
    {
        $this->expectException(InvalidArgumentException::class);

        self::createQuery()->where('x', 'BETWEEN', [1, 2, 3]);
    }

    #[Test]
    public function whereNullOperatorThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('operator should not be NULL');

        self::createQuery()->where('x', null, 1);
    }

    #[Test]
    public function whereNestedClosureRejectsInvalidGlue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        self::createQuery()->where(fn(Query $q) => $q->where('x', 1), 'XOR');
    }

    #[Test]
    public function whereNestedClosureWithoutConditionsAddsNothing(): void
    {
        $query = self::createQuery()->where('x', 1)->where(static fn(Query $q) => null);

        self::assertSqlEquals('SELECT * FROM "a" WHERE "x" = 1', $query);
    }

    #[Test]
    public function whereRawFormatsArguments(): void
    {
        $query = self::createQuery()->whereRaw('%n = %q', 'x', 'foo');

        self::assertSqlEquals("SELECT * FROM \"a\" WHERE \"x\" = 'foo'", $query);
    }

    #[Test]
    public function andWhereGroupsConditionsWithAnd(): void
    {
        $query = self::createQuery()->andWhereGroup([['x', 1], ['y', 2]]);

        self::assertSqlEquals('SELECT * FROM "a" WHERE ("x" = 1 AND "y" = 2)', $query);
    }

    #[Test]
    public function orWhereGroupsConditionsWithOr(): void
    {
        $query = self::createQuery()->orWhereGroup([['x', 1], ['y', 2]]);

        self::assertSqlEquals('SELECT * FROM "a" WHERE ("x" = 1 OR "y" = 2)', $query);
    }

    #[Test]
    public function havingRawFormatsArguments(): void
    {
        $query = self::createQuery()->group('x')->havingRaw('COUNT(%n) > %a', 'y', 3);

        self::assertSqlEquals('SELECT * FROM "a" GROUP BY "x" HAVING COUNT("y") > 3', $query);
    }

    #[Test]
    public function orHavingGroupsConditionsWithOr(): void
    {
        $query = self::createQuery()->group('x')->orHavingGroup([['x', 1], ['x', 2]]);

        self::assertSqlEquals('SELECT * FROM "a" GROUP BY "x" HAVING ("x" = 1 OR "x" = 2)', $query);
    }

    #[Test]
    public function havingNestedClosureWithoutConditionsAddsNothing(): void
    {
        $query = self::createQuery()->group('x')->having('x', 1)->having(static fn(Query $q) => null);

        self::assertSqlEquals('SELECT * FROM "a" GROUP BY "x" HAVING "x" = 1', $query);
    }

    #[Test]
    public function whereExistsAcceptsClosureAndQuery(): void
    {
        $query = self::createQuery()
            ->whereExists(static fn(Query $q) => $q->select('x')->from('b'))
            ->whereNotExists(static fn(Query $q) => $q->select('x')->from('c'));

        self::assertSqlEquals(
            'SELECT * FROM "a" WHERE EXISTS(SELECT "x" FROM "b") AND NOT EXISTS(SELECT "x" FROM "c")',
            $query
        );
    }

    #[Test]
    public function havingExistsAcceptsQueryObject(): void
    {
        $sub = self::createQuery()->select('x')->from('b');
        $query = self::createQuery()->group('x')->havingExists($sub)->havingNotExists($sub);

        self::assertStringContainsString('HAVING EXISTS(SELECT', self::renderQuery($query));
        self::assertStringContainsString('AND NOT EXISTS(SELECT', self::renderQuery($query));
    }

    #[Test]
    public function whereInWithoutDefaultKeepsInOperator(): void
    {
        $query = self::createQuery()->whereIn('id', [1, 2, 3]);

        self::assertSqlEquals('SELECT * FROM "a" WHERE "id" IN (1, 2, 3)', $query);
    }

    #[Test]
    public function whereInWithoutDefaultAndEmptyValuesKeepsInOperator(): void
    {
        $query = self::createQuery()->whereIn('id', []);

        self::assertStringContainsString('"id" IN', self::renderQuery($query));
        self::assertStringNotContainsString('= 0', self::renderQuery($query));
    }

    #[Test]
    public function whereInWithoutDefaultAcceptsIterable(): void
    {
        $gen = (static function () {
            yield 1;
            yield 2;
        })();

        $query = self::createQuery()->whereIn('id', $gen);

        self::assertSqlEquals('SELECT * FROM "a" WHERE "id" IN (1, 2)', $query);
    }

    #[Test]
    public function whereInWithDefaultUsesDefaultWhenEmpty(): void
    {
        $query = self::createQuery()->whereIn('id', [], 0);

        self::assertSqlEquals('SELECT * FROM "a" WHERE "id" = 0', $query);
    }

    #[Test]
    public function whereInWithDefaultUsesInWhenNotEmpty(): void
    {
        $query = self::createQuery()->whereIn('id', [1, 2], 0);

        self::assertSqlEquals('SELECT * FROM "a" WHERE "id" IN (1, 2)', $query);
    }

    #[Test]
    public function havingInWithoutDefaultKeepsInOperator(): void
    {
        $query = self::createQuery()->group('id')->havingIn('id', [1, 2]);

        self::assertSqlEquals('SELECT * FROM "a" GROUP BY "id" HAVING "id" IN (1, 2)', $query);
    }

    #[Test]
    public function havingInWithDefaultUsesDefaultWhenEmpty(): void
    {
        $query = self::createQuery()->group('id')->havingIn('id', [], 0);

        self::assertSqlEquals('SELECT * FROM "a" GROUP BY "id" HAVING "id" = 0', $query);
    }

    #[Test]
    public function whereNotInWithoutDefaultUsesNotInOperator(): void
    {
        $query = self::createQuery()->whereNotIn('id', [1, 2, 3]);

        self::assertSqlEquals('SELECT * FROM "a" WHERE "id" NOT IN (1, 2, 3)', $query);
    }

    #[Test]
    public function whereNotInWithoutDefaultAndEmptyValuesKeepsNotInOperator(): void
    {
        $query = self::createQuery()->whereNotIn('id', []);

        self::assertStringContainsString('"id" NOT IN', self::renderQuery($query));
        self::assertStringNotContainsString('= 0', self::renderQuery($query));
    }

    #[Test]
    public function whereNotInWithoutDefaultAcceptsIterable(): void
    {
        $gen = (static function () {
            yield 1;
            yield 2;
        })();

        $query = self::createQuery()->whereNotIn('id', $gen);

        self::assertSqlEquals('SELECT * FROM "a" WHERE "id" NOT IN (1, 2)', $query);
    }

    #[Test]
    public function whereNotInWithDefaultUsesDefaultWhenEmpty(): void
    {
        $query = self::createQuery()->whereNotIn('id', [], 0);

        self::assertSqlEquals('SELECT * FROM "a" WHERE "id" != 0', $query);
    }

    #[Test]
    public function whereNotInWithDefaultUsesNotInWhenNotEmpty(): void
    {
        $query = self::createQuery()->whereNotIn('id', [1, 2], 0);

        self::assertSqlEquals('SELECT * FROM "a" WHERE "id" NOT IN (1, 2)', $query);
    }

    #[Test]
    public function havingNotInWithoutDefaultUsesNotInOperator(): void
    {
        $query = self::createQuery()->group('id')->havingNotIn('id', [1, 2]);

        self::assertSqlEquals('SELECT * FROM "a" GROUP BY "id" HAVING "id" NOT IN (1, 2)', $query);
    }

    #[Test]
    public function havingNotInWithDefaultUsesDefaultWhenEmpty(): void
    {
        $query = self::createQuery()->group('id')->havingNotIn('id', [], 0);

        self::assertSqlEquals('SELECT * FROM "a" GROUP BY "id" HAVING "id" != 0', $query);
    }

    #[Test]
    public function whereNotEqualWithArrayRendersNotIn(): void
    {
        $query = self::createQuery()->where('id', '!=', [1, 2]);

        self::assertSqlEquals('SELECT * FROM "a" WHERE "id" NOT IN (1, 2)', $query);
    }

    #[Test]
    public function havingNotEqualWithArrayRendersNotIn(): void
    {
        $query = self::createQuery()->group('id')->having('id', '!=', [1, 2]);

        self::assertSqlEquals('SELECT * FROM "a" GROUP BY "id" HAVING "id" NOT IN (1, 2)', $query);
    }
}
