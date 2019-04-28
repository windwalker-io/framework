<?php declare(strict_types=1);
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 SMS Taiwan, Inc.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Edge\Test;

use Windwalker\Dom\Test\AbstractDomTestCase;
use Windwalker\Edge\Edge;
use Windwalker\Edge\Loader\EdgeFileLoader;

/**
 * Test class of Edge
 *
 * @since 3.0
 */
class EdgeTest extends AbstractDomTestCase
{
    /**
     * Test instance.
     *
     * @var Edge
     */
    protected $instance;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->instance = new Edge(
            new EdgeFileLoader(
                [
                    __DIR__ . '/tmpl',
                ]
            )
        );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
    }

    /**
     * Method to test render().
     *
     * @return void
     *
     * @throws \Windwalker\Edge\Exception\EdgeException
     * @covers \Windwalker\Edge\Edge::render
     */
    public function testRender()
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

    /**
     * Method to test escape().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::escape
     * @TODO   Implement testEscape().
     */
    public function testEscape()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test startSection().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::startSection
     * @TODO   Implement testStartSection().
     */
    public function testStartSection()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test inject().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::inject
     * @TODO   Implement testInject().
     */
    public function testInject()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test yieldSection().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::yieldSection
     * @TODO   Implement testYieldSection().
     */
    public function testYieldSection()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test stopSection().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::stopSection
     * @TODO   Implement testStopSection().
     */
    public function testStopSection()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test appendSection().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::appendSection
     * @TODO   Implement testAppendSection().
     */
    public function testAppendSection()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test yieldContent().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::yieldContent
     * @TODO   Implement testYieldContent().
     */
    public function testYieldContent()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test startPush().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::startPush
     * @TODO   Implement testStartPush().
     */
    public function testStartPush()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test stopPush().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::stopPush
     * @TODO   Implement testStopPush().
     */
    public function testStopPush()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test yieldPushContent().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::yieldPushContent
     * @TODO   Implement testYieldPushContent().
     */
    public function testYieldPushContent()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test renderEach().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::renderEach
     * @TODO   Implement testRenderEach().
     */
    public function testRenderEach()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test flushSections().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::flushSections
     * @TODO   Implement testFlushSections().
     */
    public function testFlushSections()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test flushSectionsIfDoneRendering().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::flushSectionsIfDoneRendering
     * @TODO   Implement testFlushSectionsIfDoneRendering().
     */
    public function testFlushSectionsIfDoneRendering()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test incrementRender().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::incrementRender
     * @TODO   Implement testIncrementRender().
     */
    public function testIncrementRender()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test decrementRender().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::decrementRender
     * @TODO   Implement testDecrementRender().
     */
    public function testDecrementRender()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test doneRendering().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::doneRendering
     * @TODO   Implement testDoneRendering().
     */
    public function testDoneRendering()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test prepareExtensions().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::prepareExtensions
     * @TODO   Implement testPrepareExtensions().
     */
    public function testPrepareExtensions()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test arrayExcept().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::arrayExcept
     * @TODO   Implement testArrayExcept().
     */
    public function testArrayExcept()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getGlobals().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::getGlobals
     * @TODO   Implement testGetGlobals().
     */
    public function testGetGlobals()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test addGlobal().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::addGlobal
     * @TODO   Implement testAddGlobal().
     */
    public function testAddGlobal()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test removeGlobal().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::removeGlobal
     * @TODO   Implement testRemoveGlobal().
     */
    public function testRemoveGlobal()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getGlobal().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::getGlobal
     * @TODO   Implement testGetGlobal().
     */
    public function testGetGlobal()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test setGlobals().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::setGlobals
     * @TODO   Implement testSetGlobals().
     */
    public function testSetGlobals()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getCompiler().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::getCompiler
     * @TODO   Implement testGetCompiler().
     */
    public function testGetCompiler()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test setCompiler().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::setCompiler
     * @TODO   Implement testSetCompiler().
     */
    public function testSetCompiler()
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
     * @covers \Windwalker\Edge\Edge::getLoader
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
     * @covers \Windwalker\Edge\Edge::setLoader
     * @TODO   Implement testSetLoader().
     */
    public function testSetLoader()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test addExtension().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::addExtension
     * @TODO   Implement testAddExtension().
     */
    public function testAddExtension()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test removeExtension().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::removeExtension
     * @TODO   Implement testRemoveExtension().
     */
    public function testRemoveExtension()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test hasExtension().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::hasExtension
     * @TODO   Implement testHasExtension().
     */
    public function testHasExtension()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getExtension().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::getExtension
     * @TODO   Implement testGetExtension().
     */
    public function testGetExtension()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getExtensions().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::getExtensions
     * @TODO   Implement testGetExtensions().
     */
    public function testGetExtensions()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test setExtensions().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::setExtensions
     * @TODO   Implement testSetExtensions().
     */
    public function testSetExtensions()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getCache().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::getCache
     * @TODO   Implement testGetCache().
     */
    public function testGetCache()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test setCache().
     *
     * @return void
     *
     * @covers \Windwalker\Edge\Edge::setCache
     * @TODO   Implement testSetCache().
     */
    public function testSetCache()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
