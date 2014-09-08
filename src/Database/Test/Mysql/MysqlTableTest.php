<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Database\Test\Mysql;

use Windwalker\Query\Mysql\MysqlQueryBuilder;

/**
 * Test class of MysqlTable
 *
 * @since {DEPLOY_VERSION}
 */
class MysqlTableTest extends AbstractMysqlTest
{
	/**
	 * Method to test getName().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Command\AbstractTable::getName
	 */
	public function testGetName()
	{
		$table = $this->db->getTable('#__flower');

		$this->assertEquals('#__flower', $table->getName());
	}

	/**
	 * Method to test setName().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Command\AbstractTable::setName
	 */
	public function testSetName()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getDriver().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Command\AbstractTable::getDriver
	 */
	public function testGetDriver()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setDriver().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Command\AbstractTable::setDriver
	 * @TODO   Implement testSetDriver().
	 */
	public function testSetDriver()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test create().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlTable::create
	 */
	public function testCreate()
	{
		$table = $this->db->getTable('#__cloud');

		$table->create(
			array(
				'id' => 'int(11) UNSIGNED NOT NULL',
				'name' => 'varchar(255) NOT NULL'
			),
			'id',
			array(),
			5,
			true
		);

		$columns = $table->getColumnDetails();

		$this->assertEquals('int(11) unsigned', $columns['id']->Type);
		$this->assertEquals('varchar(255)', $columns['name']->Type);
	}

	/**
	 * Method to test rename().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlTable::rename
	 */
	public function testRename()
	{
		$table = $this->db->getTable('#__cloud');

		$table = $table->rename('#__wind');

		$columns = $table->getColumnDetails();

		$this->assertEquals('int(11) unsigned', $columns['id']->Type);
		$this->assertEquals('varchar(255)', $columns['name']->Type);
	}

	/**
	 * Method to test lock().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlTable::lock
	 * @TODO   Implement testLock().
	 */
	public function testLock()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test unlock().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlTable::unlock
	 * @TODO   Implement testUnlock().
	 */
	public function testUnlock()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test truncate().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlTable::truncate
	 */
	public function testTruncate()
	{
		$table = $this->db->getTable('#__categories');

		$table->truncate();

		$items = $this->db->getReader('SELECT * FROM #__categories')->loadObjectList();

		$this->assertEquals(array(), $items);
	}

	/**
	 * Method to test getColumns().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlTable::getColumns
	 */
	public function testGetColumns()
	{
		$columns = $this->db->getTable('#__categories')->getColumns();

		$this->assertEquals(array('id', 'title', 'ordering', 'params'), $columns);
	}

	/**
	 * Method to test getColumnDetails().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlTable::getColumnDetails
	 */
	public function testGetColumnDetails()
	{
		$columns = $this->db->getTable('#__categories')->getColumnDetails();

		$this->assertEquals('id', $columns['id']->Field);
		$this->assertEquals('varchar(255)', $columns['title']->Type);
	}

	/**
	 * Method to test getColumnDetail().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlTable::getColumnDetail
	 */
	public function testGetColumnDetail()
	{
		$column = $this->db->getTable('#__categories')->getColumnDetail('id');

		$this->assertEquals('id', $column->Field);
		$this->assertEquals('auto_increment', $column->Extra);
	}

	/**
	 * Method to test addColumn().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlTable::addColumn
	 */
	public function testAddColumn()
	{
		$table = $this->db->getTable('#__categories');

		$table->addColumn('state', 'int(1)', true, false, 0, 'AFTER ordering', 'State');

		$columns = $table->getColumns(true);

		$this->assertEquals(array('id', 'title', 'ordering', 'state', 'params'), $columns);
	}

	/**
	 * Method to test dropColumn().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlTable::dropColumn
	 */
	public function testDropColumn()
	{
		$table = $this->db->getTable('#__categories');

		$table->dropColumn('state');

		$columns = $table->getColumns(true);

		$this->assertEquals(array('id', 'title', 'ordering', 'params'), $columns);
	}

	/**
	 * Method to test getIndexes().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlTable::getIndexes
	 */
	public function testGetIndexes()
	{
		$table = $this->db->getTable('#__categories');

		$indexes = $table->getIndexes();

		$this->assertEquals('PRIMARY', $indexes[0]->Key_name);
	}

	/**
	 * Method to test addIndex().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlTable::addIndex
	 */
	public function testAddIndex()
	{
		$table = $this->db->getTable('#__categories');

		$table->addIndex('key', 'idx_ordering', array('ordering', 'id'));

		$indexes = $table->getIndexes();

		$this->assertEquals('PRIMARY', $indexes[0]->Key_name);
		$this->assertEquals('idx_ordering', $indexes[1]->Key_name);
		$this->assertEquals('id', $indexes[2]->Column_name);
	}

	/**
	 * Method to test dropIndex().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlTable::dropIndex
	 */
	public function testDropIndex()
	{
		$table = $this->db->getTable('#__categories');

		$table->dropIndex('key', 'idx_ordering');

		$indexes = $table->getIndexes();

		$this->assertEquals(1, count($indexes));
	}

	/**
	 * Destructor
	 */
	public function __destruct()
	{
		$this->db->select(static::$dbname);

		$this->db->setQuery(MysqlQueryBuilder::dropTable('#__cloud', true))->execute();
		$this->db->setQuery(MysqlQueryBuilder::dropDatabase('#__wind', true))->execute();

		parent::__destruct();
	}

	/**
	 * tearDownAfterClass
	 *
	 * @return  void
	 */
	public static function tearDownAfterClass()
	{
		static::$dbo->select(static::$dbname);

		static::$dbo->setQuery(MysqlQueryBuilder::dropDatabase('#__cloud', true))->execute();
		static::$dbo->setQuery(MysqlQueryBuilder::dropDatabase('#__wind', true))->execute();

		parent::tearDownAfterClass();
	}
}
