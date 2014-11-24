<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Console\Test;

use Windwalker\Console\Option\Option;
use Windwalker\Console\Option\OptionSet;

/**
 * Class OptionSet Test
 *
 * @since  {DEPLOY_VERSION}
 */
class OptionSetTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var Option
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $instance;

	/**
	 * Property options.
	 *
	 * @var array
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $options = array();

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
		$this->instance = $optionset = new OptionSet;

		$optionset[] = $this->options['a'] = new Option(array('a', 'A', 'ace'), 1, 'Ace');

		$optionset[] = $this->options['b'] = new Option(array('b', 'B', 'bar'), 1, 'Barcode');
	}

	/**
	 * test OffsetGet
	 *
	 * @return  void
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function testOffsetGet()
	{
		$this->assertSame($this->instance['a'], $this->options['a']);
	}

	/**
	 * test OffsetGet
	 *
	 * @return  void
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function testOffsetGetByAlias()
	{
		$this->assertSame($this->instance['A'], $this->options['a']);

		$this->assertSame($this->instance['bar'], $this->options['b']);
	}

	/**
	 * test OffsetSet
	 *
	 * @return  void
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function testOffsetSet()
	{
		$this->instance[] = $this->options['c'] = new Option(array('c', 'C', 'Car'), 1, 'Carbon');

		$this->assertSame($this->instance['c'], $this->options['c']);
	}

	/**
	 * test OffsetUnset
	 *
	 * @return  void
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function testOffsetUnset()
	{
		$this->instance[] = $this->options['d'] = new Option('d', 1, 'Dog');

		// Remove by alias
		unset($this->instance['car']);
		$this->assertNull($this->instance['c']);

		// Remove by name
		unset($this->instance['d']);
		$this->assertNull($this->instance['d']);
	}

	/**
	 * test OffsetExists
	 *
	 * @return  void
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function testOffsetExists()
	{
		$this->assertTrue(isset($this->instance['b']));

		$this->assertTrue(isset($this->instance['bar']));

		$this->assertFalse(isset($this->instance['c']));
	}
}
