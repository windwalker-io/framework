<?php

declare(strict_types=1);

namespace Windwalker\Stream\Test;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Stream\CachingStream;
use Windwalker\Stream\Stream;
use Windwalker\Stream\StringStream;

class CachingStreamTest extends TestCase
{
    private function createCachingStream(string $content = 'FOO BAR', ?Stream $cache = null): CachingStream
    {
        $remote = Stream::fromString($content);
        $remote->rewind();

        return new CachingStream($remote, $cache);
    }

    #[Test]
    public function readCachesDataFromRemoteStream(): void
    {
        $cache = new Stream('php://temp', 'rb+');
        $stream = $this->createCachingStream('FOO BAR', $cache);

        $this->assertEquals('FOO', $stream->read(3));
        $this->assertEquals(3, $cache->getSize());

        $stream->rewind();

        $this->assertEquals('FOO BAR', $stream->read(100));
    }

    #[Test]
    public function seekWithSeekSetReadsAheadOnRemoteStream(): void
    {
        $stream = $this->createCachingStream();
        $stream->getSize();

        $stream->seek(4);

        $this->assertEquals(4, $stream->tell());
        $this->assertEquals('BAR', $stream->getContents());
    }

    #[Test]
    public function seekWithSeekCurMovesRelativeToCurrentPosition(): void
    {
        $stream = $this->createCachingStream();

        $stream->read(2);
        $stream->seek(2, SEEK_CUR);

        $this->assertEquals(4, $stream->tell());
    }

    #[Test]
    public function seekWithSeekEndCachesRemoteStream(): void
    {
        $stream = $this->createCachingStream();

        $stream->seek(1, SEEK_END);

        $this->assertEquals(5, $stream->tell());
        $this->assertEquals('AR', $stream->getContents());
    }

    #[Test]
    public function seekWithSeekEndUsesRemoteSizeWhenKnown(): void
    {
        $stream = new CachingStream(new StringStream('FOO BAR'));

        $stream->seek(0, SEEK_END);

        $this->assertEquals(6, $stream->tell());
    }

    #[Test]
    public function seekThrowsOnInvalidWhence(): void
    {
        $stream = $this->createCachingStream();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Invalid whence');

        $stream->seek(1, 99);
    }

    #[Test]
    public function writeOverwritesBufferAndSkipsRemoteBytes(): void
    {
        $stream = $this->createCachingStream('FOO BAR');

        $this->assertEquals(2, $stream->write('XY'));

        $stream->rewind();

        $this->assertEquals('XYO BAR', $stream->getContents());
    }

    #[Test]
    public function writeWithinAlreadyReadDataDoesNotSkipRemoteBytes(): void
    {
        $stream = $this->createCachingStream('FOO BAR');

        $stream->read(7);
        $stream->seek(1);
        $stream->write('X');
        $stream->rewind();

        $this->assertEquals('FXO BAR', $stream->getContents());
    }

    #[Test]
    public function eofRequiresBothBufferAndRemoteStreamToBeAtEnd(): void
    {
        $stream = $this->createCachingStream();

        $stream->read(3);
        $this->assertFalse($stream->eof());

        $stream->read(100);
        $this->assertTrue($stream->eof());
    }

    #[Test]
    public function getSizeReturnsLargerOfBufferAndRemoteSize(): void
    {
        $stream = $this->createCachingStream();

        $this->assertEquals(7, $stream->getSize());
    }

    #[Test]
    public function closeClosesBothStreams(): void
    {
        $remote = new StringStream('FOO');
        $cache = new StringStream();
        $stream = new CachingStream($remote, $cache);

        $stream->close();

        $this->assertEmpty($remote->getResource());
        $this->assertEmpty($cache->getResource());
    }
}
