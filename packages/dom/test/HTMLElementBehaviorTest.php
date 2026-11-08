<?php

declare(strict_types=1);

namespace Windwalker\DOM\Test;

use DOMException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;
use Windwalker\DOM\DOMFactory;
use Windwalker\DOM\HTMLElement;
use Windwalker\DOM\HTML5Factory;

/**
 * Tests HTMLElement-specific conversion, mutation, and serialization behavior.
 */
class HTMLElementBehaviorTest extends TestCase
{
    #[Test]
    public function appendsTextAndReturnsTheInsertedTextNode(): void
    {
        $element = HTMLElement::new('div');

        $text = $element->appendText('hello');

        self::assertInstanceOf(\Dom\Text::class, $text);
        self::assertSame($element, $text->parentNode);
        self::assertSame('hello', $text->data);
        self::assertSame('<div>hello</div>', $element->render());
    }

    #[Test]
    public function appendsNestedContentAndEmptyNodeLists(): void
    {
        $element = HTMLElement::new('div');
        $child = HTML5Factory::element('span', [], 'nested');

        $fragment = $element->appendChild(['before ', ['inside ', $child], ' after']);

        self::assertInstanceOf(\Dom\DocumentFragment::class, $fragment);
        self::assertSame(
            '<div>before inside <span>nested</span> after</div>',
            $element->render()
        );

        $emptyElement = HTMLElement::new('section');
        $emptyFragment = HTML5Factory::create()->createDocumentFragment();

        self::assertSame($emptyElement, $emptyElement->appendChild($emptyFragment->childNodes));
        self::assertSame('<section></section>', $emptyElement->render());
    }

    #[Test]
    public function importsLegacyAndForeignDocumentElements(): void
    {
        $element = HTMLElement::new('div');
        $legacyDocument = DOMFactory::create();
        $legacyChild = $legacyDocument->createElement('strong', 'legacy');
        $element->appendChild($legacyChild);

        $foreignDocument = HTML5Factory::create();
        $foreignChild = $foreignDocument->createElement('em');
        $foreignChild->textContent = 'foreign';
        $element->appendChild($foreignChild);

        self::assertSame('<div><strong>legacy</strong><em>foreign</em></div>', $element->render());
        self::assertSame($element->ownerDocument, $element->firstChild->ownerDocument);
        self::assertSame($element->ownerDocument, $element->lastChild->ownerDocument);
        self::assertNotSame($legacyChild, $element->firstChild);
        self::assertNotSame($foreignChild, $element->lastChild);
    }

    #[Test]
    public function setsBooleanNullJsonAndCallableAttributeValues(): void
    {
        $element = HTMLElement::new('input');
        $element->setAttribute('required', true);
        $element->setAttribute('data-config', ['enabled' => true]);
        $element->setAttribute('title', static fn() => 'from callback');

        self::assertSame('{"enabled":true}', $element->getAttribute('data-config'));
        self::assertSame('from callback', $element->getAttribute('title'));

        $element->setAttribute('data-config', false);
        $element->setAttribute('title', null);

        self::assertTrue($element->hasAttribute('required'));
        self::assertSame('', $element->getAttribute('required'));
        self::assertFalse($element->hasAttribute('data-config'));
        self::assertFalse($element->hasAttribute('title'));
    }

    #[Test]
    public function returnsNoAttributesForAnElementWithoutAttributes(): void
    {
        self::assertSame([], HTMLElement::new('div')->getAttributes());
    }

    #[Test]
    public function wrapsInvalidAttributeNamesWithTheirNameAndPreviousException(): void
    {
        $element = HTMLElement::new('div');

        try {
            $element->setAttribute('invalid name', 'value');
            self::fail('Expected an invalid attribute name to throw a DOMException.');
        } catch (DOMException $exception) {
            self::assertSame(5, $exception->getCode());
            self::assertSame('Invalid Character Error for attribute: "invalid name"', $exception->getMessage());
            self::assertInstanceOf(DOMException::class, $exception->getPrevious());
        }
    }

    #[Test]
    public function buildsAttributeStringsFromHtmlAndLegacyElements(): void
    {
        $htmlElement = HTMLElement::new('input', ['title' => 'sample', 'disabled' => true]);
        $legacyElement = DOMFactory::create()->createElement('input');
        $legacyElement->setAttribute('title', 'sample');
        $legacyElement->setAttribute('disabled', '');

        self::assertSame('title="sample" disabled', HTMLElement::buildAttributes($htmlElement));
        self::assertSame('title="sample" disabled', HTMLElement::buildAttributes($legacyElement));
        self::assertSame('title="sample" disabled', $htmlElement->attributesToString());
    }

    #[Test]
    public function importsIntoDocumentsWithOptionalDeepCopy(): void
    {
        $source = HTMLElement::new('div', [], HTMLElement::new('span', [], 'child'));
        $document = HTML5Factory::create();
        $deepCopy = $source->with($document);
        $shallowCopy = $source->with($document, false);

        self::assertSame('child', $deepCopy->firstChild->textContent);
        self::assertFalse($shallowCopy->hasChildNodes());
        self::assertSame($document, $deepCopy->ownerDocument);
        self::assertSame($document, $shallowCopy->ownerDocument);
    }

    #[Test]
    public function returnsAConfiguredDomCrawler(): void
    {
        $element = HTMLElement::new('div', [], HTMLElement::new('span', [], 'child'));
        $crawler = $element->getCrawler();

        self::assertInstanceOf(Crawler::class, $crawler);
        self::assertSame(1, $crawler->filter('div')->count());
        self::assertSame('child', $crawler->filter('span')->text());
    }

    #[Test]
    public function updatesClassesUsingFluentMethods(): void
    {
        $element = HTMLElement::new('div');

        self::assertSame($element, $element->addClass('alpha beta alpha'));
        self::assertSame($element, $element->removeClass('alpha'));
        self::assertSame($element, $element->toggleClass('active', true));
        self::assertSame($element, $element->toggleClass('beta', false));
        self::assertTrue($element->hasClass('active'));
        self::assertFalse($element->hasClass('beta'));
        self::assertSame('active', $element->getAttribute('class'));
    }

    #[Test]
    public function splitsCssSelectorsAndRejectsAnEmptyTagName(): void
    {
        self::assertSame(
            ['name' => 'div', 'id' => 'hero', 'class' => 'preview featured'],
            HTMLElement::splitCSSSelector('#hero.preview.featured')
        );
        self::assertSame(
            ['name' => 'article', 'id' => 'main', 'class' => 'wide'],
            HTMLElement::splitCSSSelector('article#main.wide')
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Tag name is empty');

        HTMLElement::splitCSSSelector('');
    }
}
