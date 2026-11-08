<?php

declare(strict_types=1);

namespace Windwalker\Stream\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Stream\PhpInputStream;
use Windwalker\Stream\StringStream;

class PhpInputStreamTest extends TestCase
{
    #[Test]
    public function getInstanceAndCachedReturnsRewoundStreamWithInputContents(): void
    {
        $stream = PhpInputStream::getInstanceAndCached(new StringStream());

        $this->assertSame(0, $stream->tell());
        $this->assertSame('', $stream->getContents());
    }

    #[Test]
    public function getInstanceCreatesStreamWithGivenCache(): void
    {
        $stream = PhpInputStream::getInstance();

        $this->assertInstanceOf(PhpInputStream::class, $stream);
        $this->assertSame($stream, PhpInputStream::getInstance());
    }
}
