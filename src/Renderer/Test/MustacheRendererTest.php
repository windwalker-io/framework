<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Renderer\Test;

use Windwalker\Dom\Test\AbstractDomTestCase;
use Windwalker\Renderer\MustacheRenderer;

/**
 * Test class of MustacheRenderer
 *
 * @since 2.0
 */
class MustacheRendererTest extends AbstractDomTestCase
{
    /**
     * Test instance.
     *
     * @var MustacheRenderer
     */
    protected $instance;

    /**
     * Property path.
     *
     * @var string
     */
    protected static $path;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        static::$path = realpath(__DIR__ . '/Tmpl/mustache');

        if (!static::$path) {
            throw new \RuntimeException('Path not exists');
        }

        $this->instance = new MustacheRenderer(static::$path);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown()
    {
    }

    /**
     * Method to test render().
     *
     * @return void
     *
     * @covers \Windwalker\Renderer\MustacheRenderer::render
     */
    public function testRender()
    {
        $html = $this->instance->render('hello', new Chris);

        $expect = <<<HTML
Hello Chris
You have just won $10000!
Well, $6000, after taxes.
HTML;

        $this->assertDomStringEqualsDomString($expect, $html);
    }

    /**
     * Method to test getEngine().
     *
     * @return void
     *
     * @covers \Windwalker\Renderer\MustacheRenderer::getEngine
     * @TODO   Implement testGetEngine().
     */
    public function testGetEngine()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test setEngine().
     *
     * @return void
     *
     * @covers \Windwalker\Renderer\MustacheRenderer::setEngine
     * @TODO   Implement testSetEngine().
     */
    public function testSetEngine()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getLoader().
     *
     * @return void
     *
     * @covers \Windwalker\Renderer\MustacheRenderer::getLoader
     * @TODO   Implement testGetLoader().
     */
    public function testGetLoader()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test setLoader().
     *
     * @return void
     *
     * @covers \Windwalker\Renderer\MustacheRenderer::setLoader
     * @TODO   Implement testSetLoader().
     */
    public function testSetLoader()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}

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
     * @return  int
     */
    public function taxed_value()
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
