<?php

declare(strict_types=1);

namespace Windwalker\Stream\Test;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use RuntimeException;
use Stringable;
use stdClass;
use Windwalker\Stream\StringStream;
use Windwalker\Utilities\Reflection\ReflectAccessor;

use const Windwalker\Stream\READ_ONLY_FROM_BEGIN;
use const Windwalker\Stream\READ_WRITE_FROM_BEGIN;

/**
 * Test class of Stream
 *
 * @since 2.1
 */
class StringStreamTest extends TestCase
{
    /**
     * Test instance.
     *
     * @var  StringStream
     */
    protected $instance;

    /**
     * Property tmp.
     *
     * @var  string
     */
    public $tmpnam;

    #[Before]
    protected function createWritableStringStream(): void
    {
        $this->instance = new StringStream('', READ_WRITE_FROM_BEGIN);
    }

    #[After]
    protected function removeTempFile(): void
    {
        if ($this->tmpnam && is_file($this->tmpnam)) {
            unlink($this->tmpnam);
        }
    }

    /**
     * @throws  ReflectionException
     */
    #[Test]
    public function constructWrapsStringableObjectAsResource(): void
    {
        $stringObject = new class () implements Stringable {
            public function __toString(): string
            {
                return 'FOO';
            }
        };

        $stream = new StringStream($stringObject);

        $this->assertEquals('FOO', ReflectAccessor::getValue($stream, 'resource'));
        $this->assertIsObject(ReflectAccessor::getValue($stream, 'stream'));
    }

    #[Test]
    public function toStringReturnsWrittenContents(): void
    {
        $message = 'foo bar';

        $this->instance->write($message);

        $this->assertEquals($message, (string) $this->instance);
    }

    /**
     * @throws  ReflectionException
     */
    #[Test]
    public function closeClearsResourceAndContents(): void
    {
        $stream = new StringStream();
        $stream->write('Foo Bar');

        $stream->close();

        $this->assertEmpty($stream->getResource());
        self::assertEmpty(ReflectAccessor::getValue($stream, 'stream'));
        $this->assertEquals('', (string) $stream);
    }

    /**
     * @throws  ReflectionException
     */
    #[Test]
    public function detachReturnsResourceAndClearsStream(): void
    {
        $stream = new StringStream('flower');

        $this->assertEquals('flower', $stream->detach());
        self::assertEmpty(ReflectAccessor::getValue($stream, 'resource'));
        self::assertEmpty(ReflectAccessor::getValue($stream, 'stream'));
    }

    #[Test]
    public function getSizeReturnsStringLength(): void
    {
        $stream = new StringStream('FOO BAR');

        $this->assertEquals(7, $stream->getSize());
    }

    #[Test]
    public function tellReturnsCurrentPosition(): void
    {
        $stream = new StringStream('FOO BAR');

        $stream->seek(2);

        $this->assertEquals(2, $stream->tell());
    }

    #[Test]
    public function tellThrowsWhenStreamIsDetached(): void
    {
        $stream = new StringStream('FOO BAR');

        $stream->seek(2);
        $stream->detach();

        $this->expectException(RuntimeException::class);

        $stream->tell();
    }

    #[Test]
    public function eofIsFalseWhenPartiallyRead(): void
    {
        $stream = new StringStream('FOO BAR');

        $stream->read(2);
        $this->assertFalse($stream->eof());
    }

    #[Test]
    public function eofIsTrueWhenReadingPastEnd(): void
    {
        $stream = new StringStream('FOO BAR');

        $stream->read(8);
        $this->assertTrue($stream->eof());

        $stream->rewind();
        $stream->read(4096);
        $this->assertTrue($stream->eof());
    }

    #[Test]
    public function eofBecomesTrueOnlyAfterReadingFromSoughtPastEnd(): void
    {
        $stream = new StringStream('FOO BAR');

        $stream->seek(8);
        $this->assertFalse($stream->eof());
        $stream->read(1);
        $this->assertTrue($stream->eof());
    }

    #[Test]
    public function isSeekableIsTrueByDefault(): void
    {
        $stream = new StringStream('FOO BAR');

        $this->assertTrue($stream->isSeekable());
    }

    #[Test]
    public function isSeekableIsFalseAfterDisablingSeekable(): void
    {
        $stream = new StringStream('FOO BAR');

        $stream->seekable(false);

        $this->assertFalse($stream->isSeekable());
    }

    #[Test]
    public function seekMovesPositionByWhence(): void
    {
        $stream = new StringStream('FOO BAR');

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
        $stream = new StringStream('FOO BAR');

        $stream->seek(2);

        $stream->rewind();

        $this->assertEquals(0, $stream->tell());
    }

