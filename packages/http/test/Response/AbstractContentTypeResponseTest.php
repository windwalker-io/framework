<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Response;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Windwalker\Http\Test\Stub\StubContentTypeResponse;
use Windwalker\Test\TestHelper;

/**
 * Test class of AbstractContentTypeResponse
 *
 * @since 3.0
 */
class AbstractContentTypeResponseTest extends TestCase
{
    protected StubContentTypeResponse $instance;

    #[Before]
    protected function createContentTypeResponse(): void
    {
        $this->instance = new StubContentTypeResponse('Flower');
    }

    #[Test]
    public function constructSetsContentTypeHeadersBodyAndStatus(): void
    {
        $response = new StubContentTypeResponse('Flower', 123, ['x-foo' => 'bar']);

        $this->assertEquals(['text/plain; charset=utf-8'], $response->getHeader('content-type'));
        $this->assertEquals(['bar'], $response->getHeader('x-foo'));
        $this->assertEquals('Flower', $response->getBody()->__toString());
        $this->assertEquals(123, $response->getStatusCode());
    }

    #[Test]
    public function withContentTypeReplacesContentTypeHeaderWithCharset(): void
    {
        $response = $this->instance->withContentType('application/flower');

        $this->assertEquals(['application/flower; charset=utf-8'], $response->getHeader('content-type'));
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function normalizeContentTypeLowercasesContentType(): void
    {
        $this->assertEquals('text/plain', TestHelper::invoke($this->instance, 'normalizeContentType', 'Text/Plain'));
    }
}
