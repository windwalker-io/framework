<?php

declare(strict_types=1);

namespace Windwalker\Scalars\Test\Concern;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function Windwalker\str;

/**
 * The StringStructureTest class.
 */
class StringStructureTest extends TestCase
{
    #[Test]
    public function parseToCollectionDecodesJsonIntoCollection(): void
    {
        $str = str('{"foo": "bar"}');
        $json = $str->parseToCollection('json');

        self::assertEquals('bar', $json['foo']);
    }

    #[Test]
    public function jsonDecodeReturnsDecodedValues(): void
    {
        $str = str('{"foo": "bar"}');
        $json = $str->jsonDecode();

        self::assertEquals('bar', $json['foo']);
    }

    #[Test]
    public function toDOMDocumentParsesXmlStringIntoDocument(): void
    {
        $str = str('<root><foo bar="yoo" /></root>');
        $dom = $str->toDOMDocument();

        self::assertEquals('yoo', (string) $dom->documentElement->childNodes[0]->getAttribute('bar'));
    }

    #[Test]
    public function toHTMLDocumentParsesHtmlStringIntoDocument(): void
    {
        $str = str('<div><p bar="yoo" /></div>');
        $dom = $str->toHTMLDocument();

        $xpath = new \DOMXPath($dom);
        $elements = $xpath->query('/div/p');

        self::assertEquals('yoo', (string) $elements[0]->getAttribute('bar'));
    }

    #[Test]
    public function toSimpleXMLParsesXmlStringIntoElement(): void
    {
        $str = str('<div><p bar="yoo" /></div>');
        $xml = $str->toSimpleXML();
        $elements = $xml->xpath('/div/p');

        self::assertEquals('yoo', (string) $elements[0]['bar']);
    }
}
