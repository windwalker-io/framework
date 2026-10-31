<?php

declare(strict_types=1);

namespace Windwalker\Uri\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Uri\UriTemplate;

class UriTemplateTest extends TestCase
{
    /**
     * @see  UriTemplate::expand
     */
    #[Test]
    public function expandReplacesVarsAndQueryExpression(): void
    {
        $tpl = new UriTemplate('https://example.org/foo/{foo}{?bar}', ['foo' => '123']);

        $uri = $tpl->expand(['bar' => 'yoo']);

        self::assertEquals('https://example.org/foo/123?bar=yoo', $uri);
    }

    /**
     * @see  UriTemplate::expand
     */
    #[Test]
    public function expandIgnoresUnusedVars(): void
    {
        $tpl = new UriTemplate('https://example.org/foo/{foo}{?bar}', ['foo' => '123', 'a' => 'b']);

        $uri = $tpl->expand(['bar' => 'yoo', 'goo' => 'zeo']);

        self::assertEquals('https://example.org/foo/123?bar=yoo', $uri);
    }

    /**
     * @see  UriTemplate::expand
     */
    #[Test]
    public function expandLeavesMissingVarsEmpty(): void
    {
        $tpl = new UriTemplate('https://example.org/foo/{foo}{?bar}');

        $uri = $tpl->expand(['bar' => 'yoo']);

        self::assertEquals('https://example.org/foo/?bar=yoo', $uri);
    }

    /**
     * @see  UriTemplate::extract
     */
    #[Test]
    public function extractReturnsVarsFromUri(): void
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
    #[Test]
    public function bindValueSetsVarValues(): void
    {
        $tpl = new UriTemplate('https://example.org/foo/{foo}{?bar}');
        $tpl->bindValue('foo', 123);
        $tpl->bindValue('bar', 'yoo');

        self::assertEquals('https://example.org/foo/123?bar=yoo', (string) $tpl);
    }

    /**
     * @see  UriTemplate::bind
     */
    #[Test]
    public function bindUsesVariableReferences(): void
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
