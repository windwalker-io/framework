<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Record\Test;

use Windwalker\Database\Test\Mysql\AbstractMysqlTestCase;
use Windwalker\Record\NestedRecord;
use Windwalker\Utilities\ArrayHelper;

/**
 * Test class of NestedRecord
 *
 * @since 2.0
 */
class NestedRecordTest extends AbstractMysqlTestCase
{
	/**
	 * Test instance.
	 *
	 * @var NestedRecord
	 */
	protected $instance;

	/**
	 * setUpBeforeClass
	 *
	 * @return  void
	 */
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		$record = new NestedRecord('#__nestedsets');

		$record->createRoot();
	}

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->instance = new NestedRecord('#__nestedsets');
	}

	/**
	 * testRoot
	 *
	 * @return  void
	 */
	public function testRoot()
	{
		$list = $this->listAll();

		$this->assertEquals('root', $list[0]->title);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
		parent::tearDown();
	}

	/**
	 * Method to test check().
	 *
	 * @return void
	 *
	 * @expectedException \UnexpectedValueException
	 *
	 * @covers Windwalker\Record\NestedRecord::check
	 */
	public function testCheckParentIdZero()
	{
		$this->instance->parent_id = 0;

		$this->instance->check();
	}

	/**
	 * Method to test check().
	 *
	 * @return void
	 *
	 * @expectedException \UnexpectedValueException
	 *
	 * @covers Windwalker\Record\NestedRecord::check
	 */
	public function testCheckParentIdNotExists()
	{
		$this->instance->parent_id = 99;

		$this->instance->check();
	}

	/**
	 * Method to test setLocation().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\NestedRecord::setLocation
	 */
	public function testSetLocationAndStore()
	{
		$data = array(
			'title' => 'Flower',
			'alias' => 'flower',
		);

		$this->instance->bind($data)
			->setLocation(1, NestedRecord::POSITION_FIRST_CHILD)
			->store();

		$data = array(
			'title' => 'Sakura',
			'alias' => 'sakura',
		);

		$this->instance->reset(true)
			->bind($data)
			->setLocation(2, NestedRecord::POSITION_FIRST_CHILD)
			->store();

		// First child
		$data = array(
			'title' => 'Olive',
			'alias' => 'olive',
		);

		$this->instance->reset(true)
			->bind($data)
			->setLocation(2, NestedRecord::POSITION_FIRST_CHILD)
			->store();

		$this->assertEquals(array(2, 3), array($this->instance->lft, $this->instance->rgt));

		// Last child
		$data = array(
			'title' => 'Sunflower',
			'alias' => 'sunflower',
		);

		$this->instance->reset(true)
			->bind($data)
			->setLocation(2, NestedRecord::POSITION_LAST_CHILD)
			->store();

		$this->assertEquals(array(6, 7), array($this->instance->lft, $this->instance->rgt));

		// Before
		$data = array(
			'title' => 'Rose',
			'alias' => 'rose',
		);

		$this->instance->reset(true)
			->bind($data)
			->setLocation(2, NestedRecord::POSITION_BEFORE)
			->store();

		$this->assertEquals(array(1, 2), array($this->instance->lft, $this->instance->rgt));

		// After
		$data = array(
			'title' => 'Rose',
			'alias' => 'rose',
		);

		$this->instance->reset(true)
			->bind($data)
			->setLocation(2, NestedRecord::POSITION_AFTER)
			->store();

		$this->assertEquals(array(11, 12), array($this->instance->lft, $this->instance->rgt));
	}

	/**
	 * Method to test getPath().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\NestedRecord::getPath
	 */
	public function testGetPath()
	{
		$path = $this->instance->getPath(5);

		$ids = ArrayHelper::getColumn($path, 'id');

		$this->assertEquals(array(1, 2, 5), $ids);
	}

	/**
	 * Method to test getTree().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\NestedRecord::getTree
	 */
	public function testGetTree()
	{
		$tree = $this->instance->getTree(1);

		$ids = array(1, 6, 2, 4, 3, 5, 7);

		$this->assertEquals($ids, ArrayHelper::getColumn($tree, 'id'));
	}

	/**
	 * Method to test isLeaf().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\NestedRecord::isLeaf
	 */
	public function testIsLeaf()
	{
		$this->assertTrue($this->instance->isLeaf(5));
		$this->assertFalse($this->instance->isLeaf(2));
	}

	/**
	 * Method to test move().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\NestedRecord::move
	 */
	public function testMove()
	{
		$this->instance->load(5);

		$this->instance->move(-1);

		$this->assertEquals(array(6, 7), array($this->instance->lft, $this->instance->rgt));
	}

	/**
	 * Method to test moveByReference().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\NestedRecord::moveByReference
	 */
	public function testMoveByReference()
	{
		$this->instance->load(5);

		$this->instance->moveByReference(1, NestedRecord::POSITION_LAST_CHILD);

		$this->assertEquals(array(11, 12), array($this->instance->lft, $this->instance->rgt));
	}

	/**
	 * Method to test delete().
	 *
	 * @return void
	 *
	 * @expectedException \RuntimeException
	 *
	 * @covers Windwalker\Record\NestedRecord::delete
	 */
	public function testDelete()
	{
		// Before
		$data = array(
			'title' => 'Rose',
			'alias' => 'rose',
		);

		$this->instance->reset(true)
			->bind($data)
			->setLocation(2, NestedRecord::POSITION_BEFORE)
			->store();

		$this->instance->delete(8);

		$this->instance->reset(true)->load(8);
	}

	/**
	 * Method to test getRootId().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\NestedRecord::getRootId
	 */
	public function testGetRootId()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test rebuild().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\NestedRecord::rebuild
	 * @TODO   Implement testRebuild().
	 */
	public function testRebuild()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test rebuildPath().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\NestedRecord::rebuildPath
	 */
	public function testRebuildPath()
	{
		$this->instance->load(4)->rebuildPath();

		$this->assertEquals('flower/olive', $this->instance->path);
	}

	/**
	 * Method to test reset().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\NestedRecord::reset
	 * @TODO   Implement testReset().
	 */
	public function testReset()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * listAll
	 *
	 * @return  array
	 */
	protected function listAll()
	{
		return $this->db->getReader('select * from #__nestedsets')->loadObjectList();
	}

	/**
	 * showAll
	 *
	 * @return  void
	 */
	protected function showAll()
	{
		show($this->listAll());
	}
}
