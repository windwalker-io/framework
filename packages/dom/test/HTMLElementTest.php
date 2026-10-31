<?php

declare(strict_types=1);

namespace Windwalker\DOM\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\DOM\DOMElement;
use Windwalker\DOM\DOMFactory;
use Windwalker\DOM\HTMLElement;
use Windwalker\DOM\HTML5Factory;
use Windwalker\Test\Traits\DOMTestTrait;

class HTMLElementTest extends TestCase
{
    use DOMTestTrait;

    #[Test]
    #[DataProvider('elementFactoriesProvider')]
    #[IgnoreDeprecations]
    public function createsElementWithAttributesAndContent(bool $legacy): void
    {
        $element = self::createElement(
            $legacy,
            'field',
            [
                'name' => 'foo',
                'label' => 'FOO',
                'class' => static fn() => 'col-12 form-control',
                'data-options' => [
                    'handle' => '.handle',
                    'enabled' => true
                ]
            ]
        );

        self::assertDomStringEqualsDomString(
            '<field name="foo" label="FOO" class="col-12 form-control" '
            . 'data-options="{&quot;handle&quot;:&quot;.handle&quot;,&quot;enabled&quot;:true}"></field>',
            $element
        );

        $element = self::createElement(
            $legacy,
            'field',
            [
                'name' => 'foo',
                'label' => 'FOO',
                'class' => static fn() => 'col-12 form-control',
                'data-options' => [
                    'handle' => '.handle',
                    'enabled' => true
                ]
            ],
            self::createElement($legacy, 'span', [], 'Hello')
        );

        self::assertDomStringEqualsDomString(
            '<field name="foo" label="FOO" class="col-12 form-control" '
            . 'data-options="{&quot;handle&quot;:&quot;.handle&quot;,&quot;enabled&quot;:true}">'
            . '<span>Hello</span></field>',
            $element
        );
    }

    #[Test]
    #[DataProvider('elementFactoriesProvider')]
    #[IgnoreDeprecations]
    public function rendersVoidAndNormalElements(bool $legacy): void
    {
        $image = self::createElement($legacy, 'img', ['src' => 'hello.jpg', 'width' => 300], '');
        $div = self::createElement($legacy, 'div', ['class' => 'hello']);

        self::assertSame('<img src="hello.jpg" width="300">', self::renderElement($image));
        self::assertSame('<div class="hello"></div>', self::renderElement($div));
    }

    #[Test]
    #[DataProvider('elementFactoriesProvider')]
    #[IgnoreDeprecations]
    public function supportsArrayAccess(bool $legacy): void
    {
        $element = self::createElement($legacy, 'hello');
        $element['data-foo'] = 'bar';

        self::assertTrue(isset($element['data-foo']));
        self::assertSame('bar', $element['data-foo']);
        self::assertSame('<hello data-foo="bar"></hello>', self::renderElement($element));

        unset($element['data-foo']);

        self::assertSame('<hello></hello>', self::renderElement($element));
        self::assertFalse(isset($element['data-foo']));
    }

    #[Test]
    #[DataProvider('elementFactoriesProvider')]
    #[IgnoreDeprecations]
    public function convertsToString(bool $legacy): void
    {
        $element = self::createElement($legacy, 'hello');
        $element['data-foo'] = 'bar';

        self::assertSame('<hello data-foo="bar"></hello>', (string) $element);
    }

    #[Test]
    #[DataProvider('elementFactoriesProvider')]
    #[IgnoreDeprecations]
    public function queriesAllSelectorMatches(bool $legacy): void
    {
        $element = self::createImageFixture($legacy);
        $images = [];

        foreach ($element->querySelectorAll('img') as $image) {
            $images[] = $image->getAttribute('src');
        }

        self::assertSame(['hello.jpg', 'flower.jpg', 'sakura.jpg'], $images);
    }

    #[Test]
    #[DataProvider('elementFactoriesProvider')]
    #[IgnoreDeprecations]
    public function queriesFirstSelectorMatch(bool $legacy): void
    {
        $element = self::createImageFixture($legacy);
        $image = $element->querySelector('img');

        if ($legacy) {
            self::assertSame(1, $image->count());
            self::assertSame('hello.jpg', $image->attr('src'));

            return;
        }

        self::assertSame('hello.jpg', $image->getAttribute('src'));
    }

    #[Test]
    #[DataProvider('elementNameProvider')]
    #[IgnoreDeprecations]
    public function returnsElementName(bool $legacy, string $expected): void
    {
        self::assertSame($expected, self::createElement($legacy, 'hello')->getName());
    }

