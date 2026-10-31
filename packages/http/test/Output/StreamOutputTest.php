<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Output;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\Response\Response;
use Windwalker\Http\Test\Stub\StubStreamOutput;
use Windwalker\Stream\Stream;

class StreamOutputTest extends TestCase
{
    #[Test]
    public function sendBodyWritesWholeBodyWhenBufferLengthIsNotPositive(): void
    {
        $output = new StubStreamOutput();
        $output->setMaxBufferLength(0);

        $output->sendBody(Response::fromString('Whole body'));

        self::assertSame('Whole body', $output->output);
    }

    #[Test]
    public function sendBodyReadsBodyInChunksAndDelaysBetweenThem(): void
    {
        $output = new StubStreamOutput();
        $output->setMaxBufferLength(4)->setDelay(10);

        $output->sendBody(Response::fromString('0123456789'));

        self::assertSame('0123456789', $output->output);
        self::assertSame(30, $output->waiting);
    }

    #[Test]
    public function sendBodyRewindsSeekableBodyBeforeReading(): void
    {
        $output = new StubStreamOutput();
        $response = Response::fromString('Rewind');
        $response->getBody()->seek(3);

        $output->sendBody($response);

        self::assertSame('Rewind', $output->output);
    }

    #[Test]
    #[DataProvider('contentRangeProvider')]
    public function sendBodyOnlyWritesRequestedContentRange(int $buffer, string $header, string $expected): void
    {
        $output = new StubStreamOutput();
        $output->setMaxBufferLength($buffer);

        $response = Response::fromString('0123456789', 206, ['Content-Range' => $header]);
        $output->sendBody($response);

        self::assertSame($expected, $output->output);
    }

    public static function contentRangeProvider(): array
    {
        return [
            'buffer larger than range' => [8192, 'bytes 2-5/10', '2345'],
            'buffer smaller than range' => [3, 'bytes 2-5/10', '2345'],
            'buffer matching range' => [2, 'bytes 0-3/*', '0123'],
            'unknown total length' => [4, 'bytes 6-9/*', '6789'],
        ];
    }

    #[Test]
    public function sendBodyIgnoresMalformedContentRange(): void
    {
        $output = new StubStreamOutput();

        $output->sendBody(Response::fromString('0123456789', 200, ['Content-Range' => 'invalid']));

        self::assertSame('0123456789', $output->output);
    }

    #[Test]
    public function sendBodyDoesNotDelayWhenDelayIsNotSet(): void
    {
        $output = new StubStreamOutput();
        $output->setMaxBufferLength(2);

        $output->sendBody(Response::fromString('abcd'));

        self::assertNull($output->waiting);
    }

    #[Test]
    public function prepareContentLengthAddsHeaderFromBodySizeUnlessPresent(): void
    {
        $response = Response::fromString('12345');

        self::assertSame('5', StubStreamOutput::prepareContentLength($response)->getHeaderLine('content-length'));

        $withLength = $response->withHeader('content-length', '99');

        self::assertSame($withLength, StubStreamOutput::prepareContentLength($withLength));
    }

    #[Test]
    public function prepareContentLengthKeepsResponseWhenBodySizeIsUnknown(): void
    {
        $body = new class () extends Stream {
            public function __construct()
            {
                parent::__construct('php://memory', 'wb+');
            }

            public function getSize(): ?int
            {
                return null;
            }
        };
        $response = (new Response())->withBody($body);

        self::assertFalse(StubStreamOutput::prepareContentLength($response)->hasHeader('content-length'));
    }
}
