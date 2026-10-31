<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Symbol;

class SymbolTest extends TestCase
{
    #[Test]
    public function createReturnsSameInstanceForSameValue(): void
    {
        self::assertSame(Symbol::create('foo'), Symbol::create('foo'));
        self::assertNotSame(Symbol::create('foo'), Symbol::create('bar'));
    }

    #[Test]
    public function callStaticCreatesWrappedUppercaseSymbol(): void
    {
        $symbol = Symbol::noAction();

        self::assertSame($symbol, Symbol::noAction());
        self::assertSame('__NOACTION__', $symbol->getValue());
        self::assertSame($symbol, Symbol::create('__NOACTION__'));
    }

    #[Test]
    public function equalsAndIsAcceptSymbolsAndRawValues(): void
    {
        $symbol = Symbol::none();

        self::assertTrue($symbol->equals(Symbol::none()));
        self::assertTrue($symbol->is('__NONE__'));
        self::assertFalse($symbol->equals(Symbol::all()));
        self::assertFalse($symbol->is('none'));
    }

    #[Test]
    public function sameComparesIdentity(): void
    {
        self::assertTrue(Symbol::same(Symbol::root(), Symbol::root()));
        self::assertFalse(Symbol::same(Symbol::root(), Symbol::main()));
    }
}
