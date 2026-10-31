<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Serial;

use function Windwalker\serial;

class SerialTest extends TestCase
{
    #[Before]
    #[After]
    public function resetSequences(): void
    {
        Serial::resetAll();
    }

    #[Test]
    public function getIncrementsNamedSequence(): void
    {
        self::assertSame(0, Serial::current('a'));
        self::assertSame(1, Serial::get('a'));
        self::assertSame(2, Serial::get('a'));
        self::assertSame(1, Serial::get('b'));
        self::assertSame(2, Serial::current('a'));
    }

    #[Test]
    public function getIncrementsSequencePerObject(): void
    {
        $a = new \stdClass();
        $b = new \stdClass();

        self::assertSame(0, Serial::current($a));
        self::assertSame(1, Serial::get($a));
        self::assertSame(2, Serial::get($a));
        self::assertSame(1, Serial::get($b));
        self::assertSame(2, Serial::current($a));
    }

    #[Test]
    public function setAndResetWorkForNamesAndObjects(): void
    {
        $obj = new \stdClass();

        Serial::set('a', 10);
        Serial::set($obj, 20);

        self::assertSame(11, Serial::get('a'));
        self::assertSame(21, Serial::get($obj));

        Serial::reset('a');
        Serial::reset($obj);

        self::assertSame(0, Serial::current('a'));
        self::assertSame(0, Serial::current($obj));
    }

    #[Test]
    public function resetAllClearsEverything(): void
    {
        $obj = new \stdClass();
        Serial::get('a');
        Serial::get($obj);

        Serial::resetAll();

        self::assertSame(0, Serial::current('a'));
        self::assertSame(0, Serial::current($obj));
    }

    #[Test]
    public function getThrowsWhenNamedSequenceOverflows(): void
    {
        Serial::set('a', PHP_INT_MAX);

        $this->expectException(\OverflowException::class);
        $this->expectExceptionMessageIsOrContains('Sequence maxed out.');

        Serial::get('a');
    }

    #[Test]
    public function getThrowsWhenObjectSequenceOverflows(): void
    {
        $obj = new \stdClass();
        Serial::set($obj, PHP_INT_MAX);

        $this->expectException(\OverflowException::class);
        $this->expectExceptionMessageIsOrContains('Sequence maxed out.');

        Serial::get($obj);
    }

    #[Test]
    public function serialFunctionUsesDefaultSequence(): void
    {
        self::assertSame(1, serial());
        self::assertSame(2, serial('default'));
        self::assertSame(1, serial('other'));
    }
}
