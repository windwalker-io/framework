<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Response;

use JsonException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\Response\JsonResponse;
use Windwalker\Stream\Stream;

class JsonResponseTest extends TestCase
{
    #[Test]
    #[DataProvider('encodeProvider')]
    public function constructEncodesData(mixed $data, string $expected): void
    {
        $response = new JsonResponse($data);

        self::assertSame($expected, (string) $response->getBody());
        self::assertSame('application/json; charset=utf-8', $response->getHeaderLine('Content-Type'));
    }

    public static function encodeProvider(): array
    {
        return [
            'array' => [['a' => 1], '{"a":1}'],
            'object json string' => ['{"a":1}', '{"a":1}'],
            'list json string' => ['[1,2]', '[1,2]'],
            'quoted json string' => ['"foo"', '"foo"'],
            'plain string' => ['foo', '"foo"'],
            'empty string' => ['', '""'],
            'null' => [null, 'null'],
            'int' => [123, '123'],
        ];
    }

    #[Test]
    public function constructAppliesJsonOptions(): void
    {
        $response = new JsonResponse(['url' => 'a/b'], 200, [], JSON_UNESCAPED_SLASHES);

        self::assertSame('{"url":"a/b"}', (string) $response->getBody());
    }

    #[Test]
    public function constructKeepsStreamBody(): void
    {
        $stream = Stream::fromString('{"x":1}');

        $response = new JsonResponse($stream);

        self::assertSame($stream, $response->getBody());
    }

    #[Test]
    public function constructThrowsOnUnencodableData(): void
    {
        $this->expectException(JsonException::class);

        new JsonResponse(NAN);
    }

    #[Test]
    public function withContentEncodesContent(): void
    {
        $response = new JsonResponse(['a' => 1]);

        self::assertSame('"foo"', (string) $response->withContent('foo')->getBody());
        self::assertSame('{"b":2}', (string) $response->withContent('{"b":2}')->getBody());
    }
}
