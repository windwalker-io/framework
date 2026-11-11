<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Transport;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Windwalker\Http\Exception\HttpResponseSizeExceededException;
use Windwalker\Http\Transport\ResponseSizeLimitTrait;
use Windwalker\Stream\Stream;

class ResponseSizeLimitTraitTest extends TestCase
{
    private function createSubject(): object
    {
        return new class () {
            use ResponseSizeLimitTrait {
                assertResponseSizeWithinLimit as public;
                readContentsWithLimit as public;
                copyToWithLimit as public;
            }
        };
    }

    private function createSource(string $content): StreamInterface
    {
        $stream = new Stream('php://temp', 'rb+');
        $stream->write($content);
        $stream->rewind();

        return $stream;
    }

    #[Test]
    public function assertAllowsSizeWithinLimitOrNoLimit(): void
    {
        $subject = $this->createSubject();

        $subject->assertResponseSizeWithinLimit(10, 10);
        $subject->assertResponseSizeWithinLimit(PHP_INT_MAX, null);

        $this->addToAssertionCount(2);
    }

    #[Test]
    public function assertThrowsWhenSizeExceedsLimit(): void
    {
        $this->expectException(HttpResponseSizeExceededException::class);
        $this->expectExceptionMessage('1024');

        $this->createSubject()->assertResponseSizeWithinLimit(1025, 1024);
    }

    #[Test]
    public function readContentsWithLimitReturnsContentsWithinLimit(): void
    {
        $subject = $this->createSubject();

        self::assertSame('hello', $subject->readContentsWithLimit($this->createSource('hello'), 5));
        self::assertSame('hello', $subject->readContentsWithLimit($this->createSource('hello'), null));
    }

    #[Test]
    public function readContentsWithLimitThrowsWhenExceeded(): void
    {
        $this->expectException(HttpResponseSizeExceededException::class);

        $this->createSubject()->readContentsWithLimit($this->createSource('hello!'), 5);
    }

    #[Test]
    public function copyToWithLimitCopiesAllWhenWithinLimit(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'wwtest');
        $content = str_repeat('a', 10000);

        try {
            $this->createSubject()->copyToWithLimit($this->createSource($content), $file, 10000);

            self::assertSame($content, file_get_contents($file));
        } finally {
            @unlink($file);
        }
    }

    #[Test]
    public function copyToWithLimitWithoutLimitCopiesAll(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'wwtest');

        try {
            $this->createSubject()->copyToWithLimit($this->createSource('hello'), $file, null);

            self::assertSame('hello', file_get_contents($file));
        } finally {
            @unlink($file);
        }
    }

    #[Test]
    public function copyToWithLimitThrowsAndStopsWritingWhenExceeded(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'wwtest');

        try {
            $this->createSubject()->copyToWithLimit(
                $this->createSource(str_repeat('a', 10000)),
                $file,
                4096,
            );
            self::fail('Expected HttpResponseSizeExceededException.');
        } catch (HttpResponseSizeExceededException) {
            self::assertLessThanOrEqual(4096, filesize($file));
        } finally {
            @unlink($file);
        }
    }
}