    #[Test]
    public function isWritableIsFalseForReadOnlyMode(): void
    {
        $stream = new StringStream('php://memory', READ_ONLY_FROM_BEGIN);

        $this->assertFalse($stream->isWritable());
    }

    #[Test]
    public function writeAppendsAndOverwritesAtPosition(): void
    {
        $stream = new StringStream('');
        $stream->write('flower');

        $this->assertEquals('flower', $stream->getResource());

        $stream->write(' bloom');

        $this->assertEquals('flower bloom', $stream->getResource());

        $stream->seek(4);

        $stream->write('test');

        $this->assertEquals('flowtestloom', $stream->getResource());
    }

    #[Test]
    public function isReadableIsTrueByDefault(): void
    {
        $stream = new StringStream();
        $this->assertTrue($stream->isReadable());
    }

    #[Test]
    public function readReturnsSequentialChunksAndRestartsAfterRewind(): void
    {
        $stream = new StringStream('FOO BAR');

        $this->assertEquals('FO', $stream->read(2));
        $this->assertEquals('O B', $stream->read(3));

        $stream->rewind();

        $this->assertEquals('FOO', $stream->read(3));
    }

    #[Test]
    public function getContentsReturnsWholeString(): void
    {
        $stream = new StringStream('FOO BAR');

        $this->assertEquals('FOO BAR', $stream->getContents());
    }

    #[Test]
    public function getContentsReturnsRemainingStringFromPosition(): void
    {
        $stream = new StringStream('FOO BAR');

        $stream->seek(2);

        $this->assertEquals('O BAR', $stream->getContents());
        $this->assertSame(7, $stream->tell());
        $this->assertSame('', $stream->getContents());
    }

    #[Test]
    public function getMetadataReturnsAllOrSingleKey(): void
    {
        $this->assertIsArray($this->instance->getMetadata());

        $this->assertTrue($this->instance->getMetadata('seekable'));
        $this->assertEquals('rb+', $this->instance->getMetadata('mode'));
    }

    #[Test]
    public function writableDependsOnAttachMode(): void
    {
        $this->assertTrue(new StringStream('A', READ_WRITE_FROM_BEGIN)->isWritable());
        $this->assertTrue(new StringStream('A', 'wb')->isWritable());
        $this->assertFalse(new StringStream('A', READ_ONLY_FROM_BEGIN)->isWritable());
    }

    #[Test]
    public function getMetadataReturnsNullForUnknownKey(): void
    {
        $this->assertNull($this->instance->getMetadata('not-exists'));
    }

    #[Test]
    public function attachRejectsResource(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('StringStream do not support resource.');

        new StringStream(fopen('php://memory', 'rb'));
    }

    #[Test]
    public function attachRejectsArrayAndNonStringableObject(): void
    {
        foreach ([['foo'], new stdClass()] as $value) {
            try {
                new StringStream($value);
                $this->fail('Expected InvalidArgumentException');
            } catch (InvalidArgumentException $e) {
                $this->assertEquals('StringStream only support string as resource.', $e->getMessage());
            }
        }
    }

    #[Test]
    public function getSizeReturnsNullAfterClose(): void
    {
        $stream = new StringStream('FOO');
        $stream->close();

        $this->assertNull($stream->getSize());
    }

    #[Test]
    public function getContentsReturnsEmptyStringAfterClose(): void
    {
        $stream = new StringStream('FOO');
        $stream->close();

        $this->assertSame('', $stream->getContents());
    }

    #[Test]
    public function seekThrowsWhenNotSeekable(): void
    {
        $stream = new StringStream('FOO BAR');
        $stream->seekable(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Stream is not seekable');

        $stream->seek(1);
    }

    #[Test]
    public function seekThrowsWhenPositionIsNegative(): void
    {
        $stream = new StringStream('FOO BAR');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Position should not less than 0.');

        $stream->seek(-1);
    }

    #[Test]
    public function writableControlsIsWritable(): void
    {
        $stream = new StringStream('FOO', READ_WRITE_FROM_BEGIN);

        $this->assertSame($stream, $stream->writable(false));
        $this->assertFalse($stream->isWritable());

        $stream->writable(true);

        $this->assertTrue($stream->isWritable());
    }

    #[Test]
    public function writableFalseBlocksWrite(): void
    {
        $stream = new StringStream('FOO', READ_WRITE_FROM_BEGIN);
        $stream->writable(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Stream is not writable');

        $stream->write('BAR');
    }
}
