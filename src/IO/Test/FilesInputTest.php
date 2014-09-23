<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\IO\Test;

use Windwalker\IO\FilesInput;
use Windwalker\Test\TestHelper;

/**
 * Test class of FilesInput
 *
 * @since {DEPLOY_VERSION}
 */
class FilesInputTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var FilesInput
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
		$this->instance = new FilesInput;
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
	 * Test the Windwalker\Input\Files::__construct method.
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Input\Files::__construct
	 * @since   1.1.4
	 */
	public function test__construct()
	{
		$this->assertEquals(
			$_FILES,
			TestHelper::getValue($this->instance, 'data')
		);
	}

	/**
	 * Test the Windwalker\Input\Files::get method.
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Input\Files::get
	 * @since   1.0
	 */
	public function testGet()
	{
		$this->assertEquals('foobar', $this->instance->get('myfile', 'foobar'));

		$data = array(
			'myfile' => array(
				'name' => 'n',
				'type' => 'ty',
				'tmp_name' => 'tm',
				'error' => 'e',
				'size' => 's'
			),
			'myfile2' => array(
				'name' => 'nn',
				'type' => 'ttyy',
				'tmp_name' => 'ttmm',
				'error' => 'ee',
				'size' => 'ss'
			)
		);

		TestHelper::setValue($this->instance, 'data', $data);

		$expected = array(
			'name' => 'n',
			'type' => 'ty',
			'tmp_name' => 'tm',
			'error' => 'e',
			'size' => 's'
		);

		$this->assertEquals($expected, $this->instance->get('myfile'));
	}

	/**
	 * Test the Windwalker\Input\Files::decodeData method.
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Input\Files::decodeData
	 * @since   1.1.4
	 */
	public function testDecodeData()
	{
		$data = array('n', 'ty', 'tm', 'e', 's');

		$decoded = TestHelper::invoke($this->instance, 'decodeData', $data);

		$expected = array(
			'name' => 'n',
			'type' => 'ty',
			'tmp_name' => 'tm',
			'error' => 'e',
			'size' => 's'
		);

		$this->assertEquals($expected, $decoded);

		$dataArr = array('first', 'second');
		$data = array($dataArr , $dataArr, $dataArr, $dataArr, $dataArr);

		$decoded = TestHelper::invoke($this->instance, 'decodeData', $data);

		$expectedFirst = array(
			'name' => 'first',
			'type' => 'first',
			'tmp_name' => 'first',
			'error' => 'first',
			'size' => 'first'
		);

		$expectedSecond = array(
			'name' => 'second',
			'type' => 'second',
			'tmp_name' => 'second',
			'error' => 'second',
			'size' => 'second'
		);

		$expected = array($expectedFirst, $expectedSecond);

		$this->assertEquals($expected, $decoded);
	}

	/**
	 * Test the Windwalker\Input\Files::set method.
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Input\Files::set
	 * @since   1.0
	 */
	public function testSet()
	{
		$this->instance->set('foo', 'bar');

		$this->assertEquals(null, $this->instance->get('foo'));
	}
}
