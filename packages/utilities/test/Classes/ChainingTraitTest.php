<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Classes;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Classes\ChainingTrait;
use Windwalker\Utilities\Classes\FlowControlTrait;

class ChainingTraitTest extends TestCase
{
    #[Test]
    public function pipeReturnsCallbackResultWithExtraArgs(): void
    {
        $a = new StubChaining();
        $b = new StubChaining();

        $result = $a->pipe(fn(StubChaining $self, int $n) => $b->withValue($n), 5);

        self::assertSame($b, $result);
        self::assertSame(5, $b->value);
    }

    #[Test]
    public function tapRunsCallbackAndReturnsSelf(): void
    {
        $a = new StubChaining();

        $result = $a->tap(fn(StubChaining $self, int $n) => $self->withValue($n), 3);

        self::assertSame($a, $result);
        self::assertSame(3, $a->value);
    }

    #[Test]
    public function pipeIfRunsTrueOrFalseCallback(): void
    {
        $a = new StubChaining();
        $b = new StubChaining();

        self::assertSame($b, $a->pipeIf(true, fn() => $b, fn() => $a));
        self::assertSame($a, $a->pipeIf(false, fn() => $b, fn() => $a));
        self::assertSame($b, $a->pipeIf(false, null, fn(StubChaining $s, $x) => $x, $b));
    }

    #[Test]
    public function pipeIfReturnsSelfWhenCallbackIsMissing(): void
    {
        $a = new StubChaining();

        self::assertSame($a, $a->pipeIf(true));
        self::assertSame($a, $a->pipeIf(false, fn() => new StubChaining()));
    }

    #[Test]
    public function tapIfRunsMatchingCallbackAndAlwaysReturnsSelf(): void
    {
        $a = new StubChaining();

        self::assertSame($a, $a->tapIf(1, fn(StubChaining $s, $n) => $s->withValue($n), null, 7));
        self::assertSame(7, $a->value);

        self::assertSame($a, $a->tapIf(0, null, fn(StubChaining $s, $n) => $s->withValue($n), 8));
        self::assertSame(8, $a->value);

        self::assertSame($a, $a->tapIf(true));
        self::assertSame($a, $a->tapIf(false, fn() => $this->fail('Must not be called')));
        self::assertSame(8, $a->value);
    }

    #[Test]
    public function deprecatedFlowControlTraitProvidesChainingMethods(): void
    {
        $o = new class () {
            use FlowControlTrait;
        };

        self::assertSame($o, $o->tap(fn() => null));
    }
}

/**
 * Fixture using ChainingTrait, used by ChainingTraitTest.
 *
 * Remove it together with ChainingTraitTest when the trait is removed.
 */
class StubChaining
{
    use ChainingTrait;

    public int $value = 0;

    public function withValue(int $value): static
    {
        $this->value = $value;

        return $this;
    }
}
