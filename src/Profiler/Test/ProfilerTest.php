<?php
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Profiler\Test;

use Windwalker\Environment\PhpHelper;
use Windwalker\Profiler\Point\Point;
use Windwalker\Profiler\Profiler;
use Windwalker\Profiler\Renderer\DefaultRenderer;
use Windwalker\Test\TestHelper;

/**
 * Test class of Profiler
 *
 * @since 2.0
 */
class ProfilerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test instance.
     *
     * @var Profiler
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
        $this->instance = new Profiler('test');
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
     * Tests the constructor.
     *
     * @return  void
     *
     * @covers  \Windwalker\Profiler\Profiler::__construct
     * @since   2.0
     */
    public function test__construct()
    {
        $this->assertEquals('test', $this->instance->getName());
        $this->assertInstanceOf('Windwalker\Profiler\Renderer\DefaultRenderer', $this->instance->getRenderer());
        $this->assertEmpty($this->instance->getPoints());
        $this->assertEquals(PhpHelper::isHHVM(), $this->instance->getMemoryRealUsage());

        $renderer = new DefaultRenderer();
        $pointOne = new Point('start');
        $pointTwo = new Point('two', 1, 1);
        $points = [
            'start' => $pointOne,
            'two' => $pointTwo,
        ];

        $profiler = new Profiler('bar', $renderer, $points, false);

        $this->assertEquals('bar', $profiler->getName());
        $this->assertSame($renderer, $profiler->getRenderer());
        $this->assertEquals($points, $profiler->getPoints());
        $this->assertFalse($profiler->getMemoryRealUsage());
    }

    /**
     * Method to test getName().
     *
     * @return void
     *
     * @covers \Windwalker\Profiler\Profiler::getName
     */
    public function testGetName()
    {
        $this->assertEquals('test', $this->instance->getName());
    }

    /**
     * Method to test mark().
     *
     * @return void
     *
     * @covers \Windwalker\Profiler\Profiler::mark
     */
    public function testMark()
    {
        $this->instance->mark('one');
        $this->instance->mark('two');
        $this->instance->mark('three');

        $this->assertTrue($this->instance->hasPoint('one'));
        $this->assertTrue($this->instance->hasPoint('two'));
        $this->assertTrue($this->instance->hasPoint('three'));

        // Assert the first point has a time and memory = 0
        $firstPoint = $this->instance->getPoint('one');

        $this->assertSame(0.0, $firstPoint->getTime());
        $this->assertSame(0, $firstPoint->getMemory());

        // Assert the other points have a time and memory != 0
        $secondPoint = $this->instance->getPoint('two');

        $this->assertGreaterThan(0, $secondPoint->getTime());
        $this->assertGreaterThan(0, $secondPoint->getMemory());

        $thirdPoint = $this->instance->getPoint('three');

        $this->assertGreaterThan(0, $thirdPoint->getTime());
        $this->assertGreaterThan(0, $thirdPoint->getMemory());

        // Assert the third point has greater values than the second point.
        $this->assertGreaterThan($secondPoint->getTime(), $thirdPoint->getTime());
        $this->assertGreaterThan($secondPoint->getMemory(), $thirdPoint->getMemory());
    }

    /**
     * Method to test hasPoint().
     *
     * @return void
     *
     * @covers \Windwalker\Profiler\Profiler::hasPoint
     */
    public function testHasPoint()
    {
        $this->assertFalse($this->instance->hasPoint('test'));

        $this->instance->mark('test');
        $this->assertTrue($this->instance->hasPoint('test'));
    }

    /**
     * Method to test getPoint().
     *
     * @return void
     *
     * @covers \Windwalker\Profiler\Profiler::getPoint
     */
    public function testGetPoint()
    {
        $this->assertNull($this->instance->getPoint('foo'));

        $this->instance->mark('start');

        $point = $this->instance->getPoint('start');

        $this->assertInstanceOf('Windwalker\Profiler\Point\Point', $point);
        $this->assertEquals('start', $point->getName());
    }

    /**
     * Method to test getTimeBetween().
     *
     * @return void
     *
     * @covers \Windwalker\Profiler\Profiler::getTimeBetween
     */
    public function testGetTimeBetween()
    {
        $first = new Point('start');
        $second = new Point('stop', 1.5);

        $profiler = new Profiler('test', null, [$first, $second]);

        $this->assertSame(1.5, $profiler->getTimeBetween('start', 'stop'));
        $this->assertSame(1.5, $profiler->getTimeBetween('stop', 'start'));
    }

    /**
     * Method to test getMemoryBetween().
     *
     * @return void
     *
     * @covers \Windwalker\Profiler\Profiler::getMemoryBetween
     */
    public function testgetMemoryBetween()
    {
        $first = new Point('start');
        $second = new Point('stop', 0, 1000);

        $profiler = new Profiler('test', null, [$first, $second]);

        $this->assertSame(1000, $profiler->getMemoryBetween('start', 'stop'));
        $this->assertSame(1000, $profiler->getMemoryBetween('stop', 'start'));
    }

    /**
     * Method to test getMemoryPeakBytes().
     *
     * @return void
     *
     * @throws \ReflectionException
     * @covers \Windwalker\Profiler\Profiler::getMemoryPeakBytes
     */
    public function testGetMemoryPeakBytes()
    {
        TestHelper::setValue($this->instance, 'memoryPeakBytes', 10);

        $this->assertEquals(10, $this->instance->getMemoryPeakBytes());
    }

    /**
     * Method to test getPoints().
     *
     * @return void
     *
     * @covers \Windwalker\Profiler\Profiler::getPoints
     * @TODO   Implement testGetPoints().
     */
    public function testGetPoints()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test setRenderer().
     *
     * @return void
     *
     * @throws \ReflectionException
     * @covers \Windwalker\Profiler\Profiler::setRenderer
     */
    public function testGetAndSetRenderer()
    {
        // Reset the property.
        TestHelper::setValue($this->instance, 'renderer', null);

        $renderer = new DefaultRenderer();

        $this->instance->setRenderer($renderer);

        $this->assertSame($renderer, $this->instance->getRenderer());
    }

    /**
     * Method to test render().
     *
     * @return void
     *
     * @covers \Windwalker\Profiler\Profiler::render
     */
    public function testRender()
    {
        $first = new Point('start');
        $second = new Point('stop', 5, 1000);

        $profiler = new Profiler('test', null, [$first, $second]);

        $expected = <<<RESULT
test 0.000 seconds (+0.000); 0.00 MB (0.000) - start
test 5.000 seconds (+5.000); 0.00 MB (+0.001) - stop
RESULT;

        $this->assertEquals(
            str_replace(["\r", "\n"], '', trim($expected)),
            str_replace(["\r", "\n"], '', trim($profiler->render()))
        );
    }

    /**
     * Method to test __toString().
     *
     * @return void
     *
     * @covers \Windwalker\Profiler\Profiler::__toString
     * @TODO   Implement test__toString().
     */
    public function test__toString()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getIterator().
     *
     * @return void
     *
     * @covers \Windwalker\Profiler\Profiler::getIterator
     */
    public function testGetIterator()
    {
        // Create 3 points.
        $first = new Point('test');
        $second = new Point('second', 1.5, 1000);
        $third = new Point('third', 2.5, 2000);

        $points = [
            'test' => $first,
            'second' => $second,
            'third' => $third,
        ];

        // Create a profiler and inject the points.
        $profiler = new Profiler('test', null, $points);

        $iterator = $profiler->getIterator();

        $this->assertEquals(iterator_to_array($iterator), $points);
    }

    /**
     * Method to test count().
     *
     * @return void
     *
     * @covers \Windwalker\Profiler\Profiler::count
     */
    public function testCount()
    {
        $this->assertCount(0, $this->instance);

        $this->instance->mark('start');
        $this->instance->mark('foo');
        $this->instance->mark('end');

        $this->assertCount(3, $this->instance);
    }
}
