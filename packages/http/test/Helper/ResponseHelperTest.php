<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Helper;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use ReflectionProperty;
use Windwalker\Http\Helper\ResponseHelper;
use Windwalker\Http\Output\StreamOutput;
use Windwalker\Http\Response\Response;
use Windwalker\Stream\Stream;

class ResponseHelperTest extends TestCase
{
    #[After]
    public function resetOutputObject(): void
    {
        (new ReflectionProperty(ResponseHelper::class, 'outputObject'))->setValue(null, null);
    }

    #[Test]
    public function getPhraseReturnsPhraseOrNull(): void
    {
        self::assertSame('OK', ResponseHelper::getPhrase(200));
        self::assertSame('Not Found', ResponseHelper::getPhrase(404));
        self::assertNull(ResponseHelper::getPhrase(799));
    }

    #[Test]
    #[DataProvider('statusProvider')]
    public function validatesStatusCodes(int|string $code, bool $expected): void
    {
        self::assertSame($expected, ResponseHelper::validateStatus($code));
    }

    public static function statusProvider(): array
    {
        return [
            [100, true],
            [599, true],
            ['404', true],
            [99, false],
            [600, false],
            ['abc', false],
        ];
    }

    #[Test]
    public function inRangeDefaultsEndToNextHundred(): void
    {
        self::assertTrue(ResponseHelper::inRange(200, 200));
        self::assertTrue(ResponseHelper::inRange(299, 200));
        self::assertFalse(ResponseHelper::inRange(300, 200));
        self::assertTrue(ResponseHelper::inRange(15, 10, 20));
        self::assertFalse(ResponseHelper::inRange(20, 10, 20));
    }

    #[Test]
    public function inRangeRejectsEndSmallerThanStart(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ResponseHelper::inRange(10, 20, 10);
    }

    #[Test]
    public function classifiesStatusFamilies(): void
    {
        self::assertTrue(ResponseHelper::isSuccess(204));
        self::assertFalse(ResponseHelper::isSuccess(302));
        self::assertTrue(ResponseHelper::isRedirect(301));
        self::assertFalse(ResponseHelper::isRedirect(200));
        self::assertTrue(ResponseHelper::isClientError(404));
        self::assertFalse(ResponseHelper::isClientError(500));
        self::assertTrue(ResponseHelper::isServerError(503));
        self::assertFalse(ResponseHelper::isServerError(404));
    }

    #[Test]
    public function sendAttachmentRespondsWithDownloadHeadersAndFilenameFromPath(): void
    {
        $file = __FILE__;
        $captured = $this->captureResponse();

        ResponseHelper::sendAttachment($file, null, ['delay' => 5]);

        $response = $captured->response;

        self::assertSame([5], $captured->delays);
        self::assertSame('application/octet-stream', $response->getHeaderLine('Content-Type'));
        self::assertSame(
            "attachment; filename*=utf-8''ResponseHelperTest.php",
            $response->getHeaderLine('Content-Disposition')
        );
    }

    #[Test]
    public function sendAttachmentPrefersFilenameOptionAndCustomResponse(): void
    {
        $captured = $this->captureResponse();
        $stream = new Stream('php://memory', 'rb+');
        $stream->write('data');

        ResponseHelper::sendAttachment(
            $stream,
            new Response('php://memory', 201),
            ['filename' => 'custom.bin']
        );

        $response = $captured->response;

        self::assertSame([], $captured->delays);
        self::assertSame(201, $response->getStatusCode());
        self::assertSame($stream, $response->getBody());
        self::assertSame(
            "attachment; filename*=utf-8''custom.bin",
            $response->getHeaderLine('Content-Disposition')
        );
    }

    private function captureResponse(): object
    {
        $captured = new class () extends StreamOutput {
            public ?ResponseInterface $response = null;

            public array $delays = [];

            public function __destruct() {}

            public function setDelay(?int $delay): static
            {
                $this->delays[] = $delay;

                return $this;
            }

            public function respond(ResponseInterface $response): void
            {
                $this->response = $response;
            }
        };

        (new ReflectionProperty(ResponseHelper::class, 'outputObject'))->setValue(null, $captured);

        return $captured;
    }
}
