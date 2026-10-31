<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Stream;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\Stream\RequestBodyStream;

class RequestBodyStreamTest extends TestCase
{
    #[Test]
    public function representsDataAsQueryString(): void
    {
        $stream = new RequestBodyStream(['a' => 1, 'b' => 'x y']);

        self::assertSame('a=1&b=x+y', (string) $stream);
        self::assertSame('a=1&b=x+y', $stream->getContents());
        self::assertSame('a=1&b=x+y', $stream->read(2));
        self::assertSame(strlen('a=1&b=x+y'), $stream->getSize());
    }

    #[Test]
    public function writeParsesQueryStringsAndCastsOtherValuesToArray(): void
    {
        $stream = new RequestBodyStream([]);

        self::assertSame(7, $stream->write('foo=bar'));
        self::assertSame(['foo' => 'bar'], $stream->getData());

        $stream->write(['x' => 'y']);

        self::assertSame(['x' => 'y'], $stream->getData());
    }

    #[Test]
    public function seekRewindAndTellTrackCursor(): void
    {
        $stream = new RequestBodyStream(['a' => 1]);

        $stream->seek(3);
        self::assertSame(3, $stream->tell());

        $stream->rewind();
        self::assertSame(0, $stream->tell());
    }

    #[Test]
    public function eofIsReachedWhenCursorAtLastDataIndex(): void
    {
        $stream = new RequestBodyStream(['a' => 1, 'b' => 2]);

        self::assertFalse($stream->eof());

        $stream->seek(1);

        self::assertTrue($stream->eof());
    }

    #[Test]
    public function detachClearsDataAndReturnsNull(): void
    {
        $stream = new RequestBodyStream(['a' => 1]);

        self::assertNull($stream->detach());
        self::assertSame([], $stream->getData());
        self::assertSame('', (string) $stream);
    }

    #[Test]
    public function reportsCapabilitiesAndEmptyMetadata(): void
    {
        $stream = new RequestBodyStream([]);

        self::assertTrue($stream->isSeekable());
        self::assertTrue($stream->isWritable());
        self::assertTrue($stream->isReadable());
        self::assertSame([], $stream->getMetadata());
        self::assertSame([], $stream->getMetadata('foo'));

        $stream->close();
    }

    #[Test]
    public function setDataReplacesDataFluently(): void
    {
        $stream = new RequestBodyStream(['a' => 1]);

        self::assertSame($stream, $stream->setData(['b' => 2]));
        self::assertSame('b=2', (string) $stream);
    }
}
