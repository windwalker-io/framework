<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Request;

use BadMethodCallException;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\Request\RequestBaseUri;
use Windwalker\Http\Request\ServerRequest;

/**
 * Test class of RequestBaseUri
 */
class RequestBaseUriTest extends TestCase
{
    protected RequestBaseUri $instance;

    #[Before]
    protected function createInstance(): void
    {
        $this->instance = $this->parse(
            'https://example.com/sub/index.php/foo/bar?x=1#frag',
            '/sub/index.php',
        );
    }

    protected function parse(string $url, string $script): RequestBaseUri
    {
        return RequestBaseUri::parseFromRequest(new ServerRequest(['SCRIPT_NAME' => $script], [], $url, 'GET'));
    }

    #[Test]
    public function parseFromRequestResolvesAllFields(): void
    {
        self::assertSame(
            [
                'full' => 'https://example.com/sub/index.php/foo/bar?x=1#frag',
                'current' => 'https://example.com/sub/index.php/foo/bar',
                'script' => 'index.php',
                'root' => 'https://example.com/sub/',
                'host' => 'https://example.com',
                'path' => '/sub/',
                'route' => 'foo/bar/',
                'routeWithQuery' => 'foo/bar/?x=1',
                'routeAndQuery' => ['foo/bar/', 'x=1'],
            ],
            $this->instance->all(),
        );
        self::assertSame($this->instance->all(), $this->instance->jsonSerialize());
    }

    #[Test]
    public function parseFromRequestHandlesRootScript(): void
    {
        $uri = $this->parse('http://localhost/index.php/a/b', '/index.php');

        self::assertSame('http://localhost/', $uri->root);
        self::assertSame('a/b/', $uri->route);
        self::assertSame('/', $uri->path);
    }

    #[Test]
    public function parseFromRequestWithoutScriptInPathHasEmptyRoute(): void
    {
        $uri = $this->parse('http://localhost/sub/', '/sub/index.php');

        self::assertSame('', trim($uri->route, '/'));
        self::assertSame('index.php', $uri->script);
    }

    #[Test]
    public function getScriptNameReturnsEmptyStringWhenIgnored(): void
    {
        self::assertSame('index.php', $this->instance->getScriptName());
        self::assertSame('', $this->instance->getScriptName('index.php'));
        self::assertSame('index.php', $this->instance->getScriptName('other.php'));
    }

    #[Test]
    public function withScriptNameChangesScriptField(): void
    {
        $uri = $this->instance->withScriptName('app.php');

        self::assertSame('app.php', $uri->script);
    }

    #[Test]
    public function withOriginalReturnsCloneWithNewFullUrl(): void
    {
        $new = $this->instance->withOriginal('https://other.com/x');

        self::assertNotSame($this->instance, $new);
        self::assertSame('https://other.com/x', $new->full);
        self::assertSame('https://example.com/sub/index.php/foo/bar?x=1#frag', $this->instance->full);
    }

    #[Test]
    public function suffixJoinsFieldWithUrlUsingSingleSlash(): void
    {
        self::assertSame('https://example.com/sub/y', $this->instance->suffix('root', '/y'));
        self::assertSame('https://example.com/sub/y', $this->instance->suffix('root', 'y'));
    }

    #[Test]
    public function magicCallReturnsFieldOrSuffixedValue(): void
    {
        self::assertSame('https://example.com/sub/', $this->instance->root());
        self::assertSame('https://example.com/sub/a', $this->instance->root('a'));
        self::assertSame('foo/bar/z', $this->instance->route('z'));
    }

    #[Test]
    public function magicCallThrowsForUnknownMethod(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessageIsOrContains('unknown() not found');

        $this->instance->unknown();
    }

    #[Test]
    public function routeWithQueryControlsTrailingSlash(): void
    {
        self::assertSame('foo/bar/?x=1', $this->instance->routeWithQuery());
        self::assertSame('foo/bar?x=1', $this->instance->routeWithQuery(false));
    }

    #[Test]
    public function routeWithQueryOmitsQuestionMarkWithoutQuery(): void
    {
        $uri = $this->parse('https://example.com/sub/index.php/foo', '/sub/index.php');

        self::assertSame('foo/', $uri->routeWithQuery());
        self::assertSame('foo', $uri->routeWithQuery(false));
    }

    #[Test]
    #[DataProvider('absoluteProvider')]
    public function absoluteResolvesUrlAgainstBase(string $url, bool $full, string $expected): void
    {
        self::assertSame($expected, $this->instance->absolute($url, $full));
    }

    public static function absoluteProvider(): array
    {
        return [
            'scheme url untouched' => ['http://a.com/x', false, 'http://a.com/x'],
            'root relative' => ['/x', false, '/x'],
            'root relative full' => ['/x', true, 'https://example.com/x'],
            'relative' => ['x', false, '/sub/x'],
            'relative full' => ['x', true, 'https://example.com/sub/x'],
        ];
    }

    #[Test]
    #[DataProvider('addPrefixProvider')]
    public function addPrefixNormalizesSlashes(string $url, string $prefix, string $expected): void
    {
        self::assertSame($expected, RequestBaseUri::addPrefix($url, $prefix));
    }

    public static function addPrefixProvider(): array
    {
        return [
            'defaults' => ['a', '/', '/a'],
            'both slashes' => ['/a', 'http://x.com/', 'http://x.com/a'],
            'no slashes' => ['a', 'http://x.com', 'http://x.com/a'],
        ];
    }

    #[Test]
    #[DataProvider('urlTypeProvider')]
    public function absoluteAndFullUrlDetection(string $uri, bool $absolute, bool $full): void
    {
        self::assertSame($absolute, RequestBaseUri::isAbsoluteUrl($uri));
        self::assertSame($full, RequestBaseUri::isFullUrl($uri));
    }

    public static function urlTypeProvider(): array
    {
        return [
            'https' => ['https://a.com', true, true],
            'uppercase scheme' => ['HTTP://a.com', true, true],
            'root path' => ['/a', true, false],
            'relative' => ['a/b', false, false],
        ];
    }

    #[Test]
    public function normalizeResolvesDotSegments(): void
    {
        self::assertSame('/a/c', RequestBaseUri::normalize('/a/b/../c'));
    }

    #[Test]
    public function baseReturnsRootPathOrFullPath(): void
    {
        self::assertSame('https://example.com/sub/', $this->instance->base());
        self::assertSame('/sub', $this->instance->base(true));
    }

    #[Test]
    public function baseFallsBackToSlashWhenEmpty(): void
    {
        $uri = $this->parse('', '');

        self::assertSame('/', $uri->base(true));
    }

    #[Test]
    public function addUriBaseKeepsAbsoluteAndPrefixesRelative(): void
    {
        self::assertSame('/x', $this->instance->addUriBase('/x'));
        self::assertSame('/sub/q', $this->instance->addUriBase('q'));
        self::assertSame('/other/q', $this->instance->addUriBase('q', '/other'));
    }

    #[Test]
    public function makeFullPrependsHostToRootRelativeResult(): void
    {
        self::assertSame('https://example.com/sub/a/b', $this->instance->makeFull('a/b'));
        self::assertSame('http://x.com/a', $this->instance->makeFull('http://x.com/a'));
    }
}
