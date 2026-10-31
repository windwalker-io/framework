<?php

declare(strict_types=1);

namespace Windwalker\Query\Test\Expression;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Query\Expression\Expression;
use Windwalker\Query\Grammar\BaseGrammar;
use Windwalker\Query\Query;
use Windwalker\Query\Test\Mock\MockEscaper;

class ExpressionTest extends TestCase
{
    private Expression $expr;

    private MockEscaper $escaper;

    #[Before]
    public function createExpression(): void
    {
        $this->escaper = new MockEscaper();
        $this->expr = new Expression(new Query($this->escaper, new BaseGrammar()));
    }

    #[Test]
    public function isExpressionDetectsTrailingParenthesis(): void
    {
        self::assertTrue(Expression::isExpression('COUNT(*)'));
        self::assertFalse(Expression::isExpression('foo'));
    }

    #[Test]
    public function buildFallsBackToGenericFunctionClause(): void
    {
        self::assertSame('max(a, b)', (string) $this->expr->build('max', 'a', 'b'));
    }

    #[Test]
    public function buildDispatchesToExistingMethod(): void
    {
        self::assertSame('CONCATENATE(a || b)', (string) $this->expr->build('concat', ['a', 'b']));
    }

    #[Test]
    public function concatUsesQuotedSeparator(): void
    {
        self::assertSame("CONCATENATE(a || '-' || b)", (string) $this->expr->concat(['a', 'b'], '-'));
    }

    #[Test]
    public function caseSwitchBuildsCaseWithElse(): void
    {
        self::assertSame(
            "CASE WHEN a = 1 THEN 'x'\n ELSE 'y'\n END",
            $this->expr->caseSwitch(['a = 1' => "'x'"], "'y'")
        );
    }

    #[Test]
    public function caseSwitchOmitsElseWhenNotGiven(): void
    {
        self::assertSame("CASE WHEN a = 1 THEN 'x'\n END", $this->expr->caseSwitch(['a = 1' => "'x'"]));
    }

    #[Test]
    public function magicCallBuildsSnakeCaseFunction(): void
    {
        self::assertSame('char_length(a)', (string) $this->expr->charLength('a'));
    }
}
