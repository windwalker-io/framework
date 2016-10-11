<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Record\Test;

use Windwalker\Database\Test\Mysql\AbstractMysqlTestCase;
use Windwalker\DataMapper\Adapter\WindwalkerAdapter;
use Windwalker\Query\Query;
use Windwalker\Record\Record;
use Windwalker\Record\Test\Stub\StubRecord;
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
	 * @covers \Windwalker\Record\Record::__set
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
	 * @covers \Windwalker\Record\Record::__get
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
	 * @covers \Windwalker\Record\Record::save
	 */
	public function testSave()
	{
		// Test create
		$key = $this->instance->getKeyName();

		$data = array(
			'title' => 'Test'
		);

		$this->instance->save($data);

		$flower = $this->db->setQuery('SELECT * FROM articles ORDER BY id DESC')->loadOne();

		$this->assertEquals('Test', $flower->title);

		// Test update
		$data = array(
			$key => 1,
			'title' => 'YOO',
			'extra_field' => 'BAR' // will not saved
		);

		$this->instance->reset(false)->save($data);

		$flower = $this->db->setQuery('SELECT * FROM articles WHERE id = 1')->loadOne();

		$this->assertEquals('YOO', $flower->title);
		$this->assertEquals('2000-12-14 01:53:02', $flower->created);

		// Test Update nulls
		$this->instance->reset(true)->save($data, true);

		$flower = $this->db->setQuery('SELECT * FROM articles WHERE id = 1')->loadOne();

		$this->assertEquals('0000-00-00 00:00:00', $flower->created);

		// Test save null data
		$data = array(
			$key => 2,
			'title' => null,
			'alias' => null,
			'extra_field' => 'BAR' // will not saved
		);

		$this->instance->reset(true)->save($data, true);

		$flower = $this->db->setQuery('SELECT * FROM articles WHERE id = 2')->loadOne();

		$this->assertSame('', $flower->title);
		$this->assertSame(null, $flower->alias);
	}

	/**
	 * Method to test bind(). 
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Record\Record::bind
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
		$this->assertEquals('cat', $record->fake);
	}

	/**
	 * Method to test load().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Record\Record::load
	 */
	public function testLoad()
	{
		$this->instance->load(3);

		$this->assertEquals('Illo', $this->instance->title);
	}

	/**
	 * Method to test delete().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Record\Record::delete
	 */
	public function testDelete()
	{
		$flower = $this->db->setQuery('SELECT * FROM articles WHERE id = 5')->loadOne();

		$this->assertEquals('Ipsam reprehenderit', $flower->title);

		$this->instance->delete(5);

		$flower = $this->db->setQuery('SELECT * FROM articles WHERE id = 5')->loadOne();

		$this->assertFalse($flower);
	}

	/**
	 * Method to test reset().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Record\Record::reset
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
	 * @covers \Windwalker\Record\Record::validate
	 */
	public function testCheck()
	{
		$record = new StubRecord('articles');

		$this->assertExpectedException(function () use ($record)
		{
			$record->validate();
		}, new \RuntimeException, 'Record save error');

		$this->assertExpectedException(function () use ($record)
		{
			$record->save(array());
		}, new \RuntimeException, 'Record save error');
	}

	/**
	 * Method to test store().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Record\Record::store
	 */
	public function testStore()
	{
		// Test create
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

		// Test update
		$key = $this->instance->getKeyName();

		$data = array(
			$key => 1,
			'title' => 'YOO'
		);

		$this->instance->reset()->bind($data)->store();

		$flower = $this->db->setQuery('SELECT * FROM articles WHERE id = 1')->loadOne();

		$this->assertEquals('YOO', $flower->title);
	}

	/**
	 * testCreate
	 *
	 * @return  void
	 *
	 * @covers \Windwalker\Record\Record::create
	 */
	public function testCreate()
	{
		// Test create with id
		$data = array(
			'title'    => 'Lodovico',
			'meaning'  => 'Kinsman to Brabantio',
			'ordering' => 123456,
			'params'   => ''
		);

		$record = $this->instance;

		$record->bind($data);

		$record->create();

		$flower = $this->db->setQuery('SELECT * FROM articles WHERE title = "Lodovico"')->loadOne();

		$this->assertEquals('Lodovico', $flower->title);
		$this->assertEquals($record->id, $flower->id);

		// Test create with no id
		$data = array(
			'id'       => 3000,
			'title'    => 'Brabantio',
			'meaning'  => 'senator',
			'ordering' => 123456,
			'params'   => ''
		);

		$record = $this->instance;

		$record->bind($data);

		$record->create();

		$flower = $this->db->setQuery('SELECT * FROM articles WHERE id = 3000')->loadOne();

		$this->assertEquals('Brabantio', $flower->title);
	}

	/**
	 * testUpdate
	 *
	 * @return  void
	 */
	public function testUpdate()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Method to test hasPrimaryKey().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Record\Record::hasPrimaryKey
	 */
	public function testHasPrimaryKey()
	{
		$this->assertFalse($this->instance->hasPrimaryKey());

		$key = $this->instance->getKeyName();

		$this->instance->$key = 123;

		$this->assertTrue($this->instance->hasPrimaryKey());
	}

	/**
	 * Method to test getKeyName().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Record\Record::getKeyName
	 */
	public function testGetKeyName()
	{
		$record = new Record('articles', array('id', 'alias'));

		$this->assertEquals('id', $record->getKeyName());
		$this->assertEquals(array('id', 'alias'), $record->getKeyName(true));
	}

	/**
	 * Method to test getFields().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Record\Record::getFields
	 */
	public function testGetFields()
	{
		$fields = $this->instance->getFields();

		$expected = $this->db->getTable('articles')->getColumnDetails(true);

		$expected['created']->Default = $this->db->getQuery(true)->getNullDate();

		$this->assertEquals($expected, $fields);
	}

	/**
	 * testHasField
	 *
	 * @return  void
	 */
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
	 * @covers \Windwalker\Record\Record::getTableName
	 */
	public function testGetTableName()
	{
		$this->assertEquals('articles', $this->instance->getTableName());
	}

	/**
	 * Method to test setTableName().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Record\Record::setTableName
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
	 * @covers \Windwalker\Record\Record::getIterator
	 */
	public function testGetIterator()
	{
		$this->instance->load(7);

		$this->assertEquals($this->instance->dump(), iterator_to_array($this->instance));
	}

	/**
	 * Method to test toObject().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Record\Record::toObject
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
	 * @covers \Windwalker\Record\Record::dump
	 */
	public function testDump()
	{
		$this->instance->load(7);

		$array = $this->instance->dump();

		$this->assertEquals($this->instance->title, $array['title']);
	}

	/**
	 * Method to test __isset().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Record\Record::__isset
	 */
	public function test__isset()
	{
		$this->assertTrue(isset($this->instance->title));
		$this->assertFalse(isset($this->instance->chicken));
	}

	/**
	 * Method to test __clone().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Record\Record::__clone
	 */
	public function test__clone()
	{
		$this->instance->load(7);

		$clone = clone $this->instance;

		$clone->load(8);

		$this->assertNotSame($clone, $this->instance);

		$this->assertNotEquals($clone->title, $this->instance->title);
	}

	/**
	 * Method to test q().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Record\Record::q
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
	 * @covers \Windwalker\Record\Record::qn
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
	 * @covers \Windwalker\Record\Record::valueExists
	 */
	public function testValueExists()
	{
		$this->assertTrue($this->instance->valueExists('alias', 'ut-qui-sed'));

		$this->instance->load(array('alias' => 'ut-qui-sed'));

		$this->assertFalse($this->instance->valueExists('id'));
	}

	/**
	 * Method to test setAlias().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Record\Record::setAlias
	 * @covers \Windwalker\Record\Record::resolveAlias
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
	 * @covers \Windwalker\Record\Record::offsetExists
	 */
	public function testOffsetExists()
	{
		$this->assertTrue(isset($this->instance['title']));
		$this->assertFalse(isset($this->instance['chicken']));
	}

	/**
	 * Method to test offsetGet().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Record\Record::offsetGet
	 */
	public function testOffsetGet()
	{
		$this->instance->load(12);

		$this->assertEquals('Quidem sequi', $this->instance['title']);
		$this->assertEquals(null, $this->instance['chicken']);
	}

	/**
	 * Method to test offsetSet().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Record\Record::offsetSet
	 */
	public function testOffsetSet()
	{
		$this->instance['title'] = 'ABC';
		$this->instance['Extra'] = 'foo';

		$array = $this->instance->dump(true);

		$this->assertEquals('ABC', $array['title']);
		$this->assertEquals('foo', $array['Extra']);
	}

	/**
	 * Method to test offsetUnset().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Record\Record::offsetUnset
	 */
	public function testOffsetUnset()
	{
		$this->instance['title'] = 'ABC';

		$this->assertEquals('ABC', $this->instance['title']);

		unset($this->instance['title']);

		$this->assertEquals(null, $this->instance['title']);
	}

	/**
	 * Method to test triggerEvent().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Record\Record::triggerEvent
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
	 * @covers \Windwalker\Record\Record::getDispatcher
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
	 * @covers \Windwalker\Record\Record::setDispatcher
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
	 * @covers \Windwalker\Record\Record::getDb
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
	 * @covers \Windwalker\Record\Record::setDb
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
