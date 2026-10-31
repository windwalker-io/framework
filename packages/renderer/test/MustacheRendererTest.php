<?php

declare(strict_types=1);

namespace Windwalker\Renderer\Test;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;
use Windwalker\Renderer\MustacheRenderer;
use Windwalker\Test\Traits\DOMTestTrait;

/**
 * Test class of MustacheRenderer
 *
 * @since 2.0
 */
class MustacheRendererTest extends TestCase
{
    use DOMTestTrait;

    /**
     * Test instance.
     *
     * @var MustacheRenderer
     */
    protected MustacheRenderer $instance;

    /**
     * Property path.
     *
     * @var string
     */
    protected static string $path = '';

    #[Before]
    protected function createRenderer(): void
    {
        static::$path = realpath(__DIR__ . '/Tmpl/mustache');

        if (!static::$path) {
            throw new RuntimeException('Path not exists');
        }

        $this->instance = new MustacheRenderer(['paths' => [static::$path]]);
    }

    /**
     * @throws Throwable
     */
    #[Test]
    public function renderHelloTemplateWithObjectData(): void
    {
        $html = $this->instance->render('hello', ['chris' => new Chris()]);

        $expect = <<<HTML
Hello Chris
You have just won $10000!
Well, $6000, after taxes.
HTML;

        self::assertStringSafeEquals($expect, $html);
    }

    #[Test]
    public function getEngineReturnsMustacheEngine(): void
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    #[Test]
    public function setEngineReplacesEngine(): void
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    #[Test]
    public function getLoaderReturnsTemplateLoader(): void
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    #[Test]
    public function setLoaderReplacesTemplateLoader(): void
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}

// phpcs:disable

/**
 * The Chris class.
 *
 * @since  2.0
 */
class Chris
{
    /**
     * Property name.
     *
     * @var  string
     */
    public $name = "Chris";

    /**
     * Property value.
     *
     * @var  int
     */
    public $value = 10000;

    /**
     * taxed_value
     *
     * @return float|int
     */
    public function taxed_value(): float|int
    {
        return $this->value - ($this->value * 0.4);
    }

    /**
     * Property in_ca.
     *
     * @var  bool
     */
    public $in_ca = true;
}
// phpcs:enable
