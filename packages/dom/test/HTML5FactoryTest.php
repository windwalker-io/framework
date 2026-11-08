<?php

declare(strict_types=1);

namespace Windwalker\DOM\Test;

use Dom\DocumentFragment;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\DOM\HTML5Factory;
use Windwalker\DOM\HTMLElement;

class HTML5FactoryTest extends TestCase
{
    #[Before]
    protected function resetDocument(): void
    {
        HTML5Factory::reset();
    }

    #[Test]
    public function registersWindwalkerElementsWhenCreatingDocuments(): void
    {
        $document = HTML5Factory::createFromString('<div>hello</div>');
        $element = $document->createElement('section');

        self::assertInstanceOf(HTMLElement::class, $element);
        self::assertSame('hello', $document->documentElement->textContent);
    }

    #[Test]
    public function parsesFirstElementAndMultipleFragmentNodes(): void
    {
        $element = HTML5Factory::parseAsElement('<strong>first</strong><em>second</em>');
        $fragment = HTML5Factory::parseAsFragment('<strong>first</strong><em>second</em>');

        self::assertInstanceOf(HTMLElement::class, $element);
        self::assertSame('strong', $element->localName);
        self::assertInstanceOf(DocumentFragment::class, $fragment);
        self::assertSame(
            '<strong>first</strong><em>second</em>',
            HTML5Factory::saveHtml($fragment)
        );
    }

    #[Test]
    public function serializesEscapedTextBooleanAttributesVoidElementsAndSvg(): void
    {
        $fragment = HTML5Factory::parseAsFragment(
            '<div title="a &amp; b">A &amp; B <input disabled><img src="a"><svg><path viewbox="0 0 1 1"></path></svg></div>'
        );

        self::assertSame(
            '<div title="a &amp; b">A &amp; B <input disabled><img src="a"><svg><path viewBox="0 0 1 1" /></svg></div>',
            HTML5Factory::saveHtml($fragment)
        );
    }

    #[Test]
    public function rendersScriptTextWithoutEscapingMarkupCharacters(): void
    {
        $script = HTML5Factory::element('script', [], 'if (a < b) { run(); }');

        self::assertSame('<script>if (a < b) { run(); }</script>', HTML5Factory::saveHtml($script));
    }

    #[Test]
    public function escapesStringsInsideArrayContent(): void
    {
        $element = HTML5Factory::element('p', [], ['a & b']);

        self::assertSame('<p>a &amp; b</p>', HTML5Factory::saveHtml($element));
    }

    #[Test]
    public function savesDocumentElementWithoutDoctypeWhenOmitDoctypeIsSet(): void
    {
        $doc = HTML5Factory::createFromString('<!DOCTYPE html><html><body><p>x</p></body></html>');

        self::assertSame(
            "<html><body><p>x</p></body></html>\n",
            HTML5Factory::saveHtml($doc, ['omit_doctype' => true]),
        );
    }
}
