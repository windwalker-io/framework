<?php declare(strict_types=1);
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Html\Test;

use Windwalker\Dom\Helper\DomHelper;
use Windwalker\Html\Option;

/**
 * Test class of Option
 *
 * @since 2.0
 */
class OptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test instance.
     *
     * @var Option
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
        $this->instance = new Option('flower', 'sakura', ['class' => 'item']);
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
     * testToString
     *
     * @return  void
     *
     * @covers \Windwalker\Html\Option::toString
     *
     */
    public function testToString()
    {
        $this->assertEquals(
            DomHelper::minify('<option class="item" value="sakura">flower</option>'),
            DomHelper::minify($this->instance)
        );
    }

    /**
     * Method to test getValue().
     *
     * @return void
     *
     * @covers \Windwalker\Html\Option::getValue
     */
    public function testGetAndSetValue()
    {
        $this->instance->setValue('sunflower');

        $this->assertEquals('sunflower', $this->instance->getValue());
    }
}
