<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Database\Test\Postgresql;

use Windwalker\Database\Driver\Postgresql\PostgresqlType;
use Windwalker\Database\Schema\Column;
use Windwalker\Database\Schema\DataType;
use Windwalker\Database\Schema\Key;
use Windwalker\Query\Mysql\MysqlQueryBuilder;
use Windwalker\Query\Postgresql\PostgresqlQueryBuilder;

/**
 * Test class of PostgresqlTable
 *
 * @since 2.0
 */
class PostgresqlTableTest extends AbstractPostgresqlTestCase
{
	protected static $debug = true;

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

		$table->addColumn('id', PostgresqlType::INTEGER, Column::UNSIGNED, Column::NOT_NULL, '', 'PK', array('primary' => true))
			->addColumn('name', DataType::VARCHAR, Column::SIGNED, Column::NOT_NULL, '', 'Name')
			->addColumn('alias', 'varchar(255)', Column::SIGNED, Column::NOT_NULL, '', 'Alias')
			->addIndex(Key::TYPE_INDEX, 'idx_name', 'name', 'Test')
			->addIndex(Key::TYPE_UNIQUE, 'idx_alias', 'alias', 'Alias Index')
			->create();

		$columns = $table->getColumnDetails(true);

		$this->assertEquals('integer', $columns['id']->Type);
		$this->assertEquals('varchar(255)', $columns['name']->Type);
		$this->assertEquals('UNI', $columns['alias']->Key);

		static::$dbo->setQuery(PostgresqlQueryBuilder::dropTable('#__cloud', true))->execute();

		// Test Column types
		$table = $this->db->getTable('#__cloud', true);

		$table->addColumn(new Column\Primary('id'))
			->addColumn(new Column\Varchar('name'))
			->addColumn(new Column\Char('type'))
			->addColumn(new Column\Timestamp('created'))
			->addColumn(new Column\Bit('state'))
			->addColumn(new Column\Integer('uid'))
			->addColumn(new Column\Tinyint('status'))
			->create();

		$columns = $table->getColumnDetails(true);

		$this->assertEquals('integer', $columns['id']->Type);
		$this->assertEquals('varchar(255)', $columns['name']->Type);

		static::$dbo->setQuery(PostgresqlQueryBuilder::dropTable('#__cloud', true))->execute();
	}

	/**
	 * Method to test create().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlTable::create
	 */
	public function testDoCreate()
	{
		$table = $this->db->getTable('#__cloud');

		$table->doCreate(
			array(
				'id' => 'integer NOT NULL',
				'name' => 'varchar(255) NOT NULL'
			),
			'id',
			array(),
			5,
			true
		);

		$columns = $table->getColumnDetails();

		$this->assertEquals('integer', $columns['id']->Type);
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

		$this->assertEquals('integer', $columns['id']->Type);
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

		$table->addColumn('state', DataType::INTEGER, Column::SIGNED, Column::NOT_NULL, 0, 'State')
			->save();

		$columns = $table->getColumns(true);

		$this->assertEquals(array('id', 'title', 'ordering', 'params', 'state'), $columns);
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
		$table = $this->db->getTable('#__categories', true);

		$table->dropColumn('state');

		$columns = $table->getColumns(true);

		$this->assertEquals(array('id', 'title', 'ordering', 'params'), $columns);
	}

	/**
	 * Method to test modifyColumn()
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\Database\Driver\Mysql\MysqlTable::modifyColumn
	 */
	public function testModifyColumn()
	{
		$table = $this->db->getTable('#__categories', true);

		$table->addColumn(new Column\Integer('foo'))
			->save();

		$table->modifyColumn(new Column\Tinyint('foo', 3));

		$tables = $table->getColumnDetails();

		$this->assertEquals('smallint', $tables['foo']->Type);

		$table->modifyColumn(new Column\Varchar('foo', 3));

		$tables = $table->getColumnDetails();

		$this->assertEquals('varchar(3)', $tables['foo']->Type);
	}

	/**
	 * testChangeColumn
	 *
	 * @return  void
	 */
	public function testChangeColumn()
	{
		$table = $this->db->getTable('#__categories', true);

		$table->changeColumn('foo', new Column\Char('bar', 5));

		$tables = $table->getColumnDetails();

		$this->assertEquals('character(5)', $tables['bar']->Type);
		$this->assertArrayNotHasKey('foo', $tables);
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
		$table = $this->db->getTable('#__categories', true);

		$indexes = $table->getIndexes();

		$this->assertEquals('ww_categories_pkey', $indexes[0]->Key_name);
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
		$table = $this->db->getTable('#__categories', true);

		$table->addIndex('INDEX', 'idx_ordering', array('ordering', 'id'))
			->save();

		$indexes = $table->getIndexes();

		$this->assertEquals('ww_categories_pkey', $indexes[2]->Key_name);
		$this->assertEquals('idx_ordering', $indexes[1]->Key_name);
		$this->assertEquals('id', $indexes[0]->Column_name);
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
		$table = $this->db->getTable('#__categories', true);

		$table->dropIndex('idx_ordering');

		$indexes = $table->getIndexes();

		$this->assertEquals(1, count($indexes));

		$table->modifyColumn('id', DataType::INTEGER, Column::UNSIGNED, Column::NOT_NULL, null);
		$table->dropIndex('ww_categories_pkey', true);

		$indexes = $table->getIndexes();

		$this->assertEquals(0, count($indexes));
	}

	/**
	 * Destructor
	 */
	public function __destruct()
	{
		static::tearDownAfterClass();
	}

	/**
	 * tearDownAfterClass
	 *
	 * @return  void
	 */
	public static function tearDownAfterClass()
	{
		if (!static::$dbo)
		{
			return;
		}

		if (!static::$debug)
		{
			try
			{
				static::$dbo->setQuery(PostgresqlQueryBuilder::dropTable('#__cloud', true))->execute();
			}
			catch (\Exception $e)
			{
				// Do nothing
			}

			try
			{
				static::$dbo->setQuery(PostgresqlQueryBuilder::dropTable('#__wind', true))->execute();
			}
			catch (\Exception $e)
			{
				// Do nothing
			}
		}

		parent::tearDownAfterClass();
	}
}
