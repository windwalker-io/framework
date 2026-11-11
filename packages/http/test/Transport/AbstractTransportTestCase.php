<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Transport;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Windwalker\Http\Exception\HttpRequestException;
use Windwalker\Http\Exception\HttpResponseSizeExceededException;
use Windwalker\Http\Request\Request;
use Windwalker\Http\Transport\AbstractTransport;
use Windwalker\Stream\Stream;
use Windwalker\Stream\StringStream;
use Windwalker\Test\Traits\BaseAssertionTrait;
use Windwalker\Uri\Uri;
use Windwalker\Uri\UriHelper;

/**
 * Shared transport behavior tests run against each concrete transport.
 */
abstract class AbstractTransportTestCase extends TestCase
{
    use BaseAssertionTrait;
    use TestServerExistsTrait;

    protected array $options = [
        'options' => [],
    ];

    protected AbstractTransport $instance;

    abstract protected function createTransport(): AbstractTransport;

    #[BeforeClass]
    public static function ensureTestServerIsRunning(): void
    {
        self::checkTestServerRunningOrSkip();
    }

    #[Before]
    protected function createTransportOrSkipIfUnsupported(): void
    {
        $this->instance = $this->createTransport();

        if (!$this->instance::isSupported()) {
            $this->markTestSkipped(get_class($this->instance) . ' driver not supported.');
        }
    }

    protected function createRequest(?StreamInterface $stream = null): Request
    {
        return new Request($stream ?: new StringStream());
    }

    #[Test]
    public function requestGetReturnsJsonResponseWithOkStatus(): void
    {
        $request = $this->createRequest();

        $request = $request
            ->withUri(new Uri(static::getTestUrlFromConstant() . '/json?foo=bar'))
            ->withMethod('GET');

        $response = $this->instance->request($request);

        self::assertEquals(200, $response->getStatusCode());
        self::assertJson($response->getBody()->getContents());
    }

    #[Test]
    public function requestGetParsesNestedQueryParametersOnServer(): void
    {
        $request = $this->createRequest();

        $request = $request
            ->withUri(new Uri(static::getTestUrlFromConstant() . '/json?foo=bar&baz[3]=yoo'))
            ->withMethod('GET');

        $response = $this->instance->request($request);

        $data = json_decode($response->getBody()->getContents(), true);
        self::assertEquals(['foo' => 'bar', 'baz' => [3 => 'yoo']], $data);
    }

    #[Test]
    public function requestBodyWithinMaxResponseSizeReturnsFullBody(): void
    {
        $request = $this->createRequest()
            ->withUri(new Uri(static::getTestUrlFromConstant() . '/big'))
            ->withMethod('GET');

        $response = $this->instance->request($request, ['maxResponseSize' => 65536]);

        self::assertSame(65536, strlen((string) $response->getBody()));
    }

    #[Test]
    public function requestBodyExceedingMaxResponseSizeThrowsException(): void
    {
        $request = $this->createRequest()
            ->withUri(new Uri(static::getTestUrlFromConstant() . '/big'))
            ->withMethod('GET');

        $this->expectException(HttpResponseSizeExceededException::class);

        $this->instance->request($request, ['maxResponseSize' => 1024]);
    }

    #[Test]
    public function requestBodyExceedingMaxResponseSizeIsNotWrittenToWriteStream(): void
    {
        $request = $this->createRequest()
            ->withUri(new Uri(static::getTestUrlFromConstant() . '/big'))
            ->withMethod('GET');

        $sink = new Stream('php://temp', 'rb+');

        try {
            $this->instance->request($request, ['maxResponseSize' => 1024, 'writeStream' => $sink]);
            self::fail('Expected HttpResponseSizeExceededException.');
        } catch (HttpResponseSizeExceededException) {
            self::assertLessThanOrEqual(1024, (int) $sink->getSize());
        }
    }

    #[Test]
    public function requestChunkedBodyExceedingMaxResponseSizeThrowsException(): void
    {
        $request = $this->createRequest()
            ->withUri(new Uri(static::getTestUrlFromConstant() . '/big-chunked'))
            ->withMethod('GET');

        $this->expectException(HttpResponseSizeExceededException::class);

        $this->instance->request($request, ['maxResponseSize' => 4096]);
    }

    #[Test]
    public function requestWriteHandlerReceivesAllChunks(): void
    {
        $request = $this->createRequest()
            ->withUri(new Uri(static::getTestUrlFromConstant() . '/big'))
            ->withMethod('GET');

        $received = '';

        $response = $this->instance->request(
            $request,
            ['writeHandler' => static function (string $chunk) use (&$received): void {
                $received .= $chunk;
            }],
        );

        self::assertSame(65536, strlen($received));
        self::assertSame('', (string) $response->getBody());
    }

    #[Test]
    public function requestWriteHandlerWithWriteStreamAlsoWritesToStream(): void
    {
        $request = $this->createRequest()
            ->withUri(new Uri(static::getTestUrlFromConstant() . '/big'))
            ->withMethod('GET');

        $file = tempnam(sys_get_temp_dir(), 'wwtest');
        $count = 0;

        try {
            $this->instance->request(
                $request,
                [
                    'writeStream' => $file,
                    'writeHandler' => static function (string $chunk) use (&$count): void {
                        $count += strlen($chunk);
                    },
                ],
            );

            self::assertSame(65536, $count);
            self::assertSame(65536, filesize($file));
        } finally {
            @unlink($file);
        }
    }

