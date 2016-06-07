<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Record\Test;

use Windwalker\Database\Test\Mysql\AbstractMysqlTestCase;
use Windwalker\Record\Record;
use Windwalker\Test\TestHelper;

/**
 * Test class of Record
 *
 * @since 2.0
 */
class RecordTest extends AbstractMysqlTestCase
{
	/**
	 * Test instance.
	 *
	 * @var Record
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
		parent::setUp();

		 $this->instance = new Record('articles');
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
	 * getInstallSql
	 *
	 * @return  string
	 */
	protected static function getSetupSql()
	{
		return file_get_contents(__DIR__ . '/Stub/fixtures.sql');
	}

	/**
	 * Method to test __set().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::__set
	 */
	public function test__set()
	{
		$record = $this->instance;

		$record->set('catid', 1);

		$data = (object) TestHelper::getValue($record, 'data');

		$this->assertEquals(1, $data->catid);

		$record->catid = 3;

		$data = (object) TestHelper::getValue($record, 'data');

		$this->assertEquals(3, $data->catid);
	}

	/**
	 * Method to test __get().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::__get
	 */
	public function test__get()
	{
		$record = $this->instance;

		$record->setAlias('foo', 'catid');

		$record->bind(array(
			'id' => 1,
			'foo' => 6,
			'title' => 'Sakura'
		));

		$this->assertEquals(1, $record->id);
		$this->assertEquals(6, $record->foo);
		$this->assertEquals(6, $record->catid);
		$this->assertEquals('Sakura', $record->title);
	}

	/**
	 * Method to test save().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::save
	 */
	public function testSave()
	{
		$key = $this->instance->getKeyName();

		$data = array(
			'title' => 'Test'
		);

		$this->instance->bind($data)->store();

		$flower = $this->db->setQuery('SELECT * FROM articles ORDER BY id DESC')->loadOne();

		$this->assertEquals('Test', $flower->title);
	}

	/**
	 * Method to test bind(). 
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::bind
	 * @TODO   Implement testBind().
	 */
	public function testBind()
	{
		$record = $this->instance;

		$record->bind(
			array(
				'title' => 'sakura',
				'fake' => 'cat'
			)
		);

		$this->assertEquals('sakura', $record->title);
		$this->assertEquals(null, $record->fake);
	}

	/**
	 * Method to test load().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::load
	 * @TODO   Implement testLoad().
	 */
	public function testLoad()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test delete().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::delete
	 * @TODO   Implement testDelete().
	 */
	public function testDelete()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test reset().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::reset
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
	 * Method to test check().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::check
	 * @TODO   Implement testCheck().
	 */
	public function testCheck()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test store().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::store
	 */
	public function testStore()
	{
		$data = array(
			'title'   => 'Lancelot',
			'meaning' => 'First Knight',
			'ordering' => 123456,
			'params' => ''
		);

		$record = $this->instance;

		$record->bind($data);

		$record->foo = 'Forbidden';

		$record->store();

		$record = new Record('articles');

		$record->load(array('title' => 'Lancelot'));

		$this->assertEquals(123456, $record->ordering);
	}

	/**
	 * Method to test hasPrimaryKey().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::hasPrimaryKey
	 * @TODO   Implement testHasPrimaryKey().
	 */
	public function testHasPrimaryKey()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test appendPrimaryKeys().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::appendPrimaryKeys
	 * @TODO   Implement testAppendPrimaryKeys().
	 */
	public function testAppendPrimaryKeys()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getKeyName().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::getKeyName
	 * @TODO   Implement testGetKeyName().
	 */
	public function testGetKeyName()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getFields().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::getFields
	 * @TODO   Implement testGetFields().
	 */
	public function testGetFields()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	public function testHasField()
	{
		$record = $this->instance;

		$this->assertTrue($record->hasField('title'));
		$this->assertFalse($record->hasField('chicken'));
	}

	/**
	 * Method to test getTableName().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::getTableName
	 * @TODO   Implement testGetTableName().
	 */
	public function testGetTableName()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setTableName().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::setTableName
	 * @TODO   Implement testSetTableName().
	 */
	public function testSetTableName()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getIterator().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::getIterator
	 * @TODO   Implement testGetIterator().
	 */
	public function testGetIterator()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test toObject().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::toObject
	 * @TODO   Implement testToObject().
	 */
	public function testToObject()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test toArray().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::toArray
	 * @TODO   Implement testToArray().
	 */
	public function testToArray()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test __isset().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::__isset
	 * @TODO   Implement test__isset().
	 */
	public function test__isset()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test __clone().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::__clone
	 * @TODO   Implement test__clone().
	 */
	public function test__clone()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test q().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::q
	 * @TODO   Implement testQ().
	 */
	public function testQ()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test qn().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::qn
	 * @TODO   Implement testQn().
	 */
	public function testQn()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test valueExists().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::valueExists
	 * @TODO   Implement testValueExists().
	 */
	public function testValueExists()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setAlias().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::setAlias
	 * @covers Windwalker\Record\Record::resolveAlias
	 */
	public function testAlias()
	{
		$record = $this->instance;

		// Get by alias
		$record->setAlias('foo', 'catid');

		$record->bind(array(
			'id' => 1,
			'foo' => 6,
			'title' => 'Sakura'
		));

		$this->assertEquals(6, $record->catid);
		$this->assertEquals('Sakura', $record->title);

		// Test resolve
		$this->assertEquals('catid', $record->resolveAlias('foo'));

		// Set by alias
		$record->setAlias('bar', 'air');

		$record->bar = 8;

		$data = (object) TestHelper::getValue($record, 'data');

		$this->assertEquals(8, $data->air);
	}

	/**
	 * Method to test offsetExists().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::offsetExists
	 * @TODO   Implement testOffsetExists().
	 */
	public function testOffsetExists()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test offsetGet().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::offsetGet
	 * @TODO   Implement testOffsetGet().
	 */
	public function testOffsetGet()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test offsetSet().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::offsetSet
	 * @TODO   Implement testOffsetSet().
	 */
	public function testOffsetSet()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test offsetUnset().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::offsetUnset
	 * @TODO   Implement testOffsetUnset().
	 */
	public function testOffsetUnset()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test triggerEvent().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::triggerEvent
	 * @TODO   Implement testTriggerEvent().
	 */
	public function testTriggerEvent()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getDispatcher().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::getDispatcher
	 * @TODO   Implement testGetDispatcher().
	 */
	public function testGetDispatcher()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setDispatcher().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::setDispatcher
	 * @TODO   Implement testSetDispatcher().
	 */
	public function testSetDispatcher()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getDb().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::getDb
	 * @TODO   Implement testGetDb().
	 */
	public function testGetDb()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setDb().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Record\Record::setDb
	 * @TODO   Implement testSetDb().
	 */
	public function testSetDb()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
