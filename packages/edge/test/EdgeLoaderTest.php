<?php

declare(strict_types=1);

namespace Windwalker\Edge\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Edge\Cache\EdgeArrayCache;
use Windwalker\Edge\Exception\LayoutNotFoundException;
use Windwalker\Edge\Loader\EdgeFileLoader;
use Windwalker\Edge\Loader\EdgeStringLoader;

class EdgeLoaderTest extends TestCase
{
    #[Test]
    public function fileLoaderFindsFilesByDottedNameAndExtension(): void
    {
        $loader = new EdgeFileLoader([__DIR__ . '/tmpl']);

        self::assertSame(__DIR__ . '/tmpl/hello.blade.php', $loader->find('hello'));
        self::assertSame(__DIR__ . '/tmpl/components/hello.blade.php', $loader->find('components.hello'));
        self::assertSame(__DIR__ . '/tmpl/tmpl.edge.php', $loader->find('tmpl'));
        self::assertTrue($loader->has('hello'));
        self::assertFalse($loader->has('not-exists'));
        self::assertSame(
            file_get_contents(__DIR__ . '/tmpl/hello.blade.php'),
            $loader->load($loader->find('hello'))
        );
    }

    #[Test]
    public function fileLoaderThrowsWhenLayoutIsMissing(): void
    {
        $loader = new EdgeFileLoader([__DIR__ . '/tmpl']);

        $this->expectException(LayoutNotFoundException::class);
        $this->expectExceptionMessageIsOrContains('View file not found: not-exists');

        $loader->find('not-exists');
    }

    #[Test]
    public function fileLoaderManagesPathsAndExtensions(): void
    {
        $loader = new EdgeFileLoader(['a']);

        $loader->addPath('b')->prependPath('c');

        self::assertSame(['c', 'a', 'b'], $loader->getPaths());

        $loader->setPaths(['d']);

        self::assertSame(['d'], $loader->getPaths());

        $loader->addFileExtension('tpl.php');

        self::assertContains('tpl.php', $loader->getExtensions());

        $loader->setExtensions(['x']);

        self::assertSame(['x'], $loader->getExtensions());
    }

    #[Test]
    public function stringLoaderReturnsKeyOrFallbackContent(): void
    {
        $loader = new EdgeStringLoader('default');

        self::assertSame('{{ $a }}', $loader->find('{{ $a }}'));
        self::assertSame('{{ $a }}', $loader->load('{{ $a }}'));
        self::assertSame('default', $loader->load(''));
        self::assertSame('default', $loader->getContent());
    }

    #[Test]
    public function arrayCacheStoresLoadsAndRemoves(): void
    {
        $cache = new EdgeArrayCache();

        self::assertSame('', $cache->load('a'));
        self::assertTrue($cache->isExpired('a'));

        $cache->store('a', 'content');

        self::assertSame('content', $cache->load('a'));
        self::assertSame([md5('a') => 'content'], $cache->getData());

        $cache->remove('a');

        self::assertSame('', $cache->load('a'));
        self::assertSame([], $cache->getData());
    }
}
