<?php

declare(strict_types=1);

namespace Windwalker\Renderer\Test;

use Illuminate\Contracts\View\Factory;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Windwalker\Filesystem\Filesystem;
use Windwalker\Renderer\BladeRenderer;
use Windwalker\Test\Traits\DOMTestTrait;

/**
 * Test class of BladeRenderer
 *
 * @since 2.0
 */
class BladeRendererTest extends TestCase
{
    use DOMTestTrait;

    /**
     * Test instance.
     *
     * @var  BladeRenderer
     */
    protected $instance;

    /**
     * Property path.
     *
     * @var  string
     */
    protected static $path;

    #[Before]
    protected function createRendererAndCacheDir(): void
    {
        static::$path = realpath(__DIR__ . '/Tmpl/blade');

        if (!static::$path) {
            throw new RuntimeException('Path not exists');
        }

        Filesystem::mkdir(__DIR__ . '/cache');

        $this->instance = new BladeRenderer(
            [
                'cache_path' => __DIR__ . '/cache',
                'paths' => [static::$path],
            ]
        );
    }

    #[After]
    protected function deleteCacheDir(): void
    {
        Filesystem::delete(__DIR__ . '/cache');
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        Filesystem::deleteIfExists(__DIR__ . '/cache');
    }

    #[Test]
    public function makeRendersTemplateExtendingMasterLayout(): void
    {
        $html = $this->instance->make('hello')();

        $expect = <<<HTML
<html>
<body>
    This is the master sidebar.

    <p>This is appended to the master sidebar.</p>
    <div class="container">
        <p>This is my body content.</p>
    </div>
</body>
</html>
HTML;

        self::assertHtmlFormatEquals($expect, $html);
    }

    #[Test]
    public function createCompilerThrowsWhenCachePathMissing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Please set cache_path to options.');

        $this->instance->createCompiler();
    }

    #[Test]
    public function createCompilerCreatesMissingCacheDirectory(): void
    {
        $path = __DIR__ . '/cache/nested';

        $this->instance->createCompiler(null, $path);

        self::assertDirectoryExists($path);
    }

    #[Test]
    public function createEngineReturnsViewFactory(): void
    {
        self::assertInstanceOf(Factory::class, $this->instance->createEngine([]));
    }
}
