<?php

declare(strict_types=1);

namespace Windwalker\Query\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Clause\ExprClause;
use Windwalker\Query\Clause\QuoteNameClause;
use Windwalker\Query\Clause\ValueClause;

use function Windwalker\Query\clause;
use function Windwalker\Query\expr;
use function Windwalker\Query\qn;
use function Windwalker\Query\try_uuid2bin;
use function Windwalker\Query\val;

class FunctionsTest extends TestCase
{
    #[Test]
    public function clauseCreatesClauseWithElementsAndGlue(): void
    {
        $clause = clause('A', ['b', 'c'], ',');

        self::assertInstanceOf(Clause::class, $clause);
        self::assertSame('A b,c', (string) $clause);
    }

    #[Test]
    public function valWrapsScalarAndEachArrayItem(): void
    {
        self::assertInstanceOf(ValueClause::class, val('x'));

        $values = val([1, 2]);

        self::assertCount(2, $values);
        self::assertContainsOnlyInstancesOf(ValueClause::class, $values);
    }

    #[Test]
    public function qnWrapsScalarAndEachArrayItem(): void
    {
        self::assertInstanceOf(QuoteNameClause::class, qn('a'));

        $names = qn(['a', 'b']);

        self::assertCount(2, $names);
        self::assertContainsOnlyInstancesOf(QuoteNameClause::class, $names);
    }

    #[Test]
    public function exprCreatesExprClause(): void
    {
        self::assertInstanceOf(ExprClause::class, expr('concat', 'a', 'b'));
    }

    #[Test]
    public function tryUuid2binReturnsNullForNull(): void
    {
        self::assertNull(try_uuid2bin(null));
    }
}