    #[Test]
    public function requestWriteHandlerReturningFalseAbortsDownload(): void
    {
        $request = $this->createRequest()
            ->withUri(new Uri(static::getTestUrlFromConstant() . '/big'))
            ->withMethod('GET');

        $this->expectException(HttpRequestException::class);
        $this->expectExceptionMessage('writeHandler');

        $this->instance->request($request, ['writeHandler' => static fn(string $chunk) => false]);
    }

    #[Test]
    public function requestWriteHandlerRespectsMaxResponseSize(): void
    {
        $request = $this->createRequest()
            ->withUri(new Uri(static::getTestUrlFromConstant() . '/big-chunked'))
            ->withMethod('GET');

        $this->expectException(HttpResponseSizeExceededException::class);

        $this->instance->request(
            $request,
            ['maxResponseSize' => 4096, 'writeHandler' => static function (string $chunk): void {}],
        );
    }

    #[Test]
    public function requestToUnresolvableDomainThrowsRuntimeException(): void
    {
        $this->expectException(RuntimeException::class);

        $request = $this->createRequest();

        $request = $request
            ->withUri(new Uri('http://not.exists.url/flower.sakura'))
            ->withMethod('GET');

        $this->instance->request($request);
    }

    #[Test]
    public function requestToUnknownPathReturnsNotFoundResponse(): void
    {
        $request = $this->createRequest();

        $request = $request
            ->withUri(new Uri(static::getTestUrlFromConstant() . '/wrong'))
            ->withMethod('POST');

        $request->getBody()->write(UriHelper::buildQuery(['foo' => 'bar']));

        $response = $this->instance->request($request);

        self::assertEquals(404, $response->getStatusCode());
        self::assertEquals('Not Found', $response->getReasonPhrase());
    }

    #[Test]
    public function requestPostSendsFormBodyToServer(): void
    {
        $request = $this->createRequest();

        $request = $request
            ->withUri(new Uri(static::getTestUrlFromConstant()))
            ->withMethod('POST');

        $request->getBody()->write(UriHelper::buildQuery(['foo' => 'bar']));

        $response = $this->instance->request($request);

        self::assertStringSafeEquals(
            'foo=bar',
            $response->getBody()->getContents(),
        );
    }

    #[Test]
    public function requestPutSendsFormBodyToServer(): void
    {
        $request = $this->createRequest();

        $request = $request
            ->withUri(new Uri(static::getTestUrlFromConstant()))
            ->withMethod('PUT');

        $request->getBody()->write(UriHelper::buildQuery(['foo' => 'bar']));

        $response = $this->instance->request($request);

        self::assertStringSafeEquals(
            'foo=bar',
            $response->getBody()->getContents(),
        );
    }

    #[Test]
    public function requestWithUserInfoSendsBasicAuthCredentials(): void
    {
        $request = $this->createRequest();

        $uri = new Uri(static::getTestUrlFromConstant() . '/auth');
        $uri = $uri->withUserInfo('username', 'pass1234');

        $request = $request
            ->withUri($uri)
            ->withMethod('GET');

        $response = $this->instance->request($request);

        self::assertStringSafeEquals(
            'username:pass1234',
            $response->getBody()->getContents(),
        );
    }

    #[Test]
    public function requestPostSendsRawStringBodyToServer(): void
    {
        $request = $this->createRequest();

        $request = $request
            ->withUri(new Uri(static::getTestUrlFromConstant() . '?foo=bar'))
            ->withMethod('POST');

        $request->getBody()->write('flower=sakura');

        $response = $this->instance->request($request);

        self::assertStringSafeEquals(
            'flower=sakura',
            $response->getBody()->getContents(),
        );
    }

    #[Test]
    public function downloadWritesResponseBodyToDestinationFile(): void
    {
        $root = vfsStream::setup(
            'root',
            0755,
            [
                'download' => [],
            ],
        );

        $dest = 'vfs://root/download/downloaded.tmp';

        self::assertFileDoesNotExist($dest);

        $request = $this->createRequest(new Stream());

        $src = static::getTestUrlFromConstant();

        $request = $request
            ->withUri(new Uri($src . '/json?foo=bar'))
            ->withMethod('GET');

        $response = $this->instance->download($request, $dest);

        self::assertStringSafeEquals(
            '{"foo":"bar"}',
            trim(file_get_contents($dest)),
        );
    }

    #[Test]
    public function requestWithWriteStreamWritesResponseBodyToStream(): void
    {
        $root = vfsStream::setup(
            'root',
            0755,
            [
                'download' => [],
            ],
        );

        $dest = 'vfs://root/download/stream.tmp';

        self::assertFileDoesNotExist($dest);

        $request = $this->createRequest();
        $src = static::getTestUrlFromConstant();

        $request = $request
            ->withUri(new Uri($src . '/json?foo=bar'))
            ->withMethod('GET');

        $res = $this->instance->request(
            $request,
            [
                'write_stream' => Stream::fromFilePath($dest),
            ],
        );

        self::assertStringSafeEquals(
            '{"foo":"bar"}',
            trim(file_get_contents($dest)),
        );
    }

    protected static function getTestUrl(): Uri
    {
        return new Uri(static::getTestUrlFromConstant());
    }

    protected function getHost(): string
    {
        return static::getTestUrl()->toString(Uri::HOST | Uri::PORT);
    }

    protected static function getTestUrlFromConstant(): string
    {
        return WINDWALKER_TEST_HTTP_URL;
    }
}
