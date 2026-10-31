<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Context;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Context\Loop;

class LoopTest extends TestCase
{
    /**
     * @see  Loop::first
     * @see  Loop::last
     * @see  Loop::firstKey
     * @see  Loop::lastKey
     */
    #[Test]
    public function firstAndLastReturnEdgeValuesAndKeysOfTarget(): void
    {
        $target = ['a' => 'A', 'b' => 'B', 'c' => 'C'];
        $loop = new Loop(3, $target);

        self::assertSame('A', $loop->first());
        self::assertSame('C', $loop->last());
        self::assertSame('a', $loop->firstKey());
        self::assertSame('c', $loop->lastKey());
    }

    /**
     * @see  Loop::loop
     * @see  Loop::parent
     */
    #[Test]
    public function loopRecordsPositionAndKeepsParent(): void
    {
        $outerTarget = [1, 2];
        $innerTarget = [3];
        $outer = new Loop(2, $outerTarget);
        $inner = new Loop(1, $innerTarget, $outer);

        self::assertSame($inner, $inner->loop(0, 'k'));
        self::assertSame(0, $inner->index);
        self::assertSame('k', $inner->key);
        self::assertSame($outer, $inner->parent());
        self::assertNull($outer->parent());
    }

    /**
     * @see  Loop::stop
     * @see  Loop::isStop
     */
    #[Test]
    public function stopMarksOnlyCurrentLoopByDefault(): void
    {
        $t = [];
        $outer = new Loop(1, $t);
        $inner = new Loop(1, $t, $outer);

        self::assertFalse($inner->isStop());
        self::assertTrue($inner->stop());
        self::assertTrue($inner->isStop());
        self::assertFalse($outer->isStop());
    }

    /**
     * @see  Loop::stop
     */
    #[Test]
    public function stopWithDepthPropagatesToParents(): void
    {
        $t = [];
        $a = new Loop(1, $t);
        $b = new Loop(1, $t, $a);
        $c = new Loop(1, $t, $b);

        $c->stop(2);

        self::assertTrue($c->isStop());
        self::assertTrue($b->isStop());
        self::assertFalse($a->isStop());

        $c->stop(5);

        self::assertTrue($a->isStop());
    }
}
