<?php

declare(strict_types=1);

namespace Windwalker\Http\Test;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Windwalker\Http\AsyncHttpClient;
use Windwalker\Http\Promise\PromiseResponse;
use Windwalker\Http\Request\Request;
use Windwalker\Http\Response\Response;
use Windwalker\Http\Test\Mock\MockTransport;
use Windwalker\Http\Test\Transport\TestServerExistsTrait;
use Windwalker\Http\Transport\CurlTransport;
use Windwalker\Http\Transport\StreamTransport;
use Windwalker\Uri\Uri;

class AsyncHttpClientTest extends TestCase
{
    use TestServerExistsTrait;

    protected AsyncHttpClient $instance;

    #[BeforeClass]
    public static function ensureTestServerIsRunning(): void
    {
        self::checkTestServerRunningOrSkip();
    }

    #[Before]
    public function setUpInstance(): void
    {
        $this->instance = new AsyncHttpClient(
            transport: new CurlTransport(['options' => [CURLOPT_SSL_VERIFYPEER => false]]),
        );
    }

    #[After]
    public function tearDownInstance(): void
    {
        $this->instance->reset();
    }

    protected function createUrlRequest(string $url): Request
    {
        return new Request()->withUri(new Uri($url))->withRequestTarget($url)->withMethod('GET');
    }

    protected function createRequest(string $path): Request
    {
        return $this->createUrlRequest(WINDWALKER_TEST_HTTP_URL . $path);
    }

    #[Test]
    public function getMainHandleCreatesHandleOnceAndReusesIt(): void
    {
        $mh = $this->instance->getMainHandle();

        self::assertInstanceOf(\CurlMultiHandle::class, $mh);
        self::assertSame($mh, $this->instance->getMainHandle());
    }

    #[Test]
    public function setTransportAcceptsCurlTransport(): void
    {
        $transport = new CurlTransport();

        self::assertSame($this->instance, $this->instance->setTransport($transport));
        self::assertSame($transport, $this->instance->getTransport());
    }

    #[Test]
    public function setTransportRejectsNonCurlTransport(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(CurlTransport::class);

        $this->instance->setTransport(new MockTransport());
    }

    #[Test]
    public function setTransportRejectsStreamTransport(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->instance->setTransport(new StreamTransport());
    }

    #[Test]
    public function senRequestQueuesTaskAndReturnsPromise(): void
    {
        $promise = $this->instance->senRequest($this->createRequest('/json?a=1'));

        self::assertInstanceOf(PromiseResponse::class, $promise);
        self::assertCount(1, $this->instance->getHandles());
        self::assertCount(1, $this->instance->getTasks());
    }

    #[Test]
    public function resolveReturnsResponsesForAllQueuedRequests(): void
    {
        $this->instance->senRequest($this->createRequest('/json?a=1'));
        $this->instance->senRequest($this->createRequest('/json?a=2'));

        $responses = $this->instance->resolve();

        self::assertCount(2, $responses);
        self::assertSame(200, $responses[0]->getStatusCode());
        self::assertSame('{"a":"1"}', (string) $responses[0]->getBody());
        self::assertSame('{"a":"2"}', (string) $responses[1]->getBody());
        self::assertSame([], $this->instance->getErrors());
    }

    #[Test]
    public function resolveResolvesPromisesWithResponses(): void
    {
        $received = [];

        $promise = $this->instance->senRequest($this->createRequest('/json?a=3'));
        $promise->then(
            function (Response $res) use (&$received) {
                $received[] = (string) $res->getBody();

                return $res;
            },
        );

        $this->instance->resolve();

        self::assertSame(['{"a":"3"}'], $received);
    }

    #[Test]
    public function resolveInvokesCallbackWithResponsesErrorsAndClient(): void
    {
        $this->instance->senRequest($this->createRequest('/json?a=1'));

        $args = null;

        $this->instance->resolve(
            function (array $responses, array $errors, AsyncHttpClient $client) use (&$args) {
                $args = [$responses, $errors, $client];
            },
        );

        self::assertCount(1, $args[0]);
        self::assertSame([], $args[1]);
        self::assertSame($this->instance, $args[2]);
    }

    #[Test]
    public function resolveRejectsPromiseAndCollectsErrorOnConnectionFailure(): void
    {
        $failure = null;

        $promise = $this->instance->senRequest(
            $this->createUrlRequest('http://127.0.0.1:1/unreachable'),
        );
        $promise->fail(
            function (RuntimeException $e) use (&$failure) {
                $failure = $e;

                return $e;
            },
        );

        $errors = null;
        $responses = $this->instance->resolve(
            function (array $responses, array $errs) use (&$errors) {
                $errors = $errs;
            },
        );

        self::assertSame([], $responses);
        self::assertCount(1, $errors);
        self::assertInstanceOf(RuntimeException::class, $failure);
        self::assertSame($errors[0], $failure);
        self::assertNotSame(0, $failure->getCode());
    }

    #[Test]
    public function resolveResetsQueueAfterCompletion(): void
    {
        $this->instance->senRequest($this->createRequest('/json?a=1'));
        $this->instance->resolve();

        self::assertSame([], $this->instance->getHandles());
        self::assertSame([], $this->instance->resolve());
    }

    #[Test]
    public function resetClearsTasksAndMainHandle(): void
    {
        $this->instance->senRequest($this->createRequest('/json?a=1'));
        $mh = $this->instance->getMainHandle();

        self::assertSame($this->instance, $this->instance->reset());
        self::assertSame([], $this->instance->getHandles());
        self::assertNotSame($mh, $this->instance->getMainHandle());
    }

    #[Test]
    public function resetWithoutMainHandleDoesNotFail(): void
    {
        self::assertSame($this->instance, $this->instance->reset());
        self::assertSame([], $this->instance->getHandles());
    }
}
