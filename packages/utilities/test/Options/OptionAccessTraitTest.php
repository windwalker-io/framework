<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Options;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Options\OptionAccessTrait;
use Windwalker\Utilities\Options\RecordOptions;

class OptionAccessTraitTest extends TestCase
{
    protected function createSubject(): object
    {
        return new class () {
            use OptionAccessTrait {
                prepareOptions as public;
            }
        };
    }

    #[Test]
    public function prepareOptionsMergesDefaultsAndOptionsRecursively(): void
    {
        $subject = $this->createSubject();

        $subject->prepareOptions(['a' => 1, 'b' => ['x' => 1]], ['b' => ['y' => 2], 'c' => 3]);

        self::assertEquals(['a' => 1, 'b' => ['x' => 1, 'y' => 2], 'c' => 3], $subject->getOptions());
    }

    #[Test]
    public function prepareOptionsAppliesDefaultsToRecordOptions(): void
    {
        $subject = $this->createSubject();
        $record = new class () extends RecordOptions {
            public mixed $a = null;

            public mixed $b = null;
        };
        $record->a = 'mine';

        $subject->prepareOptions(['a' => 'def', 'b' => 'def'], $record);

        self::assertInstanceOf(RecordOptions::class, $subject->getOptions());
        self::assertSame('mine', $subject->getOption('a'));
        self::assertSame('def', $subject->getOption('b'));
    }

    #[Test]
    public function getOptionFallsBackToDefault(): void
    {
        $subject = $this->createSubject();
        $subject->setOption('a', 1);

        self::assertSame(1, $subject->getOption('a', 9));
        self::assertSame(9, $subject->getOption('none', 9));
        self::assertNull($subject->getOption('none'));
    }

    #[Test]
    public function setOptionReturnsSelf(): void
    {
        $subject = $this->createSubject();

        self::assertSame($subject, $subject->setOption('a', 1));
    }
}
