<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Request;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\Request\Request;
use Windwalker\Uri\Uri;

/**
 * Test class of Request
 *
 * @since 2.1
 */
class RequestTest extends TestCase
{
    protected Request $instance;

    #[Before]
    protected function createRequest(): void
    {
        $this->instance = new Request();
    }

    #[Test]
    public function getHeadersIncludesHostHeaderFromUri(): void
    {
        self::assertEquals([], $this->instance->getHeaders());

        $request = $this->instance->withUri(new Uri('http://windwalker.io/flower/sakura'));

        self::assertEquals(['Host' => ['windwalker.io']], $request->getHeaders());
    }

    #[Test]
    public function getHeaderReturnsHostHeaderFromUri(): void
    {
        self::assertEquals([], $this->instance->getHeader('host'));

        $request = $this->instance->withUri(new Uri('http://windwalker.io/flower/sakura'));

        self::assertEquals(['windwalker.io'], $request->getHeader('host'));
    }

    #[Test]
    public function hasHeaderDetectsHostHeaderCaseInsensitively(): void
    {
        $request = new Request('http://example.com/foo', 'GET');

        self::assertTrue($request->hasHeader('host'));
        self::assertTrue($request->hasHeader('Host'));
        self::assertFalse($request->hasHeader('X-Foo'));
    }

    #[Test]
    public function getHeaderReturnsEmptyArrayForMissingHeader(): void
    {
        $request = new Request('http://example.com/foo', 'GET');

        self::assertSame([], $request->getHeader('X-Missing'));
    }

    #[Test]
    public function hostHeaderIncludesPortFromUri(): void
    {
        $request = new Request('http://example.com:8080/foo', 'GET');

        self::assertSame(['example.com:8080'], $request->getHeader('Host'));
        self::assertSame(['example.com:8080'], $request->getHeaders()['Host']);
    }

    #[Test]
    public function explicitHostHeaderTakesPrecedenceOverUriHost(): void
    {
        $request = new Request('http://example.com/foo', 'GET', 'php://memory', ['Host' => 'other.test']);

        self::assertSame(['other.test'], $request->getHeaders()['Host']);
    }

    #[Test]
    public function hostHeaderIsMissingWhenUriHasNoHost(): void
    {
        $request = new Request('/foo', 'GET');

        self::assertFalse($request->hasHeader('host'));
        self::assertArrayNotHasKey('Host', $request->getHeaders());
        self::assertSame([], $request->getHeader('host'));
    }
}
