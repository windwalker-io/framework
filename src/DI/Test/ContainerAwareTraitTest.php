<?php
/**
 * Part of windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\DI\Test;

use Windwalker\DI\Container;
use Windwalker\DI\ContainerAwareTrait;

/**
 * The ContainerAwareTraitTest class.
 * 
 * @since  2.0
 */
class ContainerAwareTraitTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * Property instance.
	 *
	 * @var  ContainerAwareTrait
	 */
	protected $instance = null;

	/**
	 * setUp
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		// Only run tests on PHP 5.4+
		if (version_compare(PHP_VERSION, '5.4', '<'))
		{
			static::markTestSkipped('Tests are not present in PHP 5.4');
		}

		$this->instance = $this->getObjectForTrait('Windwalker\\DI\\ContainerAwareTrait');
	}

	/**
	 * Tests calling getContainer() without a Container object set
	 *
	 * @return  void
	 *
	 * @expectedException   \UnexpectedValueException
	 */
	public function testGetContainerException()
	{
		$this->instance->getContainer();
	}

	/**
	 * Tests calling getContainer() with a Container object set
	 *
	 * @return  void
	 */
	public function testGetAndSetContainer()
	{
		$this->instance->setContainer(new Container);

		$this->assertInstanceOf('Windwalker\\DI\\Container', $this->instance->getContainer());
	}
}
