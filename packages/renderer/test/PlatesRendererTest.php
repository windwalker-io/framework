<?php

declare(strict_types=1);

namespace Windwalker\Renderer\Test;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Windwalker\Renderer\PlatesRenderer;
use Windwalker\Test\Traits\DOMTestTrait;

/**
 * Test class of PlatesRenderer
 *
 * @since 2.0.9
 */
class PlatesRendererTest extends TestCase
{
    use DOMTestTrait;

    /**
     * Test instance.
     *
     * @var  PlatesRenderer
     */
    protected $instance;

    /**
     * Property path.
     *
     * @var  string
     */
    protected static $path;

    #[Before]
    protected function createRenderer(): void
    {
        static::$path = realpath(__DIR__ . '/Tmpl/plates');

        if (!static::$path) {
            throw new RuntimeException('Path not exists');
        }

        $this->instance = new PlatesRenderer(['paths' => [static::$path]]);
    }

    #[Test]
    public function handleLayoutAppendsExtensionAndConvertsDots(): void
    {
        self::assertSame('foo/bar.phtml', $this->instance->handleLayout('foo.bar', 'phtml'));
    }

    #[Test]
    public function handleLayoutKeepsLayoutAlreadyEndingWithExtension(): void
    {
        self::assertSame('profile.phtml', $this->instance->handleLayout('profile.phtml', 'phtml'));
    }

    #[Test]
    public function renderProfileTemplateWithLayoutAndData(): void
    {
        $html = $this->instance->render('profile', ['name' => 'Tony Stark']);

        $expect = <<<HTML
<html>
<head>
    <title>User Profile</title>
</head>
<body>

<img src="logo.png">

<div id="page">
    <h1>Welcome!</h1>
<p>Hello Tony Stark</p>
</div>

<div id="sidebar">
        <ul>
    <li><a href="/link">Example Link</a></li>
    <li><a href="/link">Example Link</a></li>
    <li><a href="/link">Example Link</a></li>
    <li><a href="/link">Example Link</a></li>
    <li><a href="/link">Example Link</a></li>
</ul>
    </div>

</body>
</html>
HTML;

        self::assertDomStringEqualsDomString($expect, $html);
    }
}
