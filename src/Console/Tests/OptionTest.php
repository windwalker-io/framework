<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Console\Tests;

use Windwalker\Console\Command\RootCommand;
use Windwalker\Console\Option\Option;

/**
 * Class OptionTest
 *
 * @since  1.0
 */
class OptionTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var Option
	 */
	protected $instance;

	/**
	 * Test command instance.
	 *
	 * @var  RootCommand
	 */
	protected $command;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 *
	 * @since  1.0
	 */
	protected function setUp()
	{
		$command = new RootCommand('default');

		$this->instance = $option = new Option(array('y', 'yell'), 0, 'desc', Option::IS_GLOBAL);

		$command->addOption($option);

		$this->command = $command;
	}

	/**
	 * Option test provider.
	 *
	 * @return array
	 *
	 * @since  1.0
	 */
	public function optionProvider()
	{
		return array(
			array(
				array(
					'y' => array('y', 'yell', 'Y')
				),

				array(
					'yell' => array('y', 'yell', 'Y')
				),

				array(
					'Y' => array('y', 'yell', 'Y')
				)
			)
		);
	}

	/**
	 * Test set & get alias.
	 *
	 * @return void
	 *
	 * @since  1.0
	 */
	public function testSetAndGetAlias()
	{
		$this->instance->setAlias(array('yell', 'Y'));

		$alias = $this->instance->getAlias();

		$this->assertEquals(array('yell', 'Y'), $alias);
	}

	/**
	 * Test set & get default value.
	 *
	 * @return void
	 *
	 * @since  1.0
	 */
	public function testSetAndGetDefault()
	{
		$this->instance->setDefault(0);

		$this->assertEquals(0, $this->instance->getDefault(), 'Default value not matched.');
	}

	/**
	 * Test set & get description.
	 *
	 * @return void
	 *
	 * @since  1.0
	 */
	public function testSetAndGetDescription()
	{
		$this->instance->setDescription('Desc');

		$this->assertEquals('Desc', $this->instance->getDescription(), 'Description value not matched.');
	}

	/**
	 * Test set & get name.
	 *
	 * @return void
	 *
	 * @since  1.0
	 */
	public function testSetAndGetName()
	{
		$this->instance->setName('defaulttt');

		$this->assertEquals('defaulttt', $this->instance->getName(), 'Name value not matched.');
	}

	/**
	 * Test set & get input.
	 *
	 * @return void
	 *
	 * @since  1.0
	 */
	public function testSetAndGeTInput()
	{
		$this->assertEquals($this->instance->getInput(), $this->command->getInput(), 'Input not the same instance.');
	}

	/**
	 * Test get value.
	 *
	 * @param   array  $inputs  The input option.
	 *
	 * @dataProvider  optionProvider
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testGetValue($inputs)
	{
		foreach ($inputs as $key => $vals)
		{
			$this->instance->getInput()->set($key, 1);

			foreach ($vals as $val)
			{
				$this->assertEquals(1, $this->instance->getValue($val));
			}
		}

		// Filter
		$this->instance->getInput()->set('y', 'flower sakura');

		$this->assertEquals('flower sakura', $this->instance->getValue('y'), 'Default input filter should string.');
	}

	/**
	 * Test global.
	 *
	 * @return void
	 *
	 * @since  1.0
	 */
	public function testGlobal()
	{
		$this->command->addOption(
			'k',
			'default',
			'k desc',
			Option::IS_GLOBAL
		)
		->addCommand('kkk');

		$kkk = $this->command->getChild('kkk');

		$options = $kkk->getAllOptions();

		$this->assertArrayHasKey('k', $options);
	}
}
