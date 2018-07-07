<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Database\Test\Postgresql;

use Windwalker\Database\Schema\Column;
use Windwalker\Database\Schema\DataType;
use Windwalker\Database\Schema\Schema;
use Windwalker\Query\Postgresql\PostgresqlGrammar;

/**
 * Test class of PostgresqlTable
 *
 * @since 2.0
 */
class PostgresqlTableTest extends AbstractPostgresqlTestCase
{
    protected static $debug = true;

    /**
     * tearDown
     *
     * @return  void
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Method to test getName().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Command\AbstractTable::getName
     */
    public function testGetName()
    {
        $table = $this->db->getTable('#__flower');

        $this->assertEquals('#__flower', $table->getName());

        $table->setDatabase($this->db->getDatabase('yoo'));

        $this->assertEquals('yoo.#__flower', $table->getName());
    }

    /**
     * Method to test setName().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Command\AbstractTable::setName
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
     * @covers \Windwalker\Database\Command\AbstractTable::getDriver
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
     * @covers \Windwalker\Database\Command\AbstractTable::setDriver
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
     * @covers \Windwalker\Database\Driver\Mysql\MysqlTable::create
     */
    public function testCreate()
    {
        $table = $this->db->getTable('#__cloud');

        $table->create(
            function (Schema $schema) {
                $schema->primary('id')->signed(false)->comment('PK');
                $schema->varchar('name')->allowNull(false);
                $schema->varchar('alias');
                $schema->float('float');
                $schema->addIndex('name', 'idx_name')->comment('Test');
                $schema->addIndex('float');
                $schema->addUniqueKey('alias', 'idx_alias')->comment('Alias Index');
            }
        );

        $columns = $table->getColumnDetails();

        $this->assertEquals('integer', $columns['id']->Type);
        $this->assertEquals('varchar(255)', $columns['name']->Type);
        $this->assertEquals('UNI', $columns['alias']->Key);
        $this->assertEquals('real', $columns['float']->Type);

        $this->assertTrue($table->hasIndex('idx_cloud_float'));
    }

    /**
     * Method to test rename().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Driver\Postgresql\PostgresqlTable::rename
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
     * @covers \Windwalker\Database\Driver\Postgresql\PostgresqlTable::lock
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
     * @covers \Windwalker\Database\Driver\Postgresql\PostgresqlTable::unlock
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
     * @covers \Windwalker\Database\Driver\Postgresql\PostgresqlTable::truncate
     */
    public function testTruncate()
    {
        $table = $this->db->getTable('#__categories');

        $table->truncate();

        $items = $this->db->getReader('SELECT * FROM #__categories')->loadObjectList();

        $this->assertEquals([], $items);
    }

    /**
     * Method to test getColumns().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Driver\Postgresql\PostgresqlTable::getColumns
     */
    public function testGetColumns()
    {
        $columns = $this->db->getTable('#__categories')->getColumns();

        $this->assertEquals(['id', 'title', 'ordering', 'params'], $columns);
    }

    /**
     * Method to test getColumnDetails().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Driver\Postgresql\PostgresqlTable::getColumnDetails
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
     * @covers \Windwalker\Database\Driver\Postgresql\PostgresqlTable::getColumnDetail
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
     * @covers \Windwalker\Database\Driver\Postgresql\PostgresqlTable::addColumn
     */
    public function testAddColumn()
    {
        $table = $this->db->getTable('#__categories');

        $table->addColumn('state', DataType::INTEGER, Column::SIGNED, Column::NOT_NULL, 0, 'State');

        $columns = $table->getColumns();

        $this->assertEquals(['id', 'title', 'ordering', 'params', 'state'], $columns);
    }

    /**
     * Method to test dropColumn().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Driver\Postgresql\PostgresqlTable::dropColumn
     */
    public function testDropColumn()
    {
        $table = $this->db->getTable('#__categories', true);

        $table->dropColumn('state');

        $columns = $table->getColumns();

        $this->assertEquals(['id', 'title', 'ordering', 'params'], $columns);
    }

    /**
     * Method to test modifyColumn()
     *
     * @return  void
     *
     * @covers  \Windwalker\Database\Driver\Postgresql\PostgresqlTable::modifyColumn
     */
    public function testModifyColumn()
    {
        $table = $this->db->getTable('#__categories', true);

        $table->addColumn(new Column\Varchar('foo'));

        $table->modifyColumn(new Column\Integer('foo', 3));

        $tables = $table->getColumnDetails();

        $this->assertEquals('integer', $tables['foo']->Type);

        $table->modifyColumn(new Column\Tinyint('foo', 3));

        $tables = $table->getColumnDetails();

        $this->assertEquals('smallint', $tables['foo']->Type);
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
     * @covers \Windwalker\Database\Driver\Postgresql\PostgresqlTable::getIndexes
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
     * @covers \Windwalker\Database\Driver\Postgresql\PostgresqlTable::addIndex
     */
    public function testAddIndex()
    {
        $table = $this->db->getTable('#__categories', true);

        $table->addIndex('INDEX', ['ordering', 'id'], 'idx_ordering');

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
     * @covers \Windwalker\Database\Driver\Postgresql\PostgresqlTable::dropIndex
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
        if (!static::$dbo) {
            return;
        }

        if (!static::$debug) {
            try {
                static::$dbo->setQuery(PostgresqlGrammar::dropTable('#__cloud', true))->execute();
            } catch (\Exception $e) {
                // Do nothing
            }

            try {
                static::$dbo->setQuery(PostgresqlGrammar::dropTable('#__wind', true))->execute();
            } catch (\Exception $e) {
                // Do nothing
            }
        }

        parent::tearDownAfterClass();
    }
}
