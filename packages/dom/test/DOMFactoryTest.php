<?php

declare(strict_types=1);

namespace Windwalker\DOM\Test;

use DOMDocument;
use DOMDocumentFragment;
use DOMElement;
use Masterminds\HTML5;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\DOM\DOMFactory;

class DOMFactoryTest extends TestCase
{
    #[Before]
    #[After]
    protected function resetFactory(): void
    {
        DOMFactory::reset();
    }

    #[Test]
    public function createsAndReusesAnXmlDocument(): void
    {
        $document = DOMFactory::create(['encoding' => 'ISO-8859-1']);
        DOMFactory::document($document);

        self::assertInstanceOf(DOMDocument::class, DOMFactory::document());
        self::assertSame($document, DOMFactory::document());
        self::assertSame('ISO-8859-1', $document->encoding);
        self::assertInstanceOf(\Windwalker\DOM\DOMElement::class, $document->createElement('root'));
    }

    #[Test]
    public function createsElementsCommentsTextAndFragments(): void
    {
        $fragment = DOMFactory::fragment();
        $fragment->appendChild(DOMFactory::element('item', 'hello'));
        $fragment->appendChild(DOMFactory::comment('note'));
        $fragment->appendChild(DOMFactory::textNode('tail'));

        self::assertInstanceOf(DOMDocumentFragment::class, $fragment);
        self::assertSame('<item>hello</item><!--note-->tail', $fragment->ownerDocument->saveXML($fragment));
    }

    #[Test]
    public function parsesXmlAndSerializesNodes(): void
    {
        $node = DOMFactory::parse('<root><item>hello</item></root>');

        self::assertInstanceOf(DOMElement::class, $node);
        self::assertSame('item', $node->nodeName);
        self::assertSame('hello', $node->textContent);
        self::assertSame('<item>hello</item>', DOMFactory::saveXML($node));
    }

    #[Test]
    public function cachesAnInjectedHtml5Serializer(): void
    {
        $serializer = new HTML5();

        self::assertSame($serializer, DOMFactory::html5($serializer));
        self::assertSame($serializer, DOMFactory::html5());
    }
}
