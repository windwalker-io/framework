<?php

declare(strict_types=1);

namespace Windwalker\Uri\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\Uri\UriTemplate;

class UriTemplateTest extends TestCase
{
    /**
     * @see  UriTemplate::expand
     */
    public function testExpand(): void
    {
        $tpl = new UriTemplate('https://example.org/foo/{foo}{?bar}', ['foo' => '123']);

        $uri = $tpl->expand(['bar' => 'yoo']);

        self::assertEquals('https://example.org/foo/123?bar=yoo', $uri);
    }

    /**
     * @see  UriTemplate::expand
     */
    public function testExpandWithExtraVars(): void
    {
        $tpl = new UriTemplate('https://example.org/foo/{foo}{?bar}', ['foo' => '123', 'a' => 'b']);

        $uri = $tpl->expand(['bar' => 'yoo', 'goo' => 'zeo']);

        self::assertEquals('https://example.org/foo/123?bar=yoo', $uri);
    }

    /**
     * @see  UriTemplate::expand
     */
    public function testExpandAndLessVars(): void
    {
        $tpl = new UriTemplate('https://example.org/foo/{foo}{?bar}');

        $uri = $tpl->expand(['bar' => 'yoo']);

        self::assertEquals('https://example.org/foo/?bar=yoo', $uri);
    }

    /**
     * @see  UriTemplate::extract
     */
    public function testExtract(): void
    {
        $tpl = new UriTemplate('https://example.org/foo/{foo}{?bar}');
        $vars = $tpl->extract('https://example.org/foo/123?bar=yoo');

        self::assertEquals(
            ['foo' => 123, 'bar' => 'yoo'],
            $vars
        );
    }

    /**
     * @see  UriTemplate::bindValue
     */
    public function testBindValue(): void
    {
        $tpl = new UriTemplate('https://example.org/foo/{foo}{?bar}');
        $tpl->bindValue('foo', 123);
        $tpl->bindValue('bar', 'yoo');

        self::assertEquals('https://example.org/foo/123?bar=yoo', (string) $tpl);
    }

    /**
     * @see  UriTemplate::bind
     */
    public function testBind(): void
    {
        $foo = 321;
        $bar = null;

        $tpl = new UriTemplate('https://example.org/foo/{foo}{?bar}');
        $tpl->bind('foo', $foo);
        $tpl->bind('bar', $bar);

        $foo = 123;
        $bar = 'yoo';

        self::assertEquals('https://example.org/foo/123?bar=yoo', (string) $tpl);
    }
}
