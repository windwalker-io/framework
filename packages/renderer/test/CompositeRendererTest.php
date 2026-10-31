<?php

declare(strict_types=1);

namespace Windwalker\Renderer\Test;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use LogicException;
use PHPUnit\Framework\TestCase;
use stdClass;
use UnexpectedValueException;
use Windwalker\Edge\Exception\LayoutNotFoundException;
use Windwalker\Filesystem\Path;
use Windwalker\Renderer\TwigRenderer;
use Windwalker\Renderer\CompositeRenderer;
use Windwalker\Test\Traits\BaseAssertionTrait;

/**
 * The CompositeRendererTest class.
 */
class CompositeRendererTest extends TestCase
{
    use BaseAssertionTrait;

    protected ?CompositeRenderer $instance;

    #[Test]
    public function makeRendersBladeTemplateFromDottedName(): void
    {
        $r = $this->instance->make('foo.olive', [])(['hello' => 'Welcome']);

        self::assertStringSafeEquals(
            'Olive Welcome',
            $r
        );
    }

    #[Test]
    public function makeRendersTwigTemplateFromRootDottedName(): void
    {
        $r = $this->instance->make('sunflower', [])(['hello' => 'Welcome']);

        self::assertStringSafeEquals(
            'SUNFLOWER Welcome',
            $r
        );
    }

    #[Test]
    public function makeRendersTwigTemplateFromSubfolderDottedName(): void
    {
        $r = $this->instance->make('foo.sunflower', [])(['hello' => 'Welcome']);

        self::assertStringSafeEquals(
            'Sunflower Welcome',
            $r
        );
    }

    #[Test]
    public function makeRendersTwigTemplateFromRootWithExplicitExtension(): void
    {
        $r = $this->instance->make('sunflower.twig', [])(['hello' => 'Welcome']);

        self::assertStringSafeEquals(
            'SUNFLOWER Welcome',
            $r
        );
    }

    #[Test]
    public function makeRendersTwigTemplateFromSubfolderWithExplicitExtension(): void
    {
        $r = $this->instance->make('foo/sunflower.twig', [])(['hello' => 'Welcome']);

        self::assertStringSafeEquals(
            'Sunflower Welcome',
            $r
        );
    }

    #[Test]
    public function makeRendersMustacheTemplateFromDottedName(): void
    {
        $r = $this->instance->make('foo.rose', [])(['hello' => 'Welcome']);

        self::assertStringSafeEquals(
            'ROSE Welcome',
            $r
        );
    }

    #[Test]
    public function makeRendersMustacheTemplateWithExplicitExtension(): void
    {
        $r = $this->instance->make('rose.mustache', [])(['hello' => 'Welcome']);

        self::assertStringSafeEquals(
            'Rose Welcome',
            $r
        );
    }

    #[Test]
    public function makeRendersEdgeTemplateAndPrefersFirstPath(): void
    {
        $r = $this->instance->make('sakura', [])(['hello' => 'Welcome']);

        self::assertStringSafeEquals('Sakura Welcome', $r);
    }

    #[Test]
    public function makeUsesFirstMatchingPathWhenMultiplePathsContainTheLayout(): void
    {
        $r = $this->instance->make('foo.sakura', [])(['hello' => 'Welcome']);

        self::assertStringSafeEquals('sakura Welcome', $r);
    }

    #[Test]
    public function makeRendersAbsoluteFilePath(): void
    {
        $r = $this->instance->make(__DIR__ . '/Tmpl/mixed/sakura.edge.php', [])(['hello' => 'Welcome']);

        self::assertStringSafeEquals('Sakura Welcome', $r);
    }

    #[Test]
    public function makeThrowsWhenLayoutNotFound(): void
    {
        $this->expectException(LayoutNotFoundException::class);
        $this->expectExceptionMessageIsOrContains('Layout: not.exists not found in paths');

        $this->instance->make('not.exists');
    }

    #[Test]
    public function makeThrowsWhenFileExtensionHasNoFactory(): void
    {
        $this->instance->removeFactory('mustache');

        $this->expectException(LayoutNotFoundException::class);
        $this->expectExceptionMessageIsOrContains('Layout: foo.rose not found');

        $this->instance->make('foo.rose');
    }

    #[Test]
    public function renderReturnsRenderedString(): void
    {
        $r = $this->instance->render('foo.olive', ['hello' => 'Welcome']);

        self::assertStringSafeEquals('Olive Welcome', $r);
    }

    #[Test]
    public function findFileReturnsFileInfoByDottedNameAndExtension(): void
    {
        $file = $this->instance->findFile('foo.sunflower');

        self::assertSame('sunflower.twig', $file->getFilename());
        self::assertStringEndsWith('Tmpl/mixed/foo/sunflower.twig', Path::clean($file->getPathname(), '/'));
    }

