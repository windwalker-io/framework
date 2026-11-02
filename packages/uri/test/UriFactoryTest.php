<?php

declare(strict_types=1);

namespace Windwalker\Uri\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Uri\Uri;
use Windwalker\Uri\UriFactory;

class UriFactoryTest extends TestCase
{
    #[Test]
    public function createUriReturnsUriInstanceParsedFromString(): void
    {
        $uri = new UriFactory()->createUri('http://example.com/foo?a=1');

        self::assertInstanceOf(Uri::class, $uri);
        self::assertSame('example.com', $uri->getHost());
        self::assertSame('a=1', $uri->getQuery());
    }
}
