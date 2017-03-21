<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\IO\Test;

use Windwalker\IO\FilesInput;
use Windwalker\Test\TestHelper;

/**
 * Test class of FilesInput
 *
 * @since 2.0
 */
class FilesInputTest extends \PHPUnit\Framework\TestCase
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
	 * Test the Windwalker\IO\FilesInput::__construct method.
	 *
	 * @return  void
	 *
	 * @covers  \Windwalker\IO\FilesInput::__construct
	 * @since   2.0
	 */
	public function test__construct()
	{
		$this->assertEquals(
			$_FILES,
			TestHelper::getValue($this->instance, 'data')
		);
	}

	/**
	 * Test the Windwalker\IO\FilesInput::get method.
	 *
	 * @return  void
	 *
	 * @covers  \Windwalker\IO\FilesInput::get
	 * @since   2.0
	 */
	public function testGet()
	{
		$this->assertEquals('foobar', $this->instance->get('myfile', 'foobar'));

		$data = [
			'myfile' => [
				'name' => 'n',
				'type' => 'ty',
				'tmp_name' => 'tm',
				'error' => 'e',
				'size' => 's'
			],
			'myfile2' => [
				'name' => 'nn',
				'type' => 'ttyy',
				'tmp_name' => 'ttmm',
				'error' => 'ee',
				'size' => 'ss'
			]
		];

		TestHelper::setValue($this->instance, 'data', $data);

		$expected = [
			'name' => 'n',
			'type' => 'ty',
			'tmp_name' => 'tm',
			'error' => 'e',
			'size' => 's'
		];

		$this->assertEquals($expected, $this->instance->get('myfile'));

		$data2 = [
			'foo' => [
				'name' => [
					'myfile' => 'n',
					'myfile2' => 'nn'
				],
				'type' => [
					'myfile' => 'ty',
					'myfile2' => 'ttyy'
				],
				'tmp_name' => [
					'myfile' => 'tm',
					'myfile2' => 'ttmm'
				],
				'error' => [
					'myfile' => 'e',
					'myfile2' => 'ee'
				],
				'size' => [
					'myfile' => 's',
					'myfile2' => 'ss'
				]
			]
		];

		TestHelper::setValue($this->instance, 'data', $data2);

		$this->assertEquals($data, $this->instance->get('foo'));

		// We don't convert data structure for getByPath() now.
		$this->assertEquals('n', $this->instance->get('foo.name.myfile', '.'));
	}

	/**
	 * Test the Windwalker\IO\FilesInput::decodeData method.
	 *
	 * @return  void
	 *
	 * @covers  \Windwalker\IO\FilesInput::decodeData
	 * @since   2.0
	 */
	public function testDecodeData()
	{
		$data = ['n', 'ty', 'tm', 'e', 's'];

		$decoded = TestHelper::invoke($this->instance, 'decodeData', $data);

		$expected = [
			'name' => 'n',
			'type' => 'ty',
			'tmp_name' => 'tm',
			'error' => 'e',
			'size' => 's'
		];

		$this->assertEquals($expected, $decoded);

		$dataArr = ['first', 'second'];
		$data = [$dataArr , $dataArr, $dataArr, $dataArr, $dataArr];

		$decoded = TestHelper::invoke($this->instance, 'decodeData', $data);

		$expectedFirst = [
			'name' => 'first',
			'type' => 'first',
			'tmp_name' => 'first',
			'error' => 'first',
			'size' => 'first'
		];

		$expectedSecond = [
			'name' => 'second',
			'type' => 'second',
			'tmp_name' => 'second',
			'error' => 'second',
			'size' => 'second'
		];

		$expected = [$expectedFirst, $expectedSecond];

		$this->assertEquals($expected, $decoded);
	}

	/**
	 * Test the Windwalker\IO\FilesInput::set method.
	 *
	 * @return  void
	 *
	 * @covers  \Windwalker\IO\FilesInput::set
	 * @since   2.0
	 */
	public function testSet()
	{
		$this->instance->set('flower', 'bar');

		$this->assertNull($this->instance->get('flower'));
	}
}
