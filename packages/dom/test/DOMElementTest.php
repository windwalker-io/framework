<?php

declare(strict_types=1);

namespace Windwalker\DOM\Test;

use DOMDocument;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\DOM\DOMElement;
use Windwalker\DOM\DOMFactory;
use Windwalker\Test\Traits\DOMTestTrait;

class DOMElementTest extends TestCase
{
    use DOMTestTrait;

    #[Test]
    public function createsElementWithAttributesAndContent(): void
    {
        $ele = DOMElement::create(
            'field',
            [
                'name' => 'foo',
                'label' => 'FOO',
                'class' => function () {
                    return 'col-12 form-control';
                },
                'data-options' => [
                    'handle' => '.handle',
                    'enabled' => true
                ],
                'data-empty' => '',
                'data-true' => true
            ]
        );

        self::assertDomStringEqualsDomString(
            '<field name="foo" label="FOO" class="col-12 form-control" '
            . 'data-options="{&quot;handle&quot;:&quot;.handle&quot;,&quot;enabled&quot;:true}"  data-empty="" data-true=""/>',
            $ele
        );

        $ele = DOMElement::create(
            'field',
            [
                'name' => 'foo',
                'label' => 'FOO',
                'class' => function () {
                    return 'col-12 form-control';
                },
                'data-options' => [
                    'handle' => '.handle',
                    'enabled' => true
                ]
            ],
            DOMElement::create('span', [], 'Hello')
        );

        self::assertDomStringEqualsDomString(
            '<field name="foo" label="FOO" class="col-12 form-control" '
            . 'data-options="{&quot;handle&quot;:&quot;.handle&quot;,&quot;enabled&quot;:true}">'
            . '<span>Hello</span></field>',
            $ele
        );
    }

    #[Test]
    #[DataProvider('cssCreatorProvider')]
    public function createsElementFromCssSelector(string $selector, array $attributes, string $result): void
    {
        $ele = DOMElement::create($selector, $attributes);

        self::assertSame($result, (string) $ele);
    }

    public static function cssCreatorProvider(): array
    {
        return [
            [
                'img#foo',
                [],
                '<img id="foo"/>'
            ],
            [
                'img.foo',
                [],
                '<img class="foo"/>'
            ],
            [
                '#foo.bar',
                [],
                '<div id="foo" class="bar"/>'
            ],
            [
                'img#foo.bar.yoo',
                [],
                '<img id="foo" class="bar yoo"/>'
            ],
            [
                'div#foo#foo2.bar.yoo',
                [],
                '<div id="foo2" class="bar yoo"/>'
            ],
            [
                'div#foo.bar',
                [
                    'id' => 'hoo',
                    'class' => 'yoo'
                ],
                '<div id="foo" class="yoo bar"/>'
            ]
        ];
    }

    #[Test]
    public function supportsArrayAccess(): void
    {
        $ele = DOMElement::create('hello');
        $ele['data-foo'] = 'bar';

        self::assertTrue(isset($ele['data-foo']));
        self::assertSame('bar', $ele['data-foo']);
        self::assertSame('<hello data-foo="bar"/>', $ele->render(null));

        unset($ele['data-foo']);

        self::assertSame('<hello/>', $ele->render(null));
        self::assertFalse(isset($ele['data-foo']));
    }

    #[Test]
    public function convertsToString(): void
    {
        $ele = DOMElement::create('hello');
        $ele['data-foo'] = 'bar';

        self::assertSame('<hello data-foo="bar"/>', (string) $ele);
    }

    #[Test]
    public function queriesAllSelectorMatches(): void
    {
        $dom = DOMFactory::create();
        $dom->loadXML(
            <<<XML
<div class="row">
    <div class="col-lg-6 first-col">
        <img src="hello.jpg"/>
    </div>
    <div class="col-lg-6 second-col">
        <img src="flower.jpg"/>
    </div>
    <div class="col-lg-6 third-col">
        <img src="sakura.jpg"/>
    </div>
</div>
XML
        );

        $ele = DOMElement::create(
            'div',
            ['class' => 'root-node'],
            $dom->documentElement
        );

        $imgs = $ele->querySelectorAll('img');
        $images = [];

        foreach ($imgs as $img) {
            $images[] = $img['src'];
        }

        self::assertSame(
            [
                'hello.jpg',
                'flower.jpg',
                'sakura.jpg'
            ],
            $images
        );
    }

    #[Test]
    public function queriesFirstSelectorMatch(): void
    {
        $dom = DOMFactory::create();
        $dom->loadXML(
            <<<XML
<div class="row">
    <div class="col-lg-6 first-col">
        <img src="hello.jpg"/>
    </div>
    <div class="col-lg-6 second-col">
        <img src="flower.jpg"/>
    </div>
    <div class="col-lg-6 third-col">
        <img src="sakura.jpg"/>
    </div>
</div>
XML
        );

        $ele = DOMElement::create(
            'div',
            ['class' => 'root-node'],
            $dom->documentElement
        );

        $img = $ele->querySelector('img');

        self::assertSame(1, $img->count());

        self::assertSame(
            'hello.jpg',
            $img->attr('src')
        );
    }

    #[Test]
    public function returnsElementName(): void
    {
        $ele = DOMElement::create('hello');

        self::assertSame('hello', $ele->getName());
    }

    #[Test]
    public function returnsAttributes(): void
    {
        $ele = DOMElement::create(
            'hello',
            [
                'foo' => 'bar',
                'flower' => 'sakura'
            ]
        );

        $attrs = $ele->getAttributes();

        self::assertSame('sakura', $attrs['flower']->value);

        $attrs = $ele->getAttributes(true);

        self::assertSame('sakura', $attrs['flower']);
    }

    #[Test]
    public function setsAttributes(): void
    {
        $ele = DOMElement::create('hello');

        $ele->setAttributes(
            [
                'foo' => 'bar',
                'flower' => 'sakura'
            ]
        );

        self::assertSame('bar', $ele['foo']);
        self::assertSame('sakura', $ele['flower']);
    }

    #[Test]
    public function importsIntoAnotherDocument(): void
    {
        $dom = new DOMDocument();
        $root = $dom->createElement('root');

        $ele = DOMElement::create('hello');
        $root->appendChild($ele->with($root));

        self::assertSame('<root><hello/></root>', $dom->saveXML($root));
    }

    #[Test]
    public function createsChildElements(): void
    {
        $ele = DOMElement::create('root');
        $hello = $ele->createChild('hello');
        $hello->setAttribute('foo', 'bar');

        self::assertSame('<root><hello foo="bar"/></root>', $ele->render(null));
    }

    #[Test]
    #[IgnoreDeprecations]
    public function buildsAttributes(): void
    {
        $attrs = DOMElement::buildAttributes(
            [
                'class' => 'foo bar',
                'data-foo' => 'yoo',
                'required' => true,
                'selected' => 'true',
                'disabled' => false,
                'no-show' => null,
                'empty-text' => ''
            ]
        );

        self::assertSame(
            'class="foo bar" data-foo="yoo" required selected="true" empty-text',
            $attrs
        );
    }
}
