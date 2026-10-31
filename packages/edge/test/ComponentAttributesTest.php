<?php

declare(strict_types=1);

namespace Windwalker\Edge\Test;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Edge\Component\ComponentAttributes;

class ComponentAttributesTest extends TestCase
{
    protected ComponentAttributes $instance;

    #[Before]
    public function setUpAttributes(): void
    {
        $this->instance = new ComponentAttributes(
            [
                'class' => 'a',
                'id' => 'x',
                'data-foo' => '1',
                'wire:click' => 'go',
                'disabled' => true,
            ]
        );
    }

    #[Test]
    public function getReturnsValueOrDefault(): void
    {
        self::assertSame('a', $this->instance->first());
        self::assertSame('x', $this->instance->get('id'));
        self::assertSame('d', $this->instance->get('none', 'd'));
        self::assertTrue($this->instance->has('id'));
        self::assertFalse($this->instance->has('none'));
    }

    #[Test]
    public function wrapReusesExistingInstance(): void
    {
        self::assertSame($this->instance, ComponentAttributes::wrap($this->instance));
        self::assertSame(['a' => 1], ComponentAttributes::wrap(['a' => 1])->getAttributes());
    }

    #[Test]
    #[DataProvider('selectionProvider')]
    public function selectAttributes(string $method, mixed $arg, array $expected): void
    {
        self::assertSame($expected, $this->instance->$method($arg)->getAttributes());
    }

    public static function selectionProvider(): array
    {
        return [
            'only one key' => ['only', 'id', ['id' => 'x']],
            'only many keys' => ['only', ['id', 'class'], ['class' => 'a', 'id' => 'x']],
            'except one key' => [
                'except',
                'id',
                ['class' => 'a', 'data-foo' => '1', 'wire:click' => 'go', 'disabled' => true],
            ],
            'whereStartsWith' => ['whereStartsWith', 'data-', ['data-foo' => '1']],
            'whereDoesntStartWith' => [
                'whereDoesntStartWith',
                'data-',
                ['class' => 'a', 'id' => 'x', 'wire:click' => 'go', 'disabled' => true],
            ],
            'thatStartWith strips nothing but keeps the match' => [
                'thatStartWith',
                'wire:',
                ['wire:click' => 'go'],
            ],
            'filter by key and value' => [
                'filter',
                fn($value, $key) => $key === 'id' && $value === 'x',
                ['id' => 'x'],
            ],
        ];
    }

    #[Test]
    public function toHtmlRendersAttributes(): void
    {
        self::assertSame(
            'class="a" id="x" data-foo="1" wire:click="go" disabled',
            (string) $this->instance
        );
    }

    #[Test]
    public function classPrependsConditionalClasses(): void
    {
        self::assertStringStartsWith(
            'class="b c a"',
            (string) $this->instance->class(['b', 'c' => true, 'd' => false])
        );
    }

    #[Test]
    public function mergeCombinesClassWithDefaults(): void
    {
        $html = (string) new ComponentAttributes(['class' => 'a', 'style' => 'k:v'])->merge(['class' => 'b']);

        self::assertSame('class="b a" style="k:v"', $html);
    }

    #[Test]
    public function mergeAddsMissingDefaults(): void
    {
        self::assertSame(
            'class="z a" x="y" id="x" data-foo="1" wire:click="go" disabled',
            (string) $this->instance->merge(['class' => 'z', 'x' => 'y'])
        );
    }

    #[Test]
    public function invokeMergesDefaults(): void
    {
        self::assertSame(
            (string) $this->instance->merge(['id' => 'q']),
            ($this->instance)(['id' => 'q'])
        );
    }

    #[Test]
    public function exceptPropsRemovesDeclaredProps(): void
    {
        self::assertSame(
            ['class' => 'a', 'data-foo' => '1', 'wire:click' => 'go', 'disabled' => true],
            $this->instance->exceptProps(['id' => 1])->getAttributes()
        );
    }

    #[Test]
    public function arrayAccessAndIteration(): void
    {
        self::assertTrue(isset($this->instance['id']));
        self::assertSame('x', $this->instance['id']);
        self::assertCount(5, iterator_to_array($this->instance));

        $this->instance['title'] = 't';
        unset($this->instance['id']);

        self::assertFalse(isset($this->instance['id']));
        self::assertSame('t', $this->instance['title']);
    }

    #[Test]
    public function toKebabCaseConvertsCamelCase(): void
    {
        self::assertSame('foo-bar-baz', ComponentAttributes::toKebabCase('fooBarBaz'));
    }
}
