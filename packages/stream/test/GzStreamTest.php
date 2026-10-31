<?php

declare(strict_types=1);

namespace Windwalker\Stream\Test;

use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Windwalker\Stream\GzStream;

use const Windwalker\Stream\READ_ONLY_FROM_BEGIN;
use const Windwalker\Stream\WRITE_ONLY_RESET;

class GzStreamTest extends TestCase
{
    private string $tmpnam;

    #[Before]
    protected function createTempFile(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'windwalker');
    }

    #[After]
    protected function removeTempFile(): void
    {
        if (is_file($this->tmpnam)) {
            unlink($this->tmpnam);
        }
    }

    private function createGzFile(string $content = 'FOO BAR'): void
    {
        file_put_contents($this->tmpnam, gzencode($content));
    }

    #[Test]
    public function fromFilePathOpensGzFileForReading(): void
    {
        $this->createGzFile();

        $stream = GzStream::fromFilePath($this->tmpnam, READ_ONLY_FROM_BEGIN);

        $this->assertEquals('FOO BAR', $stream->getContents());
    }

    #[Test]
    public function writeCompressesDataIntoFile(): void
    {
        $stream = new GzStream($this->tmpnam, WRITE_ONLY_RESET);

        $this->assertEquals(7, $stream->write('FOO BAR'));

        $stream->close();

        $this->assertEquals('FOO BAR', gzdecode(file_get_contents($this->tmpnam)));
    }

    #[Test]
    public function attachAcceptsPathAndRejectsInvalidResource(): void
    {
        $this->createGzFile();
        $stream = new GzStream($this->tmpnam, READ_ONLY_FROM_BEGIN);

        $this->assertIsResource($stream->getResource());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Invalid resource.');

        $stream->attach(123);
    }

    #[Test]
    public function toStringReturnsDecompressedContents(): void
    {
        $this->createGzFile();

        $stream = new GzStream($this->tmpnam, READ_ONLY_FROM_BEGIN);
        $stream->read(2);

        $this->assertEquals('FOO BAR', (string) $stream);
    }

    #[Test]
    public function toStringReturnsEmptyStringWhenNotReadable(): void
    {
        $stream = new GzStream($this->tmpnam, WRITE_ONLY_RESET);

        $this->assertSame('', (string) $stream);
    }

    #[Test]
    public function readReturnsDecompressedChunks(): void
    {
        $this->createGzFile();
        $stream = new GzStream($this->tmpnam, READ_ONLY_FROM_BEGIN);

        $this->assertEquals('FOO', $stream->read(3));
        $this->assertEquals(' BAR', $stream->read(10));
        $this->assertTrue($stream->eof());
    }

    #[Test]
    public function seekAndTellTrackDecompressedPosition(): void
    {
        $this->createGzFile();
        $stream = new GzStream($this->tmpnam, READ_ONLY_FROM_BEGIN);

        $stream->seek(4);

        $this->assertEquals(4, $stream->tell());
        $this->assertEquals('BAR', $stream->read(3));

        $stream->rewind();

        $this->assertEquals(0, $stream->tell());
    }

    #[Test]
    public function isSeekableReflectsResourceAvailability(): void
    {
        $stream = new GzStream($this->tmpnam, WRITE_ONLY_RESET);

        $this->assertTrue($stream->isSeekable());

        $stream->close();

        $this->assertFalse($stream->isSeekable());
    }

    #[Test]
    public function isReadableDependsOnMode(): void
    {
        $this->createGzFile();

        $reader = new GzStream($this->tmpnam, READ_ONLY_FROM_BEGIN);
        $writer = new GzStream($this->tmpnam, WRITE_ONLY_RESET);

        $this->assertTrue($reader->isReadable());
        $this->assertFalse($writer->isReadable());
    }

    #[Test]
    public function readThrowsWhenNotReadable(): void
    {
        $stream = new GzStream($this->tmpnam, WRITE_ONLY_RESET);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Stream is not readable');

        $stream->read(1);
    }

    #[Test]
    public function getContentsReturnsEmptyStringWhenNotReadable(): void
    {
        $stream = new GzStream($this->tmpnam, WRITE_ONLY_RESET);

        $this->assertSame('', $stream->getContents());
    }

    #[Test]
    public function getSizeIsNotSupported(): void
    {
        $stream = new GzStream($this->tmpnam, WRITE_ONLY_RESET);

        $this->expectException(LogicException::class);

        $stream->getSize();
    }

    #[Test]
    public function getMetadataReturnsAllOrSingleKey(): void
    {
        $this->createGzFile();
        $stream = new GzStream($this->tmpnam, READ_ONLY_FROM_BEGIN);

        $this->assertIsArray($stream->getMetadata());
        $this->assertEquals('rb', $stream->getMetadata('mode'));
        $this->assertNull($stream->getMetadata('not-exists'));
    }

    #[Test]
    public function closeReleasesResourceAndIsIdempotent(): void
    {
        $stream = new GzStream($this->tmpnam, WRITE_ONLY_RESET);

        $stream->close();
        $stream->close();

        $this->assertNull($stream->getResource());
    }

    #[Test]
    public function isWritableDependsOnOpenMode(): void
    {
        $writer = new GzStream($this->tmpnam, WRITE_ONLY_RESET);
        $this->assertTrue($writer->isWritable());
        $writer->close();

        $reader = new GzStream($this->tmpnam, READ_ONLY_FROM_BEGIN);
        $this->assertFalse($reader->isWritable());
        $reader->close();
    }

    #[Test]
    public function detachedStreamIsAtEofAndNotReadable(): void
    {
        $stream = new GzStream($this->tmpnam, WRITE_ONLY_RESET);

        $this->assertIsResource($stream->detach());
        $this->assertTrue($stream->eof());
        $this->assertFalse($stream->isWritable());
        $this->assertFalse($stream->isReadable());
    }

    #[Test]
    public function operationsThrowWhenNoResourceAvailable(): void
    {
        $stream = new GzStream($this->tmpnam, WRITE_ONLY_RESET);
        $stream->detach();

        foreach (
            [
                fn() => $stream->tell(),
                fn() => $stream->seek(0),
                fn() => $stream->write('x'),
                fn() => $stream->read(1),
            ] as $operation
        ) {
            try {
                $operation();
                $this->fail('Expected RuntimeException');
            } catch (RuntimeException $e) {
                $this->assertEquals('No resource available.', $e->getMessage());
            }
        }
    }
}
