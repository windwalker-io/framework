<?php

declare(strict_types=1);

namespace Windwalker\Renderer\Test;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Edge\Edge;
use Windwalker\Renderer\EdgeRenderer;
use Windwalker\Test\Traits\DOMTestTrait;

/**
 * Test class of EdgeRenderer
 */
class EdgeRendererTest extends TestCase
{
    use DOMTestTrait;

    protected EdgeRenderer $instance;

    #[Before]
    protected function createRenderer(): void
    {
        $this->instance = new EdgeRenderer(['paths' => [__DIR__ . '/Tmpl/edge']]);
    }

    #[Test]
    public function renderTemplateExtendingMasterLayout(): void
    {
        $html = $this->instance->render('hello');

        self::assertStringContainsString('This is appended to the master sidebar.', $html);
        self::assertStringContainsString('This is my body content.', $html);
    }

    #[Test]
    public function createEngineReturnsEdgeWithArrayCacheByDefault(): void
    {
        self::assertInstanceOf(Edge::class, $this->instance->createEngine());
    }

    #[Test]
    public function createEngineUsesFileCacheWhenCachePathIsSet(): void
    {
        $cachePath = sys_get_temp_dir() . '/windwalker-edge-test-' . uniqid();

        mkdir($cachePath);

        try {
            $renderer = new EdgeRenderer(['paths' => [__DIR__ . '/Tmpl/edge'], 'cache_path' => $cachePath]);

            $renderer->render('hello');

            self::assertNotEmpty(glob($cachePath . '/*'));
        } finally {
            array_map('unlink', glob($cachePath . '/*') ?: []);
            rmdir($cachePath);
        }
    }

    #[Test]
    public function extendDecoratesEngineBuilder(): void
    {
        $options = null;

        $this->instance->extend(
            function (Edge $edge, array $opts) use (&$options) {
                $options = $opts;

                return $edge;
            }
        );

        $this->instance->createEngine(['debug' => true]);

        self::assertTrue($options['debug']);
    }
}
