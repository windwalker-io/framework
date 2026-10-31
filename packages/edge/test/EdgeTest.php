<?php

declare(strict_types=1);

namespace Windwalker\Edge\Test;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Edge\Cache\EdgeFileCache;
use Windwalker\Edge\Edge;
use Windwalker\Edge\Loader\EdgeFileLoader;
use Windwalker\Test\Traits\DOMTestTrait;

/**
 * Test class of Edge
 *
 * @since 3.0
 */
class EdgeTest extends TestCase
{
    use DOMTestTrait;

    /**
     * Test instance.
     *
     * @var  Edge
     */
    protected ?Edge $instance;

    #[Before]
    public function setUpEdgeInstance(): void
    {
        $this->instance = new Edge(
            new EdgeFileLoader(
                [
                    __DIR__ . '/tmpl',
                ]
            )
        );
    }

    #[Test]
    public function renderTemplateWithLayoutAndSections(): void
    {
        $result = $this->instance->render('hello');

        $expected = <<<HTML
<html>
    <body>
        This is the master sidebar.

        <p>This is appended to the master sidebar.</p>

        <div class="container">
            <p>This is my body content.</p>
            A
        </div>
    </body>
</html>
HTML;

        $this->assertHtmlFormatEquals($expected, $result);
    }

    #[Test]
    public function renderEdgeFileWithEscapedAndRawOutput(): void
    {
        $result = $this->instance->render(
            'tmpl',
            [
                'test' => '<TEST>',
                'escape' => '<TEST>',
                'yoo' => '<TEST>',
                'a' => array_fill(0, 3, 'Hello'),
            ]
        );

        self::assertStringSafeEquals(
            <<<HTML
<html>
<body>
&lt;TEST&gt;

<TEST>

&lt;TEST&gt;


<li>Hello</li>
<li>Hello</li>
<li>Hello</li>
</body>
</html>
HTML,
            $result
        );
    }

    #[Test]
    public function renderWithContextWritesCacheFile(): void
    {
        $edge = new Edge(
            new EdgeFileLoader(
                [
                    __DIR__ . '/tmpl',
                ]
            ),
            new EdgeFileCache(
                __DIR__ . '/../tmp'
            )
        );

        $result = $edge->renderWithContext(
            'context-cached',
            [
                'foo' => 'bar',
            ],
            $this
        );

        self::assertStringDataEquals(
            <<<HTML
            Windwalker\Edge\Test\EdgeTest
            HTML,
            $result
        );

        $path = __DIR__ . '/tmpl/context-cached.blade.php';

        self::assertStringDataEquals(
            <<<HTML
            <?php /* File: $path */ ?>
            <?php echo \$__edge->escape(\$this::class); ?>
            HTML,
            file_get_contents(
                __DIR__ . '/../tmp/~'
                . $edge->getCache()->getCacheKey($path)
            )
        );
    }

    #[Test]
    public function renderConditionalAssetsTemplate(): void
    {
        $this->instance->setCache(
            new EdgeFileCache(
                __DIR__ . '/../tmp'
            )->setDebug(true)
        );

        $result = $this->instance->renderWithContext(
            'conditional',
            [],
            $this
        );

        self::assertHtmlFormatEquals(
            <<<HTML
            <html lang="en">
                <head>
                    <title></title>
                    <script>console.log(123);</script>
                    <script>console.log(789);</script>
                    <link rel="stylesheet" href="foo.css" />
                </head>
                <body>
                    <div class="container"></div>
                    <link rel="stylesheet" href="yoo.css" />
                </body>
            </html>
            HTML,
            $result
        );
    }
}
