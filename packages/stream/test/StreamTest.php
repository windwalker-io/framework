<?php

declare(strict_types=1);

namespace Windwalker\Stream\Test;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use ReflectionException;
use RuntimeException;
use Windwalker\Stream\Stream;
use Windwalker\Utilities\Reflection\ReflectAccessor;

use const Windwalker\Stream\READ_ONLY_FROM_BEGIN;
use const Windwalker\Stream\READ_WRITE_FROM_BEGIN;
use const Windwalker\Stream\READ_WRITE_RESET;
use const Windwalker\Stream\WRITE_ONLY_RESET;

/**
 * Test class of Stream
 *
 * @since 2.1
 */
class StreamTest extends TestCase
{
    /**
     * Test instance.
     *
     * @var  Stream
     */
    protected $instance;

    /**
     * Property tmp.
     *
     * @var  string
     */
    public $tmpnam;

    #[Before]
    protected function createMemoryStream(): void
    {
        $this->instance = new Stream('php://memory', READ_WRITE_RESET);
    }

    #[After]
    protected function removeTempFile(): void
    {
        if ($this->tmpnam && is_file($this->tmpnam)) {
            unlink($this->tmpnam);
        }
    }

    #[Test]
    public function constructAcceptsResourceAndDefaultsToMemory(): void
    {
        $resource = fopen('php://memory', READ_WRITE_RESET);
        $stream = new Stream($resource);

        $this->assertInstanceOf('Windwalker\Stream\Stream', $stream);

        $stream = new Stream();

        $this->assertIsResource(ReflectAccessor::getValue($stream, 'resource'));
        $this->assertEquals('php://memory', ReflectAccessor::getValue($stream, 'stream'));
    }

    #[Test]
    public function toStringReturnsWrittenContents(): void
    {
        $message = 'foo bar';

        $this->instance->write($message);

        $this->assertEquals($message, (string) $this->instance);
    }

    #[Test]
    public function toStringReturnsEmptyStringWhenNotReadable(): void
    {
        $this->createTempFile();

        file_put_contents($this->tmpnam, 'FOO BAR');

        $stream = new Stream($this->tmpnam, 'w');

        $this->assertEquals('', $stream->__toString());
    }

    /**
     * @throws  ReflectionException
     */
    #[Test]
    public function closeClosesResourceAndClearsContents(): void
    {
        $this->createTempFile();

        $resource = fopen($this->tmpnam, READ_WRITE_RESET);

        $stream = new Stream($resource);
        $stream->write('Foo Bar');

        $stream->close();

        $this->assertFalse(is_resource($resource));
        $this->assertEmpty(ReflectAccessor::getValue($stream, 'resource'));
        $this->assertEquals('', (string) $stream);
    }

    /**
     * @throws  ReflectionException
     */
    #[Test]
    public function detachReturnsResourceAndClearsStream(): void
    {
        $resource = fopen('php://memory', READ_WRITE_RESET);
        $stream = new Stream($resource);

        $this->assertSame($resource, $stream->detach());
        self::assertEmpty(ReflectAccessor::getValue($stream, 'resource'));
        self::assertEmpty(ReflectAccessor::getValue($stream, 'stream'));
    }

    #[Test]
    public function getSizeReturnsFileSize(): void
    {
        $this->createTempFile();

        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, READ_ONLY_FROM_BEGIN);

        $stream = new Stream($resource);

