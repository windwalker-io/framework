<?php

declare(strict_types=1);

namespace Windwalker\Edge\Test;

use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Windwalker\Edge\Cache\EdgeArrayCache;
use Windwalker\Edge\Component\AnonymousComponent;
use Windwalker\Edge\Component\ComponentExtension;
use Windwalker\Edge\Component\ComponentTagCompiler;
use Windwalker\Edge\Component\DynamicComponent;
use Windwalker\Edge\Edge;
use Windwalker\Edge\Loader\EdgeFileLoader;
use Windwalker\Edge\Test\Component\FooComponent;

class ComponentTagCompilerTest extends TestCase
{
    protected Edge $edge;

    protected ComponentExtension $extension;

    #[Before]
    public function setUpEdge(): void
    {
        $this->edge = new Edge(new EdgeFileLoader([__DIR__ . '/tmpl']), new EdgeArrayCache());
        $this->edge->addExtension($this->extension = new ComponentExtension($this->edge));
        $this->extension->registerComponent('foo', FooComponent::class);
    }

    protected function compiler(array $components = []): ComponentTagCompiler
    {
        return new ComponentTagCompiler($this->edge, $components + $this->extension->getComponents());
    }

    #[Test]
    public function componentClassResolvesRegisteredClass(): void
    {
        self::assertSame(FooComponent::class, $this->compiler()->componentClass('foo'));
    }

    #[Test]
    public function componentClassResolvesAliasToView(): void
    {
        $compiler = $this->compiler(['alias' => 'components.foo-component']);

        self::assertSame('components.foo-component', $compiler->componentClass('alias'));
    }

    #[Test]
    public function componentClassFallsBackToComponentNameWhenAliasTargetMissing(): void
    {
        $compiler = $this->compiler(['hello' => 'missing.view']);

        self::assertSame('hello', $compiler->componentClass('hello'));
    }

    #[Test]
    public function componentClassThrowsWhenAliasTargetAndNameMissing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains(
            'Unable to locate class or view [missing.view] for component [nope]'
        );

        $this->compiler(['nope' => 'missing.view'])->componentClass('nope');
    }

    #[Test]
    public function componentClassResolvesViewByName(): void
    {
        self::assertSame('components.foo-component', $this->compiler()->componentClass('components.foo-component'));
    }

    #[Test]
    public function componentClassThrowsForUnknownComponent(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Unable to locate a class or view for component [unknown]');

        $this->compiler()->componentClass('unknown');
    }

    #[Test]
    public function compileSelfClosingClassComponentWithBoundAttribute(): void
    {
        $out = $this->compiler()->compile('<x-foo :id="$id" class="a" />');

        self::assertStringContainsString("@component('" . FooComponent::class . "', 'foo'", $out);
        self::assertStringContainsString("'id' => ", $out);
        self::assertStringContainsString("'class' => 'a'", $out);
        self::assertStringContainsString('@endComponentClass', $out);
    }

    #[Test]
    public function compileAnonymousComponentPassesViewAndData(): void
    {
        $out = $this->compiler()->compile('<x-components.foo-component type="x" />');

        self::assertStringContainsString("@component('" . AnonymousComponent::class . "'", $out);
        self::assertStringContainsString("'view' => 'components.foo-component'", $out);
    }

    #[Test]
    public function compileDynamicComponentInjectsEdge(): void
    {
        $out = $this->compiler()->compile('<x-dynamic-component is="foo" />');

        self::assertStringContainsString('$__edge', $out);
        self::assertStringContainsString("'is' => 'foo'", $out);
    }

    #[Test]
    public function compileBoundAttributesBagIsPassedAsAttributes(): void
    {
        $out = $this->compiler()->compile('<x-foo :attributes="$attributes" />');

        self::assertStringContainsString("'attributes' =>", $out);
    }

    #[Test]
    public function boundAttributeWithoutValueDefaultsToTrue(): void
    {
        $out = $this->compiler()->compile('<x-foo disabled />');

        self::assertStringContainsString("'disabled' => true", $out);
    }

    #[Test]
    public function escapedAttributeKeepsLiteralColon(): void
    {
        $out = $this->compiler()->compile('<x-foo ::title="x" />');

        self::assertStringContainsString("':title' =>", $out);
    }

    #[Test]
    public function singleQuotesInHtmlAreEscaped(): void
    {
        $method = new ReflectionMethod(ComponentTagCompiler::class, 'escapeSingleQuotesOutsideOfPhpBlocks');

        self::assertSame(
            "it\\'s <?php echo 'x'; ?>",
            $method->invoke($this->compiler(), "it's <?php echo 'x'; ?>")
        );
    }

    #[Test]
    public function dynamicComponentRequiresComponentExtension(): void
    {
        $edge = new Edge(new EdgeFileLoader([__DIR__ . '/tmpl']), new EdgeArrayCache());
        $component = new DynamicComponent();
        $component->edge = $edge;

        $method = new ReflectionMethod($component, 'createCompiler');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessageIsOrContains('Extension: ' . ComponentExtension::class . ' not found.');

        $method->invoke($component);
    }

    #[Test]
    public function dynamicComponentFindsExtensionByInstanceWhenNameDiffers(): void
    {
        $edge = new Edge(new EdgeFileLoader([__DIR__ . '/tmpl']), new EdgeArrayCache());
        $edge->addExtension(new ComponentExtension($edge));
        $component = new DynamicComponent();
        $component->edge = $edge;

        $compiler = (new ReflectionMethod($component, 'createCompiler'))->invoke($component);

        self::assertInstanceOf(ComponentTagCompiler::class, $compiler);
    }
}
