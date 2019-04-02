<?php
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Compare\Test;

use Windwalker\Compare\LteCompare;

/**
 * Test class of LteCompare
 *
 * @since 2.0
 */
class LteCompareTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test instance.
     *
     * @var LteCompare
     */
    protected $instance;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->instance = new LteCompare('flower', 'sakura');
    }

    /**
     * testToString
     *
     * @return  void
     */
    public function testToString()
    {
        $this->assertEquals('flower <= sakura', $this->instance->toString());
    }

    /**
     * testToString
     *
     * @return  void
     */
    public function testCompare()
    {
        $compare = new LteCompare(5, 5);

        $this->assertTrue($compare->compare());

        $compare = new LteCompare(4, 5);

        $this->assertTrue($compare->compare());

        $compare = new LteCompare(6, 5);

        $this->assertFalse($compare->compare());
    }
}