    public static function elementNameProvider(): array
    {
        return [
            'new API' => [false, 'HELLO'],
            'legacy API' => [true, 'hello']
        ];
    }

    #[Test]
    #[DataProvider('elementFactoriesProvider')]
    #[IgnoreDeprecations]
    public function returnsAttributes(bool $legacy): void
    {
        $element = self::createElement($legacy, 'hello', ['foo' => 'bar', 'flower' => 'sakura']);

        self::assertSame('sakura', $element->getAttributes()['flower']->value);
        self::assertSame('sakura', $element->getAttributes(true)['flower']);
    }

    #[Test]
    #[DataProvider('elementFactoriesProvider')]
    #[IgnoreDeprecations]
    public function setsAttributes(bool $legacy): void
    {
        $element = self::createElement($legacy, 'hello');
        $element->setAttributes(['foo' => 'bar', 'flower' => 'sakura']);

        self::assertSame('bar', $element['foo']);
        self::assertSame('sakura', $element['flower']);
    }

    #[Test]
    #[DataProvider('elementFactoriesProvider')]
    #[IgnoreDeprecations]
    public function importsIntoAnotherDocument(bool $legacy): void
    {
        if ($legacy) {
            $document = DOMFactory::create();
        } else {
            $document = HTML5Factory::create();
        }

        $root = $document->createElement('root');
        $root->appendChild(self::createElement($legacy, 'hello')->with($root));

        self::assertSame($legacy ? 'hello' : 'HELLO', $root->firstChild->nodeName);
        self::assertSame($root, $root->firstChild->parentNode);
    }

    #[Test]
    #[DataProvider('elementFactoriesProvider')]
    #[IgnoreDeprecations]
    public function createsChildElements(bool $legacy): void
    {
        $element = self::createElement($legacy, 'root');
        $child = $element->createChild('hello');
        $child->setAttribute('foo', 'bar');

        self::assertSame('<root><hello foo="bar"></hello></root>', self::renderElement($element));
    }

    #[Test]
    #[DataProvider('elementFactoriesProvider')]
    #[IgnoreDeprecations]
    public function createsElementFromCssSelector(bool $legacy): void
    {
        $element = self::createElement($legacy, 'img#hero.preview');

        self::assertSame('<img id="hero" class="preview">', self::renderElement($element));
    }

    #[Test]
    public function buildsAttributes(): void
    {
        $attributes = HTMLElement::buildAttributes(
            [
                'class' => 'foo bar',
                'data-foo' => 'yoo',
                'required' => true,
                'selected' => true,
                'disabled' => false,
                'no-show' => null,
                'normal-attr' => ''
            ]
        );

        self::assertSame('class="foo bar" data-foo="yoo" required selected normal-attr', $attributes);
    }

    #[Test]
    public function convertsLegacyElement(): void
    {
        $document = DOMFactory::create();
        $document->loadXML('<div class="foo"><div>Hello</div><div>World</div></div>');

        $element = HTMLElement::fromLegacyElement($document->documentElement);

        self::assertSame('DIV', $element->nodeName);
        self::assertSame('foo', $element->getAttribute('class'));
        self::assertSame('Hello', $element->firstElementChild->textContent);
        self::assertSame('World', $element->lastElementChild->textContent);
    }

    public static function elementFactoriesProvider(): array
    {
        return [
            'new API' => [false],
            'legacy API' => [true]
        ];
    }

    private static function createElement(
        bool $legacy,
        string $name,
        array $attributes = [],
        mixed $content = null
    ): DOMElement|HTMLElement {
        if ($legacy) {
            return HTMLElement::create($name, $attributes, $content);
        }

        return HTMLElement::new($name, $attributes, $content);
    }

    private static function createImageFixture(bool $legacy): DOMElement|HTMLElement
    {
        $columns = [];

        foreach (['hello.jpg', 'flower.jpg', 'sakura.jpg'] as $imageSource) {
            $image = self::createElement($legacy, 'img', ['src' => $imageSource]);
            $columns[] = self::createElement($legacy, 'div', ['class' => 'col-lg-6'], $image);
        }

        $row = self::createElement($legacy, 'div', ['class' => 'row'], $columns);

        return self::createElement($legacy, 'div', ['class' => 'root-node'], $row);
    }

    private static function renderElement(DOMElement|HTMLElement $element): string
    {
        if ($element instanceof HTMLElement) {
            return $element->render();
        }

        return $element->render(null);
    }
}
