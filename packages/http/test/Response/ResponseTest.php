<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Response;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\Response\Response;
use Windwalker\Stream\Stream;

/**
 * Test class of Response
 *
 * @since 2.1
 */
class ResponseTest extends TestCase
{
    protected Response $instance;

    #[Before]
    protected function createResponse(): void
    {
        $this->instance = new Response();
    }

    #[Test]
    public function constructWithoutParamsUsesMemoryStreamAndDefaults(): void
    {
        $res = new Response();

        self::assertInstanceOf(Stream::class, $res->getBody());
        self::assertEquals('php://memory', $res->getBody()->getMetadata('uri'));
        self::assertEquals(200, $res->getStatusCode());
        self::assertEquals([], $res->getHeaders());
    }

    #[Test]
    public function constructWithResourceBodyStatusAndHeadersAppliesThem(): void
    {
        $body = fopen($tmpfile = tempnam(sys_get_temp_dir(), 'windwalker'), 'wb+');
        $headers = [
            'X-Foo' => ['Flower', 'Sakura'],
            'Content-Type' => 'application/json',
        ];

        $res = new Response($body, 404, $headers);

        self::assertInstanceOf(Stream::class, $res->getBody());
        self::assertEquals($tmpfile, $res->getBody()->getMetadata('uri'));
        self::assertEquals(['Flower', 'Sakura'], $res->getHeader('x-foo'));
        self::assertEquals(['application/json'], $res->getHeader('content-type'));

        fclose($body);
    }

    #[Test]
    public function constructWithStreamObjectKeepsSameStreamInstance(): void
    {
        $body = new Stream();
        $res = new Response($body);

        self::assertSame($body, $res->getBody());
    }

    #[Test]
    public function withStatusReturnsNewInstanceWithStatusCodeAndReasonPhrase(): void
    {
        self::assertEquals(200, $this->instance->getStatusCode());

        $res = $this->instance->withStatus(403);

        self::assertNotSame($res, $this->instance);
        self::assertEquals(403, $res->getStatusCode());

        $res = $res->withStatus(500, 'Unknown error');

        self::assertEquals(500, $res->getStatusCode());
        self::assertEquals('Unknown error', $res->getReasonPhrase());
    }

    #[Test]
    public function getReasonPhraseReturnsDefaultPhraseForStatusCode(): void
    {
        $res = new Response();

        $res = $res->withStatus(200);

        self::assertEquals('OK', $res->getReasonPhrase());

        $res = $res->withStatus(400);

        self::assertEquals('Bad Request', $res->getReasonPhrase());

        $res = $res->withStatus(404);

        self::assertEquals('Not Found', $res->getReasonPhrase());

        $res = $res->withStatus(500);

        self::assertEquals('Internal Server Error', $res->getReasonPhrase());
    }
}
