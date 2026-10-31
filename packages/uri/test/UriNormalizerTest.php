<?php

declare(strict_types=1);

namespace Windwalker\Uri\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Uri\UriNormalizer;

class UriNormalizerTest extends TestCase
{
    #[Test]
    public function cleanCollapsesDuplicatedSlashesInUriPath(): void
    {
        self::assertSame('http://example.com/foo/bar', UriNormalizer::clean('http://example.com//foo///bar'));
    }

    #[Test]
    public function cleanPathCollapsesDuplicatedSlashes(): void
    {
        self::assertSame('/foo/bar/', UriNormalizer::cleanPath('//foo//bar///'));
    }

    #[DataProvider('normalizePathProvider')]
    #[Test]
    public function normalizePathResolvesDotSegments(string $path, string $expected): void
    {
        self::assertSame($expected, UriNormalizer::normalizePath($path));
    }

    public static function normalizePathProvider(): array
    {
        return [
            'parent segment' => ['/foo/bar/../boo.php', '/foo/boo.php'],
            'multiple parent segments' => ['/foo/bar/../../boo.php', '/boo.php'],
            'parent with duplicated slashes' => ['/foo/bar/.././/boo.php', '/foo/boo.php'],
            'current segment' => ['/foo/./bar', '/foo/bar'],
            'parent above root' => ['/../foo', '/foo'],
            'relative parent' => ['foo/bar/../baz', 'foo/baz'],
        ];
    }

    #[Test]
    public function normalizeResolvesDotSegmentsInUriPath(): void
    {
        self::assertSame(
            'http://example.com/foo/boo.php',
            UriNormalizer::normalize('http://example.com/foo/bar/../boo.php')
        );
    }

    #[Test]
    public function ensureRootPrependsSlashOnlyWhenMissing(): void
    {
        self::assertSame('/foo', UriNormalizer::ensureRoot('foo'));
        self::assertSame('/foo', UriNormalizer::ensureRoot('/foo'));
        self::assertSame('\\foo', UriNormalizer::ensureRoot('foo', '\\'));
    }

    #[Test]
    public function ensureDirAppendsSlashOnlyWhenMissing(): void
    {
        self::assertSame('foo/', UriNormalizer::ensureDir('foo'));
        self::assertSame('foo/', UriNormalizer::ensureDir('foo/'));
        self::assertSame('foo\\', UriNormalizer::ensureDir('foo', '\\'));
    }
}
