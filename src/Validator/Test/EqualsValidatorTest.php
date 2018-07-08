<?php
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Validator\Test;

use Windwalker\Validator\Rule\EqualsValidator;

/**
 * Test class of EqualsValidator
 *
 * @since 2.0
 */
class EqualsValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * getInstance
     *
     * @param string $compare
     * @param bool   $strict
     *
     * @return  EqualsValidator
     */
    protected function getInstance($compare, $strict = false)
    {
        return new EqualsValidator($compare, $strict);
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
     * Method to test test().
     *
     * @return void
     *
     * @covers \Windwalker\Validator\Rule\EqualsValidator::test
     */
    public function testValidate()
    {
        $this->assertTrue($this->getInstance('abc')->validate('abc'));

        $this->assertTrue($this->getInstance('1')->validate(1));

        $this->assertTrue($this->getInstance(true)->validate(1));

        $this->assertFalse($this->getInstance(true, true)->validate(1));

        $this->assertFalse($this->getInstance(1, true)->validate('1'));

        $this->assertFalse($this->getInstance(1.5, true)->validate('1.5'));
    }
}
