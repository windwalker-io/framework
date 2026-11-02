<?php

declare(strict_types=1);

namespace Windwalker\Stream\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Stream\DecoratorStream;
use Windwalker\Stream\StringStream;

class DecoratorStreamTest extends TestCase
{
    #[Test]
    public function constructAcceptsInnerStream(): void
    {
        $inner = new StringStream('FOO');

        $this->assertSame($inner, new DecoratorStream($inner)->getInnerStream());
    }

    #[Test]
    public function setInnerStreamReplacesTarget(): void
    {
        $decorator = new DecoratorStream(new StringStream('FOO'));
        $other = new StringStream('BAR');

        $this->assertSame($decorator, $decorator->setInnerStream($other));
        $this->assertSame($other, $decorator->getInnerStream());
        $this->assertEquals('BAR', $decorator->getContents());
    }

    #[Test]
    public function toStringRewindsSeekableStreamBeforeReading(): void
    {
        $inner = new StringStream('FOO BAR');
        $inner->seek(4);

        $this->assertEquals('FOO BAR', (string) new DecoratorStream($inner));
    }

    #[Test]
    public function toStringReadsFromCurrentPositionWhenNotSeekable(): void
    {
        $inner = new StringStream('FOO BAR');
        $inner->seek(4);
        $inner->seekable(false);

        $this->assertEquals('BAR', (string) new DecoratorStream($inner));
    }

    #[Test]
    public function rewindSeeksInnerStreamToStart(): void
    {
        $inner = new StringStream('FOO BAR');
        $inner->seek(4);

        new DecoratorStream($inner)->rewind();

        $this->assertEquals(0, $inner->tell());
    }

    #[Test]
    public function operationsAreForwardedToInnerStream(): void
    {
        $inner = new StringStream('FOO BAR');
        $decorator = new DecoratorStream($inner);

        $this->assertEquals(7, $decorator->getSize());
        $this->assertEquals('FOO', $decorator->read(3));
        $this->assertEquals(3, $decorator->tell());
        $this->assertFalse($decorator->eof());
        $this->assertTrue($decorator->isSeekable());
        $this->assertTrue($decorator->isReadable());
        $this->assertSame($inner->isWritable(), $decorator->isWritable());
        $this->assertEquals(3, $decorator->write('XYZ'));
        $this->assertEquals('FOOXYZR', $inner->getResource());
        $this->assertEquals($inner->getMetadata('mode'), $decorator->getMetadata('mode'));
        $this->assertEquals('FOOXYZR', $decorator->detach());
    }

    #[Test]
    public function closeClosesInnerStream(): void
    {
        $inner = new StringStream('FOO');

        new DecoratorStream($inner)->close();

        $this->assertEmpty($inner->getResource());
    }
}
