<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Classes;

use BadMethodCallException;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Classes\MarcoableTrait;

class MarcoableTraitTest extends TestCase
{
    /**
     * @var  MarcoableTrait
     */
    protected $instance;

    /**
     * @see  MarcoableTrait::__call
     */
    #[Test]
    public function instanceCallInvokesRegisteredMacro(): void
    {
        $r = $this->instance->foo('Simon');

        self::assertEquals('Hello:Simon', $r);
    }

    /**
     * @see  MarcoableTrait::do
     */
    #[Test]
    public function doInvokesRegisteredMacroByName(): void
    {
        $r = $this->instance->do('foo', 'Simon');

        self::assertEquals('Hello:Simon', $r);
    }

    /**
     * @see  MarcoableTrait::hasMacro
     */
    #[Test]
    public function hasMacroReturnsTrueForRegisteredMacro(): void
    {
        $this->instance->do('foo', 'Simon');

        self::assertTrue($this->instance::hasMacro('foo'));
    }

    /**
     * @see  MarcoableTrait::__callStatic
     */
    #[Test]
    public function staticCallInvokesRegisteredMacro(): void
    {
        $r = $this->instance::foo('Simon');

        self::assertEquals('Hello:Simon', $r);
    }

    /**
     * @see  MarcoableTrait::clearMarco()
     */
    #[Test]
    public function clearMarcoRemovesAllMacros(): void
    {
        $this->instance::clearMarco();

        self::assertFalse($this->instance::hasMacro('foo'));
    }

    #[Test]
    public function callingMissingMacroOnInstanceThrows(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessageIsOrContains('::missing does not exist.');

        $this->instance->missing();
    }

    #[Test]
    public function callingMissingMacroStaticallyThrows(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessageIsOrContains('::missing does not exist.');

        $this->instance::missing();
    }

    #[Test]
    public function nonClosureMacroIsCalledAsIs(): void
    {
        $this->instance::macro('up', 'strtoupper');

        self::assertSame('ABC', $this->instance::up('abc'));
        self::assertSame('ABC', $this->instance->up('abc'));
    }

    #[Before]
    protected function registerFooMacro(): void
    {
        $this->instance = new class () {
            use MarcoableTrait;
        };

        $this->instance::macro(
            'foo',
            function ($a = null) {
                return 'Hello:' . $a;
            }
        );
    }
}
