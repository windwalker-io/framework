<?php

declare(strict_types=1);

namespace Windwalker\Edge\Test;

use Closure;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Edge\Component\AbstractComponent;
use Windwalker\Edge\Component\AnonymousComponent;
use Windwalker\Edge\Component\ComponentAttributes;
use Windwalker\Edge\Component\InvokableComponentVariable;

class AbstractComponentTest extends TestCase
{
    protected function createComponent(): AbstractComponent
    {
        return new class () extends AbstractComponent {
            public string $title = 'Hello';

            public static string $ignoredStatic = 'x';

            public string $secret = 'hidden';

            protected array $except = ['secret'];

            public function render(): Closure|string
            {
                return 'tmpl';
            }

            public function upper(): string
            {
                return strtoupper($this->title);
            }

            public function wrap(string $s): string
            {
                return "[$s]";
            }

            public function __magic(): void {}
        };
    }

    #[Test]
    public function dataExposesPropertiesAndMethods(): void
    {
        $component = $this->createComponent();
        $data = $component->data();

        self::assertSame('Hello', $data['title']);
        self::assertArrayNotHasKey('secret', $data);
        self::assertArrayNotHasKey('ignoredStatic', $data);
        self::assertArrayNotHasKey('render', $data);
        self::assertArrayNotHasKey('data', $data);
        self::assertArrayNotHasKey('__magic', $data);
        self::assertInstanceOf(ComponentAttributes::class, $data['attributes']);
    }

    #[Test]
    public function zeroArgMethodsBecomeInvokableVariables(): void
    {
        $data = $this->createComponent()->data();

        self::assertInstanceOf(InvokableComponentVariable::class, $data['upper']);
        self::assertSame('HELLO', (string) $data['upper']);
        self::assertSame('HELLO', $data['upper']());
    }

    #[Test]
    public function methodsWithArgsBecomeClosures(): void
    {
        $data = $this->createComponent()->data();

        self::assertInstanceOf(Closure::class, $data['wrap']);
        self::assertSame('[a]', $data['wrap']('a'));
    }

    #[Test]
    public function resolveViewAndShouldRender(): void
    {
        $component = $this->createComponent();

        self::assertSame('tmpl', $component->resolveView());
        self::assertTrue($component->shouldRender());
    }

    #[Test]
    public function withNameSetsName(): void
    {
        $component = $this->createComponent();

        self::assertSame($component, $component->withName('foo'));
        self::assertSame('foo', $component->componentName);
    }

    #[Test]
    public function withAttributesMutatesTheBag(): void
    {
        $component = $this->createComponent();

        self::assertSame($component, $component->withAttributes(['id' => 'a']));
        self::assertSame('a', $component->attributes->get('id'));
    }

    #[Test]
    public function mergeAttributesReplacesBagWithMergedCopy(): void
    {
        $component = $this->createComponent();
        $component->withAttributes(['id' => 'a']);
        $before = $component->attributes;

        self::assertSame($component, $component->mergeAttributes(['class' => 'b']));

        self::assertNotSame($before, $component->attributes);
        self::assertSame('a', $component->attributes->get('id'));
        self::assertSame('b', $component->attributes->get('class'));
        self::assertFalse($before->has('class'));
    }

    #[Test]
    public function mergeAttributesCreatesBagWhenMissing(): void
    {
        $component = $this->createComponent();

        self::assertNull($component->attributes);

        $component->mergeAttributes(['id' => 'z']);

        self::assertSame('z', $component->attributes->get('id'));
    }

    #[Test]
    public function mergeAttributesUnwrapsParentAttributesBag(): void
    {
        $component = $this->createComponent();
        $parent = new ComponentAttributes(['id' => 'p', 'class' => 'c']);

        $component->mergeAttributes(['attributes' => $parent, 'id' => 'own']);

        self::assertSame('own', $component->attributes->get('id'));
        self::assertSame('c', $component->attributes->get('class'));
    }

    #[Test]
    public function anonymousComponentMergesDataAndAttributes(): void
    {
        $component = new AnonymousComponent();
        $component->view = 'v';
        $component->data = [
            'attributes' => new ComponentAttributes(['a' => '1', 'b' => '1']),
            'x' => 'data',
        ];
        $component->withAttributes(['b' => '2']);

        $data = $component->data();

        self::assertSame('v', $component->render());
        self::assertSame('1', $data['a']);
        self::assertSame('2', $data['b']);
        self::assertSame('data', $data['x']);
        self::assertSame($component->attributes, $data['attributes']);
    }

    #[Test]
    public function anonymousComponentAcceptsPlainArrayAttributes(): void
    {
        $component = new AnonymousComponent();
        $component->data = ['attributes' => ['a' => '1']];

        self::assertSame('1', $component->data()['a']);
    }

    #[Test]
    public function invokableVariableProxiesObjectAccess(): void
    {
        $obj = new class () {
            public string $name = 'n';

            public function hi(string $x): string
            {
                return "hi $x";
            }
        };
        $var = new InvokableComponentVariable(fn() => $obj);

        self::assertSame('n', $var->name);
        self::assertSame('hi bob', $var->hi('bob'));
    }
}