    #[Test]
    public function findFileMatchesFullFilename(): void
    {
        $file = $this->instance->findFile('foo/sunflower.twig');

        self::assertStringEndsWith('Tmpl/mixed/foo/sunflower.twig', Path::clean($file->getPathname(), '/'));
    }

    #[Test]
    public function findFileReturnsNullWhenNotFound(): void
    {
        self::assertNull($this->instance->findFile('not.exists'));
    }

    #[Test]
    public function findFileResolvesAlias(): void
    {
        $this->instance->alias('flower', 'foo.sunflower');

        $file = $this->instance->findFile('flower');

        self::assertStringEndsWith('Tmpl/mixed/foo/sunflower.twig', Path::clean($file->getPathname(), '/'));
    }

    #[Test]
    public function resolveAliasFollowsAliasChain(): void
    {
        $this->instance->alias('a', 'b')->alias('b', 'c');

        self::assertSame('c', $this->instance->resolveAlias('a'));
        self::assertSame('other', $this->instance->resolveAlias('other'));
    }

    #[Test]
    public function hasReportsLayoutExistence(): void
    {
        self::assertTrue($this->instance->has('foo.olive'));
        self::assertFalse($this->instance->has('not.exists'));
    }

    #[Test]
    public function getSupportedExtensionsListsAllFactoryExtensions(): void
    {
        self::assertSame(
            ['edge.php', 'blade.php', 'php', 'mustache', 'twig'],
            $this->instance->getSupportedExtensions()
        );
    }

    #[Test]
    public function findFileInfoByExtensionsReturnsFirstExistingFile(): void
    {
        $file = CompositeRenderer::findFileInfoByExtensions(
            __DIR__ . '/Tmpl/mixed',
            'foo/sunflower',
            ['blade.php', 'twig']
        );

        self::assertSame('sunflower.twig', $file->getFilename());
        self::assertNull(
            CompositeRenderer::findFileInfoByExtensions(__DIR__ . '/Tmpl/mixed', 'foo/sunflower', ['mustache'])
        );
    }

    #[Test]
    public function addFactoryRegistersNewRendererType(): void
    {
        $this->instance->addFactory('custom', TwigRenderer::class, ['custom']);

        self::assertSame([TwigRenderer::class, ['custom']], $this->instance->getFactory('custom'));
    }

    #[Test]
    public function addFactoryRejectsClassNotImplementingTemplateFactory(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageIsOrContains('Renderer factory should be sub class of');

        $this->instance->addFactory('bad', stdClass::class, ['bad']);
    }

    #[Test]
    public function addFileExtensionsMergesWithoutDuplicates(): void
    {
        $this->instance->addFileExtensions('twig', ['twig', 'html.twig']);

        self::assertSame(['twig', 'html.twig'], array_values($this->instance->getFactory('twig')[1]));
    }

    #[Test]
    public function addFileExtensionsRejectsUnregisteredFactory(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageIsOrContains('Factory nope not registered.');

        $this->instance->addFileExtensions('nope', 'x');
    }

    #[Test]
    public function removeFactoryDeletesFactory(): void
    {
        $this->instance->removeFactory('twig');

        self::assertNull($this->instance->getFactory('twig'));
    }

    #[Test]
    public function setFactoriesReplacesAllFactories(): void
    {
        $this->instance->setFactories(['twig' => [TwigRenderer::class, ['twig']]]);

        self::assertSame(['twig'], array_keys($this->instance->getFactories()));
    }

    #[Test]
    public function getRendererReturnsSameInstancePerType(): void
    {
        $renderer = $this->instance->getRenderer('twig');

        self::assertInstanceOf(TwigRenderer::class, $renderer);
        self::assertSame($renderer, $this->instance->getRenderer('twig'));
    }

    #[Test]
    public function getRendererThrowsWhenFactoryNotRegistered(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessageIsOrContains('Renderer factory unknown not registered.');

        $this->instance->getRenderer('unknown');
    }

    #[Test]
    public function extendDecoratesCreatedRenderers(): void
    {
        $decorated = [];

        $this->instance->extend(
            function (object $renderer) use (&$decorated) {
                $decorated[] = $renderer::class;

                return $renderer;
            }
        );

        $renderer = $this->instance->getRenderer('twig');

        self::assertInstanceOf(TwigRenderer::class, $renderer);
        self::assertSame([TwigRenderer::class], $decorated);
    }

    #[Before]
    protected function createRenderer(): void
    {
        $this->instance = new CompositeRenderer(
            [
                __DIR__ . '/Tmpl/mixed',
                __DIR__ . '/Tmpl/mixed2',
            ],
            [
                'cache_path' => __DIR__ . '/../tmp',
            ]
        );
    }
}
