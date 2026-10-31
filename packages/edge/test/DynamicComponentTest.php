<?php

declare(strict_types=1);

namespace Windwalker\Edge\Test;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Windwalker\Edge\Cache\EdgeArrayCache;
use Windwalker\Edge\Component\ComponentExtension;
use Windwalker\Edge\Component\DynamicComponent;
use Windwalker\Edge\Edge;
use Windwalker\Edge\Loader\EdgeFileLoader;
use Windwalker\Test\Traits\DOMTestTrait;

class DynamicComponentTest extends TestCase
{
    use DOMTestTrait;

    protected Edge $edge;

    #[Before]
    public function setUpEdge(): void
    {
        $this->edge = new Edge(new EdgeFileLoader([__DIR__ . '/tmpl']), new EdgeArrayCache());
        $this->edge->addExtension(new ComponentExtension($this->edge));
    }

    #[Test]
    public function rendersAnonymousViewWithBoundPropsAndExtraAttributes(): void
    {
        $html = $this->edge->render('components.dynamic');

        self::assertDomStringEqualsDomString(
            <<<'HTML'
            <div id="foo" class="alert alert-DYN extra">
                Foo Component: DYN - Message: Msg
                Body
            </div>
            HTML,
            $html
        );
    }

    #[Test]
    public function compilePropsAndBindingsFromKeys(): void
    {
        $component = new DynamicComponent();

        $props = new ReflectionMethod($component, 'compileProps');
        $bindings = new ReflectionMethod($component, 'compileBindings');

        self::assertSame('', $props->invoke($component, []));
        self::assertSame("@props(['fooBar','baz'])", $props->invoke($component, ['foo-bar', 'baz']));
        self::assertSame(':foo-bar="$fooBar" :a.b="$aB"', $bindings->invoke($component, ['foo-bar', 'a.b']));
    }

    #[Test]
    public function compileSlotsSkipsDefaultSlot(): void
    {
        $component = new DynamicComponent();
        $method = new ReflectionMethod($component, 'compileSlots');

        self::assertSame(
            '<x-slot name="header">{{ $header }}</x-slot>',
            $method->invoke($component, ['__default' => 'x', 'header' => 'h'])
        );
        self::assertSame('', $method->invoke($component, []));
    }
}
