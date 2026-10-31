<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Response;

use DOMDocument;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;
use Windwalker\Http\Response\EmptyResponse;
use Windwalker\Http\Response\HtmlResponse;
use Windwalker\Http\Response\JsonResponse;
use Windwalker\Http\Response\RedirectResponse;
use Windwalker\Http\Response\XmlResponse;
use Windwalker\Test\Traits\BaseAssertionTrait;

/**
 * Test class of content type responses.
 *
 * @since 3.0
 */
class ContentTypeResponseTest extends TestCase
{
    use BaseAssertionTrait;

    #[Test]
    public function htmlResponseSetsBodyAndHtmlContentType(): void
    {
        $response = new HtmlResponse('Flower');

        $this->assertEquals('Flower', $response->getBody()->__toString());
        $this->assertEquals(['text/html; charset=utf-8'], $response->getHeader('Content-Type'));
    }

    #[Test]
    public function emptyResponseKeepsBodyEmptyAndReadOnly(): void
    {
        $response = new EmptyResponse(204, ['x-foo' => 'bar']);

        $this->assertEquals('', $response->getBody()->__toString());

        // Still read only.
        $response->getBody()->write('Hello');

        $this->assertEquals('', $response->getBody()->__toString());

        $this->assertEquals(['bar'], $response->getHeader('x-foo'));
    }

    #[Test]
    public function jsonResponseEncodesArrayAndSetsJsonContentType(): void
    {
        $data = [
            'foo' => 'bar',
        ];

        $response = new JsonResponse($data);

        $this->assertEquals(json_encode($data), $response->getBody()->__toString());
        $this->assertEquals(['application/json; charset=utf-8'], $response->getHeader('Content-Type'));
    }

    #[Test]
    public function jsonResponseKeepsJsonStringAsIs(): void
    {
        $response = new JsonResponse('{"foo": 123}');

        $this->assertEquals('{"foo": 123}', $response->getBody()->__toString());
    }

    #[Test]
    public function xmlResponseRendersSimpleXmlElementAndSetsXmlContentType(): void
    {
        $xml = new SimpleXMLElement('<root />');
        $child = $xml->addChild('foo', 'bar');
        $child['flower'] = 'sakura';

        $response = new XmlResponse($xml);

        $expected = '<?xml version="1.0"?>
<root><foo flower="sakura">bar</foo></root>';

        $this->assertXmlStringEqualsXmlString($expected, $response->getBody()->__toString());
        $this->assertEquals(['application/xml; charset=utf-8'], $response->getHeader('Content-Type'));
    }

    #[Test]
    public function xmlResponseRendersDomDocument(): void
    {
        $xml = new DOMDocument();
        $xml->loadXML('<root />');

        $child = $xml->createElement('foo', 'bar');
        $child->setAttribute('flower', 'sakura');

        $xml->firstChild->appendChild($child);

        $response = new XmlResponse($xml);

        $expected = '<?xml version="1.0"?>
<root><foo flower="sakura">bar</foo></root>';

        $this->assertXmlStringEqualsXmlString($expected, $response->getBody()->__toString());
    }

    #[Test]
    public function redirectResponseSetsLocationHeaderAndStatusCode(): void
    {
        $response = new RedirectResponse('http://example.com', 307);

        $this->assertEquals(['http://example.com'], $response->getHeader('Location'));
        $this->assertEquals(307, $response->getStatusCode());
    }
}
