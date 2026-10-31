<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Helper;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\File\HttpUploadStringFile;
use Windwalker\Http\Helper\MultipartParser;

class MultipartParserTest extends TestCase
{
    #[Test]
    public function flattenToQueryNameFlattensNestedArrays(): void
    {
        $result = MultipartParser::flattenToQueryName([
            'a' => 1,
            'b' => ['c' => 2, 'd' => ['e' => 3]],
            'f' => [4, 5],
        ]);

        self::assertSame(
            ['a' => 1, 'b[c]' => 2, 'b[d][e]' => 3, 'f[0]' => 4, 'f[1]' => 5],
            $result,
        );
    }

    #[Test]
    public function flattenToQueryNameReturnsEmptyArrayForEmptyInput(): void
    {
        self::assertSame([], MultipartParser::flattenToQueryName([]));
    }

    #[Test]
    public function createBoundaryReturnsUrlSafeUniqueValue(): void
    {
        $boundary = MultipartParser::createBoundary();

        self::assertMatchesRegularExpression('/^----[A-Za-z0-9_-]+$/', $boundary);
        self::assertNotSame($boundary, MultipartParser::createBoundary());
    }

    #[Test]
    public function toFormDataBuildsFieldsAndFileParts(): void
    {
        $body = MultipartParser::toFormData('BND', [
            'foo' => 'bar',
            'nested' => ['x' => 'y'],
            'file' => new HttpUploadStringFile('FILEDATA', 'text/csv', 'a.csv'),
        ]);

        self::assertStringStartsWith("--BND\r\n", $body);
        self::assertStringEndsWith("\r\n--BND--", $body);
        self::assertStringContainsString("Content-Disposition: form-data; name=\"foo\"\r\n\r\nbar", $body);
        self::assertStringContainsString("name=\"nested[x]\"\r\n\r\ny", $body);
        self::assertStringContainsString(
            "name=\"file\"; filename=\"a.csv\"\r\nContent-Type: text/csv\r\n"
            . "Content-Transfer-Encoding: binary\r\n\r\nFILEDATA\r\n",
            $body,
        );
    }

    #[Test]
    public function toFormDataUsesDefaultsForFileWithoutNameOrMime(): void
    {
        $body = MultipartParser::toFormData('BND', ['file' => new HttpUploadStringFile('x')]);

        self::assertMatchesRegularExpression('/filename="[^"]+\.txt"/', $body);
        self::assertStringContainsString("Content-Type: text/plain\r\n", $body);
    }

    #[Test]
    public function parseFormDataReadsFieldsAndFiles(): void
    {
        $body = "--BND\r\n"
            . "Content-Disposition: form-data; name=\"foo\"\r\n\r\nbar\r\n"
            . "--BND\r\n"
            . "Content-Disposition: form-data; name=\"nested[x]\"\r\n\r\ny\r\n"
            . "--BND\r\n"
            . "Content-Disposition: form-data; name=\"upload\"; filename=\"a.txt\"\r\n"
            . "Content-Type: text/plain\r\n\r\nFILEDATA\r\n"
            . '--BND--';

        $result = MultipartParser::parseFormData($body);

        self::assertSame(['foo' => 'bar', 'nested' => ['x' => 'y']], $result['data']);

        $file = $result['files']['upload'];

        self::assertSame('a.txt', $file['name']);
        self::assertSame('text/plain', $file['type']);
        self::assertSame(0, $file['error']);
        self::assertSame(8, $file['size']);
        self::assertSame('FILEDATA', file_get_contents($file['tmp_name']));

        unlink($file['tmp_name']);
    }

    #[Test]
    public function parseFormDataRoundTripsToFormDataOutput(): void
    {
        $body = MultipartParser::toFormData('BND', ['a' => '1', 'b' => ['c' => '2']]);

        self::assertSame(
            ['data' => ['a' => '1', 'b' => ['c' => '2']], 'files' => []],
            MultipartParser::parseFormData($body),
        );
    }
}
