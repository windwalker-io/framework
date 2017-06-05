<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2017 Asikart.
 * @license    __LICENSE__
 */

namespace Windwalker\Validator\Rule\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\Validator\Rule\PhpTypeValidator;

/**
 * Test class of \Windwalker\Validator\Rule\PhpTypeValidator
 *
 * @since 3.2
 */
class PhpTypeValidatorTest extends TestCase
{
	/**
	 * Test instance.
	 *
	 * @var PhpTypeValidator
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
		$this->instance = new PhpTypeValidator('ARRAY');
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
	* Method to test __construct().
	*
	* @return void
	*
	* @covers \Windwalker\Validator\Rule\PhpTypeValidator::__construct
	* @covers \Windwalker\Validator\Rule\PhpTypeValidator::getType
	*/
	public function test__construct()
	{
		self::assertEquals('array', $this->instance->getType());
	}

	/**
	* Method to test setType().
	*
	* @return void
	*
	* @covers \Windwalker\Validator\Rule\PhpTypeValidator::setType
	*/
	public function testValidate()
	{
		self::assertTrue($this->instance->validate([]));
		self::assertFalse($this->instance->validate(''));

		self::assertTrue($this->instance->setType(\stdClass::class)->validate(new \stdClass));
		self::assertTrue($this->instance->setType('numeric')->validate('1.2'));
		self::assertTrue($this->instance->setType('float')->validate(1.2));
		self::assertTrue($this->instance->setType('double')->validate(1.2));
		self::assertTrue($this->instance->setType('scalar')->validate('abc'));
		self::assertFalse($this->instance->setType('scalar')->validate(null));
		self::assertFalse($this->instance->setType('scalar')->validate([]));
		self::assertTrue($this->instance->setType('callable')->validate('trim'));
		self::assertTrue($this->instance->setType('array')->validate([]));
		self::assertTrue($this->instance->setType('object')->validate(new \stdClass));
	}
}
