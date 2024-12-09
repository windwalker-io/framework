<?php

declare(strict_types=1);

namespace Windwalker\DOM\Test;

use Dom\HTMLDocument;
use DOMDocument;
use Masterminds\HTML5;
use Windwalker\DOM\DOMElement;
use Windwalker\DOM\HTML5Factory;
use Windwalker\DOM\HTMLElement;
use Windwalker\Test\Traits\DOMTestTrait;

/**
 * The DomElementTest class.
 */
class HTMLElementTest extends DOMElementTest
{
    use DOMTestTrait;

    /**
     * @var DOMElement
     */
    protected $instance;

    /**
     * @see  HTMLElement::create
     */
    public function testCreate(): void
    {
        $ele = HTMLElement::new(
            'field',
            [
                'name' => 'foo',
                'label' => 'FOO',
                'class' => function () {
                    return 'col-12 form-control';
                },
                'data-options' => [
                    'handle' => '.handle',
                    'enabled' => true,
                ],
            ]
        );

        self::assertDomStringEqualsDomString(
            '<field name="foo" label="FOO" class="col-12 form-control" ' .
            'data-options="{&quot;handle&quot;:&quot;.handle&quot;,&quot;enabled&quot;:true}"></field>',
            $ele
        );

        $ele = HTMLElement::new(
            'field',
            [
                'name' => 'foo',
                'label' => 'FOO',
                'class' => function () {
                    return 'col-12 form-control';
                },
                'data-options' => [
                    'handle' => '.handle',
                    'enabled' => true,
                ],
            ],
            HTMLElement::new('span', [], 'Hello')
        );

        self::assertDomStringEqualsDomString(
            '<field name="foo" label="FOO" class="col-12 form-control" ' .
            'data-options="{&quot;handle&quot;:&quot;.handle&quot;,&quot;enabled&quot;:true}">' .
            '<span>Hello</span></field>',
            $ele
        );
    }

    public function testSelfClosed(): void
    {
        $img = HTMLElement::new('img', ['src' => 'hello.jpg', 'width' => 300], '');

        self::assertEquals('<img src="hello.jpg" width="300">', $img->render());

        $div = HTMLElement::new('div', ['class' => 'hello']);

        self::assertEquals('<div class="hello"></div>', $div->render());
    }

    /**
     * @see  DOMElement::offsetSet
     * @see  DOMElement::offsetGet
     */
    public function testOffsetAccess(): void
    {
        $ele = HTMLElement::new('hello');
        $ele['data-foo'] = 'bar';

        self::assertTrue(isset($ele['data-foo']));
        self::assertEquals('bar', $ele['data-foo']);
        self::assertEquals('<hello data-foo="bar"></hello>', $ele->render());

        unset($ele['data-foo']);

        self::assertEquals('<hello></hello>', $ele->render());
        self::assertFalse(isset($ele['data-foo']));
    }

    /**
     * @see  DOMElement::__toString
     */
    public function testToString(): void
    {
        $ele = HTMLElement::create('hello');
        $ele['data-foo'] = 'bar';

        self::assertEquals('<hello data-foo="bar"></hello>', (string) $ele);
    }

    /**
     * @see  DOMElement::querySelectorAll
     */
    public function testQuerySelectorAll(): void
    {
        /** @var HTMLDocument $dom */
        $dom = HTMLDocument::createFromString(
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

        $ele = HTMLElement::new(
            'div',
            ['class' => 'root-node'],
            $dom->documentElement
        );

        $imgs = $ele->querySelectorAll('img');

        foreach ($imgs as $img) {
            $images[] = $img['src'];
        }

        self::assertEquals(
            [
                'hello.jpg',
                'flower.jpg',
                'sakura.jpg',
            ],
            $images
        );
    }

    /**
     * @see  DOMElement::querySelector
     */
    public function testQuerySelector(): void
    {
        /** @var HTMLDocument $dom */
        $dom = HTMLDocument::createFromString(
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

        $ele = HTMLElement::new(
            'div',
            ['class' => 'root-node'],
            $dom->documentElement
        );

        $img = $ele->querySelector('img');

        self::assertEquals(
            'hello.jpg',
            $img->getAttribute('src')
        );
    }

    /**
     * @see  DOMElement::getName
     */
    public function testGetName(): void
    {
        $ele = HTMLElement::new('hello');

        self::assertEquals('HELLO', $ele->getName());
    }

    /**
     * @see  DOMElement::getAttributes
     */
    public function testGetAttributes(): void
    {
        $ele = HTMLElement::new(
            'hello',
            [
                'foo' => 'bar',
                'flower' => 'sakura',
            ]
        );

        $attrs = $ele->getAttributes();

        self::assertEquals('sakura', $attrs['flower']->value);

        $attrs = $ele->getAttributes(true);

        self::assertEquals('sakura', $attrs['flower']);
    }

    /**
     * @see  DOMElement::setAttributes
     */
    public function testSetAttributes(): void
    {
        $ele = HTMLElement::create('hello');

        $ele->setAttributes(
            [
                'foo' => 'bar',
                'flower' => 'sakura',
            ]
        );

        self::assertEquals('bar', $ele['foo']);
        self::assertEquals('sakura', $ele['flower']);
    }

    public function testWith(): void
    {
        $dom = new DOMDocument();
        $root = $dom->createElement('root');

        $ele = HTMLElement::create('hello');
        $root->appendChild($ele->with($root));

        self::assertEquals('<root><hello/></root>', $dom->saveXML($root));
    }

    public function testCreateChild(): void
    {
        $ele = HTMLElement::create('root');
        $hello = $ele->createChild('hello');
        $hello->setAttribute('foo', 'bar');

        self::assertEquals('<root><hello foo="bar"></hello></root>', $ele->render(null));
    }

    public function testBuildAttributes()
    {
        $attrs = HTMLElement::buildAttributes(
            [
                'class' => 'foo bar',
                'data-foo' => 'yoo',
                'required' => true,
                'selected' => true,
                'disabled' => false,
                'no-show' => null,
                'normal-attr' => '',
            ]
        );

        self::assertEquals('class="foo bar" data-foo="yoo" required selected normal-attr', $attrs);
    }

    protected function setUp(): void
    {
        $this->instance = DOMElement::class;
    }

    protected function tearDown(): void
    {
    }
}
