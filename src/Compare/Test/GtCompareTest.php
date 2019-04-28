<?php declare(strict_types=1);
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Compare\Test;

use Windwalker\Compare\GtCompare;

/**
 * Test class of GtCompare
 *
 * @since 2.0
 */
class GtCompareTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test instance.
     *
     * @var GtCompare
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
        $this->instance = new GtCompare('flower', 'sakura');
    }

    /**
     * testToString
     *
     * @return  void
     */
    public function testToString()
    {
        $this->assertEquals('flower > sakura', $this->instance->toString());
    }

    /**
     * testToString
     *
     * @return  void
     */
    public function testCompare()
    {
        $compare = new GtCompare(5, '1');

        $this->assertTrue($compare->compare());

        $compare = new GtCompare(4, 6);

        $this->assertFalse($compare->compare());
    }
}
