<?php

declare(strict_types=1);

namespace Windwalker\Renderer\Test;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Windwalker\Renderer\TwigRenderer;
use Windwalker\Renderer\Test\Stub\StubTwigExtension;
use Windwalker\Test\Traits\DOMTestTrait;

/**
 * Test class of TwigRenderer
 *
 * @since 2.0
 */
class TwigRendererTest extends TestCase
{
    use DOMTestTrait;

    /**
     * Property path.
     *
     * @var  string
     */
    protected static $path;

    /**
     * Test instance.
     *
     * @var  TwigRenderer
     */
    protected $instance;

    #[Before]
    protected function createRenderer(): void
    {
        static::$path = realpath(__DIR__ . '/Tmpl/twig');

        if (!static::$path) {
            throw new RuntimeException('Path not exists');
        }

        $this->instance = new TwigRenderer(['paths' => static::$path]);
    }

    #[Test]
    public function handleLayoutAppendsExtensionAndConvertsDots(): void
    {
        self::assertSame('foo/bar.twig', $this->instance->handleLayout('foo.bar', 'twig'));
    }

    #[Test]
    public function handleLayoutKeepsLayoutAlreadyEndingWithExtension(): void
    {
        self::assertSame('foo/bar.twig', $this->instance->handleLayout('foo/bar.twig', 'twig'));
    }

    #[Test]
    public function debugOptionRegistersDebugExtension(): void
    {
        $twig = new TwigRenderer(['paths' => static::$path, 'debug' => true])->createEngine();

        self::assertTrue($twig->hasExtension(DebugExtension::class));
        self::assertFalse($this->instance->createEngine()->hasExtension(DebugExtension::class));
    }

    #[Test]
    public function extendedEngineExposesStubExtensionFilterFunctionAndGlobal(): void
    {
        $this->instance->extend(
            function (Environment $twig) {
                $twig->addExtension(new StubTwigExtension());

                return $twig;
            }
        );

        $html = $this->instance->render('ext-test.twig');

        self::assertStringContainsString('<li>peace</li>', $html);
        self::assertStringContainsString('<li>sakura</li>', $html);
    }

    #[Test]
    public function renderTemplateWithIncludedPartials(): void
    {
        $html = $this->instance->render('default.twig');

        $expect = <<<HTML
<div id="global">
    <p> (_global/global) Lorem ipsum dolor sit amet</p>
    <p> (default) Nulla sed libero sem. Praesent ac dignissim risus.</p>
    <p> (foo/bar) Phasellus vitae bibendum neque, quis suscipit urna. Fusce eu odio ante.</p>
    <p> (_global/global) Suspendisse finibus fermentum massa ut tempus. </p>
</div>
HTML;

        self::assertDomStringEqualsDomString($expect, $html);
    }
}
