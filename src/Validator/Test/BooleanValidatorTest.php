<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Validator\Test;

use Windwalker\Validator\Rule\BooleanValidator;

/**
 * Test class of BooleanValidator
 *
 * @since 2.0
 */
class BooleanValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test instance.
     *
     * @var BooleanValidator
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
        $this->instance = new BooleanValidator();
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
     * testValidate
     *
     * @return  void
     *
     * @cover  \Windwalker\Validator\Rule\BooleanValidator
     */
    public function testValidate()
    {
        $this->assertTrue($this->instance->validate(true));
        $this->assertTrue($this->instance->validate(false));
        $this->assertTrue($this->instance->validate('1'));
        $this->assertTrue($this->instance->validate('0'));
        $this->assertTrue($this->instance->validate('true'));
        $this->assertTrue($this->instance->validate('false'));

        $this->assertFalse($this->instance->validate('abc123 cba456'));
        $this->assertFalse($this->instance->validate(''));
    }
}
