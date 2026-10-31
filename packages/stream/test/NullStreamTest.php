<?php

declare(strict_types=1);

namespace Windwalker\Stream\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Stream\NullStream;

class NullStreamTest extends TestCase
{
    #[Test]
    public function readAndContentsAreAlwaysEmpty(): void
    {
        $stream = new NullStream();

        $this->assertSame('', (string) $stream);
        $this->assertSame('', $stream->read(10));
        $this->assertSame('', $stream->getContents());
    }

    #[Test]
    public function writeDiscardsDataAndReportsZeroBytes(): void
    {
        $stream = new NullStream();

        $this->assertSame(0, $stream->write('FOO'));
        $this->assertSame('', (string) $stream);
    }

    #[Test]
    public function stateIsAlwaysEmptySeekableReadableWritableAndAtEof(): void
    {
        $stream = new NullStream();

        $stream->seek(5);
        $stream->rewind();
        $stream->close();

        $this->assertSame(0, $stream->getSize());
        $this->assertSame(0, $stream->tell());
        $this->assertTrue($stream->eof());
        $this->assertTrue($stream->isSeekable());
        $this->assertTrue($stream->isWritable());
        $this->assertTrue($stream->isReadable());
    }

    #[Test]
    public function detachAndMetadataReturnNull(): void
    {
        $stream = new NullStream();

        $this->assertNull($stream->detach());
        $this->assertNull($stream->getMetadata());
        $this->assertNull($stream->getMetadata('mode'));
    }
}
