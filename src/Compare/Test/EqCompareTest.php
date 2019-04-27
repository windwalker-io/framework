<?php
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Compare\Test;

use Windwalker\Compare\EqCompare;

/**
 * Test class of EqCompare
 *
 * @since 2.0
 */
class EqCompareTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test instance.
     *
     * @var EqCompare
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
        $this->instance = new EqCompare('flower', 'sakura');
    }

    /**
     * testToString
     *
     * @return  void
     */
    public function testToString()
    {
        $this->assertEquals('flower = sakura', $this->instance->toString());
    }

    /**
     * testToString
     *
     * @return  void
     */
    public function testCompare()
    {
        $compare = new EqCompare(1, '1');

        $this->assertTrue($compare->compare());
        $this->assertFalse($compare->compare(true));
    }
}
