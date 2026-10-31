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

    #[Test]
    public function setVarsAndSetTemplateAffectExpandedResult(): void
    {
        $tpl = new UriTemplate('https://example.org/{foo}');
        $tpl->setTemplate('https://example.org/bar/{foo}')->setVars(['foo' => 'x']);

        self::assertSame('https://example.org/bar/x', (string) $tpl);
        self::assertSame(['foo' => 'x'], $tpl->getVars());
    }

    #[Test]
    public function expandUsesCustomExpandHandler(): void
    {
        $tpl = new UriTemplate('tpl', ['a' => 1]);
        $tpl->setExpandHandler(
            static fn(string $template, array $vars) => $template . ':' . json_encode($vars)
        );

        self::assertSame('tpl:{"a":1,"b":2}', $tpl->expand(['b' => 2]));
    }

    #[Test]
    public function extractUsesCustomExtractHandler(): void
    {
        $tpl = new UriTemplate('tpl');
        $tpl->setExtractHandler(
            static fn(string $template, string $uri, bool $strict) => [$template, $uri, $strict]
        );

        self::assertSame(['tpl', 'foo', true], $tpl->extract('foo', true));
    }

    #[Test]
    public function helperFunctionsPrepareExpandAndExtract(): void
    {
        $tpl = \Windwalker\Uri\uri_prepare('https://example.org/{foo}', ['foo' => 1]);

        self::assertInstanceOf(UriTemplate::class, $tpl);
        self::assertSame('https://example.org/1', (string) $tpl);
        self::assertSame('https://example.org/2', \Windwalker\Uri\uri_expand('https://example.org/{foo}', ['foo' => 2]));
        self::assertEquals(
            ['foo' => 3],
            \Windwalker\Uri\uri_extract('https://example.org/{foo}', 'https://example.org/3')
        );
    }
}
