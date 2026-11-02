<?php

declare(strict_types=1);

namespace Windwalker\Edge\Test;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Edge\Cache\EdgeFileCache;
use Windwalker\Edge\Component\ComponentExtension;
use Windwalker\Edge\Edge;
use Windwalker\Edge\Loader\EdgeFileLoader;
use Windwalker\Edge\Test\Component\FooComponent;
use Windwalker\Test\Traits\DOMTestTrait;

/**
 * The ComponentTest class.
 */
class ComponentTest extends TestCase
{
    use DOMTestTrait;

    /**
     * Test instance.
     *
     * @var  Edge
     */
    protected ?Edge $instance;

    #[Before]
    public function setUpEdgeWithComponents(): void
    {
        $this->instance = new Edge(
            new EdgeFileLoader(
                [
                    __DIR__ . '/tmpl',
                ]
            ),
            new EdgeFileCache(__DIR__ . '/../tmp')->setDebug(true)
        );

        $this->instance->addExtension($ext = new ComponentExtension($this->instance));

        $ext->registerComponent('foo', FooComponent::class);

        // Clear tmp
        $files = glob(__DIR__ . '/../tmp/~*');

        foreach ($files as $file) {
            unlink($file);
        }
    }

    #[Test]
    public function renderClassAnonymousAndDynamicComponents(): void
    {
        $v = $this->instance->render('components.tags');

        self::assertDomStringEqualsDomString(
            <<<HTML
<div>
    <h3>Class Component</h3>
    Foo Hello World

    <h3>Anonymous Component</h3>
    <div id="foo" class="alert alert-TTT" flower="sakura" x-data foo="Foo Attr" @click="toGo()">
        Foo Component: TTT - Message: unknown message

        World
    </div>

    <h3>Dynamic Component</h3>
    Foo Hello YOO
</div>
HTML,
            $v
        );
    }
}
