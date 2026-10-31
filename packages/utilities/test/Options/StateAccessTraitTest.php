<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Options;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Options\StateAccessTrait;

class StateAccessTraitTest extends TestCase
{
    protected function createSubject(): object
    {
        return new class () {
            use StateAccessTrait {
                prepareState as public;
            }
        };
    }

    #[Test]
    public function prepareStateMergesDefaultsAndOptionsRecursively(): void
    {
        $subject = $this->createSubject();

        $subject->prepareState(['a' => 1, 'b' => ['x' => 1]], ['b' => ['y' => 2]]);

        self::assertEquals(['a' => 1, 'b' => ['x' => 1, 'y' => 2]], $subject->getStates());
    }

    #[Test]
    public function getStateFallsBackToDefault(): void
    {
        $subject = $this->createSubject();
        $subject->setState('a', 1);

        self::assertSame(1, $subject->getState('a', 9));
        self::assertSame(9, $subject->getState('none', 9));
        self::assertNull($subject->getState('none'));
    }

    #[Test]
    public function setStateReturnsSelf(): void
    {
        $subject = $this->createSubject();

        self::assertSame($subject, $subject->setState('a', 1));
    }
}
