<?php declare(strict_types=1);
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Profiler\Test;

use Windwalker\Profiler\Benchmark;

/**
 * Test class of Benchmark
 *
 * @since 2.0
 */
class BenchmarkTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test instance.
     *
     * @var Benchmark
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
        $this->instance = new Benchmark();

        $this->instance->setTimeFormat(Benchmark::MILLI_SECOND);
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
     * Method to test setTimeFormat().
     *
     * @return void
     *
     * @covers \Windwalker\Profiler\Benchmark::setTimeFormat
     * @TODO   Implement testSetTimeFormat().
     */
    public function testSetTimeFormat()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test addTask().
     *
     * @return void
     *
     * @covers \Windwalker\Profiler\Benchmark::addTask
     * @TODO   Implement testAddTask().
     */
    public function testAddTask()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test execute().
     *
     * @return void
     *
     * @covers \Windwalker\Profiler\Benchmark::execute
     */
    public function testExecute()
    {
        $count = 0;

        $this->instance->addTask(
            'md5',
            function () use (&$count) {
                $count++;

                md5('Windwalker');
            }
        )->addTask(
            'sha1',
            function () use (&$count) {
                $count++;

                sha1('Windwalker');
            }
        );

        $this->instance
            ->setTimeFormat(Benchmark::MICRO_SECOND)
            ->execute(5);

        $results = $this->instance->getResults(Benchmark::SORT_DESC);

        $this->assertEquals(['md5', 'sha1'], array_keys($results));

        $this->assertEquals(10, $count);

        $this->instance->setRenderOneHandler(
            function ($name, $result, $round, $format) {
                return $name . ':123123';
            }
        );

        $results = $this->instance->render(true, Benchmark::SORT_DESC);

        $this->assertEquals("md5:123123\nsha1:123123", $results);
    }

    /**
     * Method to test getResults().
     *
     * @return void
     *
     * @covers \Windwalker\Profiler\Benchmark::getResults
     * @TODO   Implement testGetResults().
     */
    public function testGetResults()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getResult().
     *
     * @return void
     *
     * @covers \Windwalker\Profiler\Benchmark::getResult
     * @TODO   Implement testGetResult().
     */
    public function testGetResult()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test renderOne().
     *
     * @return void
     *
     * @covers \Windwalker\Profiler\Benchmark::renderOne
     * @TODO   Implement testRenderOne().
     */
    public function testRenderOne()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test render().
     *
     * @return void
     *
     * @covers \Windwalker\Profiler\Benchmark::render
     * @TODO   Implement testRender().
     */
    public function testRender()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
