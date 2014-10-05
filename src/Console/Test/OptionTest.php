<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Console\Test;

use Windwalker\Console\Command\RootCommand;
use Windwalker\Console\Option\Option;

/**
 * Class OptionTest
 *
 * @since  {DEPLOY_VERSION}
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
	 * @since  {DEPLOY_VERSION}
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
	 * @since  {DEPLOY_VERSION}
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
	 * @since  {DEPLOY_VERSION}
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
	 * @since  {DEPLOY_VERSION}
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
	 * @since  {DEPLOY_VERSION}
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
	 * @since  {DEPLOY_VERSION}
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
	 * @since  {DEPLOY_VERSION}
	 */
	public function testSetAndGetIO()
	{
		$this->assertEquals($this->instance->getIO(), $this->command->getIO(), 'IO not the same instance.');
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
	 * @since   {DEPLOY_VERSION}
	 */
	public function testGetValue($inputs)
	{
		foreach ($inputs as $key => $vals)
		{
			$this->instance->getIO()->setOption($key, 1);

			foreach ($vals as $val)
			{
				$this->assertEquals(1, $this->instance->getValue($val));
			}
		}

		// Filter
		$this->instance->getIO()->setOption('y', 'flower sakura');

		$this->assertEquals('flower sakura', $this->instance->getValue('y'), 'Default input filter should string.');
	}

	/**
	 * Test global.
	 *
	 * @return void
	 *
	 * @since  {DEPLOY_VERSION}
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