        $this->assertEquals(7, $stream->getSize());
    }

    #[Test]
    public function tellReturnsCurrentPosition(): void
    {
        $this->createTempFile();

        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, READ_WRITE_RESET);

        $stream = new Stream($resource);

        fseek($resource, 2);

        $this->assertEquals(2, $stream->tell());
    }

    #[Test]
    public function tellThrowsWhenStreamIsDetached(): void
    {
        $this->createTempFile();

        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, READ_WRITE_RESET);

        $stream = new Stream($resource);

        fseek($resource, 2);

        $stream->detach();

        $this->expectException(
            RuntimeException::class
        );

        $stream->tell();
    }

    #[Test]
    public function eofIsFalseBeforeReachingEnd(): void
    {
        $this->createTempFile();
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, READ_ONLY_FROM_BEGIN);
        $stream = new Stream($resource);

        fseek($resource, 2);
        $this->assertFalse($stream->eof());
    }

    #[Test]
    public function eofIsTrueAfterReadingToEnd(): void
    {
        $this->createTempFile();
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, READ_ONLY_FROM_BEGIN);
        $stream = new Stream($resource);

        while (!feof($resource)) {
            fread($resource, 4096);
        }

        $this->assertTrue($stream->eof());

        $stream->rewind();
        $stream->read(8);

        $this->assertTrue($stream->eof());
    }

    #[Test]
    public function eofBecomesTrueOnlyAfterReadingFromSoughtPastEnd(): void
    {
        $this->createTempFile();
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, READ_ONLY_FROM_BEGIN);
        $stream = new Stream($resource);

        $stream->seek(8);
        $this->assertFalse($stream->eof());
        $stream->read(1);
        $this->assertTrue($stream->eof());
    }

    #[Test]
    public function isSeekableIsTrueForFileResource(): void
    {
        $this->createTempFile();

        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, READ_WRITE_RESET);
        $stream = new Stream($resource);

        $this->assertTrue($stream->isSeekable());
    }

    #[Test]
    public function seekMovesPositionByWhence(): void
    {
        $this->createTempFile();
        file_put_contents($this->tmpnam, 'FOO BAR');

        $resource = fopen($this->tmpnam, READ_ONLY_FROM_BEGIN);
        $stream = new Stream($resource);

        $stream->seek(2);
        $this->assertEquals(2, $stream->tell());

        $stream->seek(2, SEEK_CUR);
        $this->assertEquals(4, $stream->tell());

        $stream->seek(-1, SEEK_END);
        $this->assertEquals(6, $stream->tell());
    }

    #[Test]
    public function rewindResetsPositionToStart(): void
    {
        $this->createTempFile();
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, READ_WRITE_RESET);

        $stream = new Stream($resource);

        $stream->seek(2);

        $stream->rewind();

        $this->assertEquals(0, $stream->tell());
    }

    #[Test]
    public function isWritableUsesOpenModeForStreamsWithoutUri(): void
    {
        $this->assertTrue(Stream::wrap(fopen('php://memory', 'w+b'))->isWritable());
        $this->assertTrue(Stream::wrap(fopen('php://temp', 'wb'))->isWritable());
    }

    #[Test]
    public function isWritableIsFalseForReadOnlyMode(): void
    {
        $stream = new Stream('php://memory', READ_ONLY_FROM_BEGIN);

        $this->assertFalse($stream->isWritable());
    }

    #[Test]
    public function writeAppendsAndOverwritesAtPosition(): void
    {
        $this->createTempFile();
        $resource = fopen($this->tmpnam, READ_WRITE_RESET);

        $stream = new Stream($resource);
        $stream->write('flower');

        $this->assertEquals('flower', file_get_contents($this->tmpnam));

        $stream->write(' bloom');

        $this->assertEquals('flower bloom', file_get_contents($this->tmpnam));

        $stream->seek(4);

        $stream->write('test');

        $this->assertEquals('flowtestloom', file_get_contents($this->tmpnam));
    }

    #[Test]
    public function isReadableIsFalseForWriteOnlyMode(): void
    {
        $this->createTempFile();

        $stream = new Stream($this->tmpnam, WRITE_ONLY_RESET);
        $this->assertFalse($stream->isReadable());
    }

    #[Test]
    public function readReturnsSequentialChunksAndRestartsAfterRewind(): void
    {
        $this->createTempFile();
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, READ_ONLY_FROM_BEGIN);

        $stream = new Stream($resource);

        $this->assertEquals('FO', $stream->read(2));
        $this->assertEquals('O B', $stream->read(3));

        $stream->rewind();

        $this->assertEquals('FOO', $stream->read(3));
    }

    #[Test]
    public function getContentsReturnsWholeFile(): void
    {
        $this->createTempFile();
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, READ_ONLY_FROM_BEGIN);

        $stream = new Stream($resource);

        $this->assertEquals('FOO BAR', $stream->getContents());
    }

    #[Test]
    public function getMetadataReturnsAllOrSingleKeyOfAttachedResource(): void
    {
        $this->createTempFile();
        $resource = fopen($this->tmpnam, READ_WRITE_FROM_BEGIN);
        $this->instance->attach($resource);

        $this->assertEquals(stream_get_meta_data($resource), $this->instance->getMetadata());

        $this->assertEquals(READ_WRITE_FROM_BEGIN, $this->instance->getMetadata('mode'));

        fclose($resource);
    }

    #[Test]
    public function fromStringWritesContentsIntoMemoryStream(): void
    {
        $stream = Stream::fromString('FOO BAR');
        $stream->rewind();

        $this->assertEquals('FOO BAR', $stream->getContents());
    }

    #[Test]
    public function fromFilePathOpensFile(): void
    {
        $this->createTempFile();
        file_put_contents($this->tmpnam, 'FOO BAR');

        $stream = Stream::fromFilePath($this->tmpnam, READ_ONLY_FROM_BEGIN);

        $this->assertEquals('FOO BAR', $stream->getContents());
    }

    #[Test]
    public function wrapReturnsExistingStreamOrCreatesNewOne(): void
    {
        $this->assertSame($this->instance, Stream::wrap($this->instance));
        $this->assertInstanceOf(Stream::class, Stream::wrap('php://memory'));
    }

    #[Test]
    public function attachRejectsInvalidResource(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Invalid resource.');

        $this->instance->attach(123);
    }

    #[Test]
    public function closeDoesNothingWhenNoResource(): void
    {
        $this->instance->detach();
        $this->instance->close();

        $this->assertNull($this->instance->getResource());
    }

    #[Test]
    public function getSizeReturnsNullWithoutResource(): void
    {
        $this->instance->detach();

        $this->assertNull($this->instance->getSize());
    }

    #[Test]
    public function stateIsUnavailableWithoutResource(): void
    {
        $this->instance->detach();

        $this->assertTrue($this->instance->eof());
        $this->assertFalse($this->instance->isSeekable());
        $this->assertFalse($this->instance->isWritable());
        $this->assertFalse($this->instance->isReadable());
        $this->assertSame('', $this->instance->getContents());
        $this->assertSame('', (string) $this->instance);
    }

    #[Test]
    #[DataProvider('operationsWithoutResourceProvider')]
    public function operationsThrowWithoutResource(string $method, array $args): void
    {
        $this->instance->detach();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('No resource available.');

        $this->instance->$method(...$args);
    }

    public static function operationsWithoutResourceProvider(): array
    {
        return [
            'tell' => ['tell', []],
            'seek' => ['seek', [0]],
            'rewind' => ['rewind', []],
            'write' => ['write', ['x']],
            'read' => ['read', [1]],
        ];
    }

    #[Test]
    public function seekThrowsWhenStreamIsNotSeekable(): void
    {
        $stream = new Stream('php://output', WRITE_ONLY_RESET);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Stream is not seekable');

        $stream->seek(0);
    }

    #[Test]
    public function seekThrowsWhenSeekFails(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Error seeking within stream');

        $this->instance->seek(-1);
    }

    #[Test]
    public function readThrowsWhenStreamIsNotReadable(): void
    {
        $this->createTempFile();
        $stream = new Stream($this->tmpnam, WRITE_ONLY_RESET);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Stream is not readable');

        $stream->read(1);
    }

    #[Test]
    public function getMetadataReturnsNullForUnknownKey(): void
    {
        $this->assertNull($this->instance->getMetadata('not-exists'));
    }

    protected function createTempFile(): string
    {
        return $this->tmpnam = tempnam(sys_get_temp_dir(), 'windwalker');
    }
}
