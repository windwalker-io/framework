<?php declare(strict_types=1);
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Validator\Test;

use Windwalker\Validator\Rule\RegexValidator;

/**
 * Test class of RegexValidator
 *
 * @since 2.0
 */
class RegexValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test instance.
     *
     * @var RegexValidator
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
        // Using Alnum to test
        $this->instance = new RegexValidator('^[a-zA-Z0-9]*$', 'i');
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
     * testValidate
     *
     * @return  void
     *
     * @cover  \Windwalker\Validator\Rule\AlnumValidator
     */
    public function testValidate()
    {
        $this->assertTrue($this->instance->validate('abc123cba456'));

        $this->assertFalse($this->instance->validate('abc123 cba456'));
        $this->assertFalse($this->instance->validate('abc_123.cba-456'));
    }

    /**
     * Method to test getRegex().
     *
     * @return void
     *
     * @covers \Windwalker\Validator\Rule\RegexValidator::getRegex
     * @TODO   Implement testGetRegex().
     */
    public function testGetRegex()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test setRegex().
     *
     * @return void
     *
     * @covers \Windwalker\Validator\Rule\RegexValidator::setRegex
     * @TODO   Implement testSetRegex().
     */
    public function testSetRegex()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getModifiers().
     *
     * @return void
     *
     * @covers \Windwalker\Validator\Rule\RegexValidator::getModifiers
     * @TODO   Implement testGetModifiers().
     */
    public function testGetModifiers()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test setModifiers().
     *
     * @return void
     *
     * @covers \Windwalker\Validator\Rule\RegexValidator::setModifiers
     * @TODO   Implement testSetModifiers().
     */
    public function testSetModifiers()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
