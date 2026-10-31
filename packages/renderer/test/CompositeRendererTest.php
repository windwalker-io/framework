<?php

declare(strict_types=1);

namespace Windwalker\Renderer\Test;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
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

    /**
     * @see  CompositeRenderer::make
     */
    #[Test]
    public function makeReturnsRenderableView(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  CompositeRenderer::render
     */
    #[Test]
    public function renderOutputsTemplate(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  CompositeRenderer::findFile
     */
    #[Test]
    public function findFileResolvesTemplatePath(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
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
