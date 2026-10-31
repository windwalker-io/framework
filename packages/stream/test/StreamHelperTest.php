<?php

declare(strict_types=1);

namespace Windwalker\Stream\Test;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Stream\Stream;
use Windwalker\Stream\StreamHelper;
use Windwalker\Stream\StringStream;

class StreamHelperTest extends TestCase
{
    private ?string $tmpnam = null;

    #[After]
    protected function removeTempFile(): void
    {
        if ($this->tmpnam && is_file($this->tmpnam)) {
            unlink($this->tmpnam);
        }
    }

    #[Test]
    public function copyWritesWholeSourceToDestination(): void
    {
        $src = new StringStream('FOO BAR');
        $dest = new StringStream();

        StreamHelper::copy($src, $dest);

        $this->assertEquals('FOO BAR', $dest->getResource());
    }

    #[Test]
    public function copyRewindsSeekableSourceBeforeCopying(): void
    {
        $src = new StringStream('FOO BAR');
        $src->seek(4);
        $dest = new StringStream();

        StreamHelper::copy($src, $dest);

        $this->assertEquals('FOO BAR', $dest->getResource());
    }

    #[Test]
    public function copyReadsInChunksForLargeSource(): void
    {
        $content = str_repeat('abcdefghij', 1000);
        $src = new StringStream($content);
        $dest = new StringStream();

        StreamHelper::copy($src, $dest);

        $this->assertEquals($content, $dest->getResource());
    }

    #[Test]
    public function copyToWritesSourceIntoPathAndClosesDestination(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'windwalker');

        StreamHelper::copyTo(new StringStream('FOO BAR'), $this->tmpnam);

        $this->assertEquals('FOO BAR', file_get_contents($this->tmpnam));
    }

    #[Test]
    public function copyFromReadsPathIntoDestination(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'windwalker');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $dest = new StringStream();

        StreamHelper::copyFrom($this->tmpnam, $dest);

        $this->assertEquals('FOO BAR', $dest->getResource());
    }

    #[Test]
    public function copyFromAcceptsStreamSourceAndClosesIt(): void
    {
        $src = new Stream();
        $src->write('FOO BAR');
        $dest = new StringStream();

        StreamHelper::copyFrom($src, $dest);

        $this->assertEquals('FOO BAR', $dest->getResource());
        $this->assertNull($src->getResource());
    }

    #[Test]
    public function copyToStringReadsAllRemainingContents(): void
    {
        $stream = new StringStream('FOO BAR');
        $stream->seek(4);

        $this->assertEquals('BAR', StreamHelper::copyToString($stream));
    }

    #[Test]
    public function copyToStringStopsAtMaxLength(): void
    {
        $stream = new StringStream('FOO BAR');

        $this->assertEquals('FOO ', StreamHelper::copyToString($stream, 4));
        $this->assertEquals('BAR', StreamHelper::copyToString($stream, 10));
    }

    #[Test]
    public function copyToStringReturnsEmptyStringAtEof(): void
    {
        $stream = new StringStream('');

        $this->assertSame('', StreamHelper::copyToString($stream));
        $this->assertSame('', StreamHelper::copyToString($stream, 5));
    }
}
