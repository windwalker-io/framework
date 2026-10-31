<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Response;

use DOMDocument;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;
use Windwalker\Http\Response\XmlResponse;

class XmlResponseTest extends TestCase
{
    #[Test]
    public function constructNormalizesXmlStringWithUtf8Encoding(): void
    {
        $response = new XmlResponse('<root><foo>bar</foo></root>');

        self::assertXmlStringEqualsXmlString(
            '<root><foo>bar</foo></root>',
            (string) $response->getBody(),
        );
        self::assertSame('application/xml; charset=utf-8', $response->getHeaderLine('Content-Type'));
    }

    #[Test]
    public function constructAcceptsSimpleXmlElementAndDomDocument(): void
    {
        $fromSimple = new XmlResponse(new SimpleXMLElement('<root><foo>1</foo></root>'));

        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadXML('<root><foo>1</foo></root>');
        $fromDom = new XmlResponse($doc);

        self::assertXmlStringEqualsXmlString((string) $fromSimple->getBody(), (string) $fromDom->getBody());
    }

    #[Test]
    public function constructAppliesStatusAndHeaders(): void
    {
        $response = new XmlResponse('<root/>', 202, ['X-Foo' => 'bar']);

        self::assertSame(202, $response->getStatusCode());
        self::assertSame('bar', $response->getHeaderLine('X-Foo'));
    }

    #[Test]
    public function constructThrowsOnUnsupportedContentType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Invalid XML content type, array provided.');

        new XmlResponse(['foo']);
    }
}
