<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Response;

use JsonException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Windwalker\Http\Exception\HttpRequestException;
use Windwalker\Http\Response\Response;

class ResponseTraitTest extends TestCase
{
    #[Test]
    #[DataProvider('statusProvider')]
    public function statusHelpersReflectStatusCode(
        int $status,
        bool $success,
        bool $redirect,
        bool $authError,
        bool $serverError,
        bool $error
    ): void {
        $response = new Response('php://memory', $status);

        self::assertSame($success, $response->isSuccess());
        self::assertSame($redirect, $response->isRedirect());
        self::assertSame($authError, $response->isAuthError());
        self::assertSame($serverError, $response->isServerError());
        self::assertSame($error, $response->isError());
    }

    public static function statusProvider(): array
    {
        return [
            'ok' => [200, true, false, false, false, false],
            'redirect' => [302, false, true, false, false, false],
            'client error' => [404, false, false, true, false, true],
            'server error' => [500, false, false, false, true, true],
        ];
    }

    #[Test]
    public function jsonDecodeDecodesBodyAndThrowsOnInvalidJson(): void
    {
        $response = Response::fromString('{"a":{"b":1}}');

        self::assertSame(['a' => ['b' => 1]], $response->jsonDecode(true));
        self::assertSame(1, $response->jsonDecode()->a->b);

        $this->expectException(JsonException::class);

        Response::fromString('{broken')->jsonDecode();
    }

    #[Test]
    public function decodeAndToArrayConvertJsonBody(): void
    {
        $response = Response::fromString('{"a":1,"b":[2]}');

        self::assertSame(1, $response->decode()->get('a'));
        self::assertSame(['a' => 1, 'b' => [2]], $response->toArray());
    }

    #[Test]
    public function toExceptionBuildsHttpRequestExceptionWithResponse(): void
    {
        $response = new Response('php://memory', 404);

        $e = $response->toException();

        self::assertInstanceOf(HttpRequestException::class, $e);
        self::assertSame('Not Found', $e->getMessage());
        self::assertSame(404, $e->getCode());
        self::assertSame($response, $e->response);
    }

    #[Test]
    public function toExceptionSupportsCustomExceptionClass(): void
    {
        $e = new Response('php://memory', 500)->toException(RuntimeException::class);

        self::assertInstanceOf(RuntimeException::class, $e);
        self::assertSame(500, $e->getCode());
    }

    #[Test]
    public function throwThrowsExceptionRegardlessOfStatus(): void
    {
        $this->expectException(HttpRequestException::class);

        new Response('php://memory', 200)->throw();
    }

    #[Test]
    public function throwIfErrorThrowsOnlyForErrorStatus(): void
    {
        new Response('php://memory', 302)->throwIfError();
        new Response('php://memory', 200)->throwIfError();

        $this->expectException(HttpRequestException::class);

        new Response('php://memory', 503)->throwIfError();
    }
}
