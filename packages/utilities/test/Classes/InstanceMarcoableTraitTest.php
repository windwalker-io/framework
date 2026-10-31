<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Classes;

use BadMethodCallException;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Classes\InstanceMarcoableTrait;

class InstanceMarcoableTraitTest extends TestCase
{
    protected object $instance;

    #[Before]
    protected function createInstance(): void
    {
        $this->instance = new class () {
            use InstanceMarcoableTrait;

            protected string $name = 'inner';
        };
    }

    #[Test]
    public function macroIsCalledByMagicCallAndDo(): void
    {
        $this->instance->macro('greet', fn(string $a) => 'Hello:' . $a);

        self::assertSame('Hello:Sakura', $this->instance->greet('Sakura'));
        self::assertSame('Hello:Sakura', $this->instance->do('greet', 'Sakura'));
    }

    #[Test]
    public function macroClosureIsBoundToInstance(): void
    {
        $this->instance->macro('name', fn() => $this->name);

        self::assertSame('inner', $this->instance->name());
    }

    #[Test]
    public function nonClosureCallableIsCalledAsIs(): void
    {
        $this->instance->macro('up', 'strtoupper');

        self::assertSame('ABC', $this->instance->up('abc'));
    }

    #[Test]
    public function macroIsStoredPerInstance(): void
    {
        $this->instance->macro('foo', fn() => 1);

        $other = clone $this->instance;
        $other->clearMarco();

        self::assertTrue($this->instance->hasMacro('foo'));
        self::assertFalse($other->hasMacro('foo'));
    }

    #[Test]
    public function macroAndClearMarcoSupportChaining(): void
    {
        self::assertSame($this->instance, $this->instance->macro('foo', fn() => 1));
        self::assertSame($this->instance, $this->instance->clearMarco());
        self::assertFalse($this->instance->hasMacro('foo'));
    }

    #[Test]
    public function callingMissingMacroThrows(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessageIsOrContains('::missing does not exist.');

        $this->instance->missing();
    }
}
