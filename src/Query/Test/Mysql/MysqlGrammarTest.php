<?php
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Query\Test\Mysql;

use Windwalker\Database\Test\AbstractQueryTestCase;
use Windwalker\Query\Mysql\MysqlGrammar;
use Windwalker\Query\Mysql\MysqlQuery;
use Windwalker\Query\Query;

/**
 * Test class of MysqlGrammar
 *
 * @since 2.0
 */
class MysqlGrammarTest extends AbstractQueryTestCase
{
    /**
     * Property quote.
     *
     * @var  array
     */
    protected static $quote = '`';

    /**
     * Method to test showDatabases().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Mysql\MysqlGrammar::listDatabases
     */
    public function testShowDatabases()
    {
        $expected = "SHOW DATABASES WHERE a = b";

        $actual = MysqlGrammar::listDatabases('a = b');

        $this->assertEquals($this->format($expected), $this->format($actual));
    }

    /**
     * Method to test createDatabase().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Mysql\MysqlGrammar::createDatabase
     */
    public function testCreateDatabase()
    {
        $expected = "CREATE DATABASE {$this->qn('foo')}";

        $actual = MysqlGrammar::createDatabase('foo');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );

        $expected = "CREATE DATABASE IF NOT EXISTS {$this->qn('foo')}";

        $actual = MysqlGrammar::createDatabase('foo', true);

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );

        $expected = "CREATE DATABASE IF NOT EXISTS {$this->qn('foo')} CHARACTER SET='utf8' COLLATE='bar'";

        $actual = MysqlGrammar::createDatabase('foo', true, 'utf8', 'bar');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );
    }

    /**
     * Method to test dropDatabase().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Mysql\MysqlGrammar::dropDatabase
     */
    public function testDropDatabase()
    {
        $expected = "DROP DATABASE {$this->qn('foo')}";

        $actual = MysqlGrammar::dropDatabase('foo');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );

        $expected = "DROP DATABASE IF EXISTS {$this->qn('foo')}";

        $actual = MysqlGrammar::dropDatabase('foo', true);

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );
    }

    /**
     * Method to test showTableColumns().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Mysql\MysqlGrammar::showTableColumns
     */
    public function testShowTableColumns()
    {
        $expected = "SHOW COLUMNS FROM {$this->qn('foo')}";

        $actual = MysqlGrammar::showTableColumns('foo');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );

        $expected = "SHOW FULL COLUMNS FROM {$this->qn('foo')} WHERE a = b";

        $actual = MysqlGrammar::showTableColumns('foo', true, 'a = b');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );
    }

    /**
     * Method to test showDbTables().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Mysql\MysqlGrammar::showDbTables
     */
    public function testShowDbTables()
    {
        $expected = "SHOW TABLE STATUS FROM {$this->qn('foo')}";

        $actual = MysqlGrammar::showDbTables('foo');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );

        $expected = "SHOW TABLE STATUS FROM {$this->qn('foo')} WHERE a = b";

        $actual = MysqlGrammar::showDbTables('foo', 'a = b');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );
    }

    /**
     * Method to test createTable().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Mysql\MysqlGrammar::createTable
     */
    public function testCreateTable()
    {
        $expected = <<<SQL
CREATE TABLE IF NOT EXISTS {$this->qn('foo')} (
  {$this->qn('id')} int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  {$this->qn('name')} varchar(255) NOT NULL COMMENT 'Member Name',
  {$this->qn('email')} varchar(255) NOT NULL COMMENT 'Member email',
  PRIMARY KEY ({$this->qn('id')}),
  KEY {$this->qn('idx_alias')} ({$this->qn('email')})
) ENGINE=InnoDB AUTO_INCREMENT=415 DEFAULT CHARSET=utf8mb4 COLLATE = utf8mb4_unicode_ci
SQL;

        $columns = [
            'id' => 'int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT \'Primary Key\'',
            'name' => ['varchar(255)', 'NOT NULL', 'COMMENT \'Member Name\''],
            'email' => "varchar(255) NOT NULL COMMENT 'Member email'",
        ];

        $keys = [
            ['type' => 'KEY', 'name' => 'idx_alias', 'columns' => 'email'],
        ];

        $actual = MysqlGrammar::createTable('foo', $columns, 'id', $keys, 415, true, 'InnoDB');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );

        $expected = <<<SQL
CREATE TABLE {$this->qn('foo')} (
  {$this->qn('id')} int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  {$this->qn('name')} varchar(255) NOT NULL COMMENT 'Member Name',
  {$this->qn('email')} varchar(255) NOT NULL COMMENT 'Member email',
  PRIMARY KEY ({$this->qn('id')}, {$this->qn('email')}),
  UNIQUE KEY {$this->qn('idx_alias')} ({$this->qn('email')}, {$this->qn('id')})
) ENGINE=InnoDB AUTO_INCREMENT=415 DEFAULT CHARSET=utf8mb4 COLLATE = utf8mb4_unicode_ci
SQL;

        $columns = [
            'id' => 'int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT \'Primary Key\'',
            'name' => ['varchar(255)', 'NOT NULL', 'COMMENT \'Member Name\''],
            'email' => "varchar(255) NOT NULL COMMENT 'Member email'",
        ];

        $keys = [
            ['type' => 'UNIQUE KEY', 'name' => 'idx_alias', 'columns' => ['email', 'id']],
        ];

        $actual = MysqlGrammar::createTable('foo', $columns, ['id', 'email'], $keys, 415, false, 'InnoDB');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );
    }

    /**
     * Method to test dropTable().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Mysql\MysqlGrammar::dropTable
     */
    public function testDropTable()
    {
        $expected = "DROP TABLE {$this->qn('foo')}";

        $actual = MysqlGrammar::dropTable('foo');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );

        $expected = "DROP TABLE IF EXISTS {$this->qn('foo')}";

        $actual = MysqlGrammar::dropTable('foo', true);

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );
    }

    /**
     * Method to test alterColumn().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Mysql\MysqlGrammar::alterColumn
     */
    public function testAlterColumn()
    {
        $expected = "ALTER TABLE {$this->qn('foo')} MODIFY {$this->qn('bar')} int(11) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Test' FIRST";

        $actual = MysqlGrammar::alterColumn('MODIFY', 'foo', 'bar', 'int(11)', false, false, '1', 'FIRST', 'Test');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );

        $expected = "ALTER TABLE {$this->qn('foo')} CHANGE {$this->qn('bar')} {$this->qn('yoo')} text AFTER {$this->qn('id')}";

        $actual = MysqlGrammar::alterColumn(
            'CHANGE',
            'foo',
            ['bar', 'yoo'],
            'text',
            true,
            true,
            false,
            'AFTER id',
            null
        );

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );
    }

    /**
     * Method to test addColumn().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Mysql\MysqlGrammar::addColumn
     */
    public function testAddColumn()
    {
        $expected = "ALTER TABLE {$this->qn('foo')} ADD {$this->qn('bar')} int(11) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Test' FIRST";

        $actual = MysqlGrammar::addColumn('foo', 'bar', 'int(11)', false, false, '1', 'FIRST', 'Test');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );
    }

    /**
     * Method to test changeColumn().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Mysql\MysqlGrammar::changeColumn
     */
    public function testChangeColumn()
    {
        $expected = "ALTER TABLE {$this->qn('foo')} CHANGE {$this->qn('bar')} {$this->qn('yoo')} int(11) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Test' FIRST";

        $actual = MysqlGrammar::changeColumn('foo', 'bar', 'yoo', 'int(11)', false, false, '1', 'FIRST', 'Test');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );
    }

    /**
     * Method to test modifyColumn().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Mysql\MysqlGrammar::modifyColumn
     */
    public function testModifyColumn()
    {
        $expected = "ALTER TABLE {$this->qn('foo')} MODIFY {$this->qn('bar')} int(11) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Test' FIRST";

        $actual = MysqlGrammar::modifyColumn('foo', 'bar', 'int(11)', false, false, '1', 'FIRST', 'Test');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );
    }

    /**
     * Method to test dropColumn().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Mysql\MysqlGrammar::dropColumn
     */
    public function testDropColumn()
    {
        $expected = "ALTER TABLE {$this->qn('foo')} DROP {$this->qn('bar')}";

        $actual = MysqlGrammar::dropColumn('foo', 'bar');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );
    }

    /**
     * Method to test addIndex().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Mysql\MysqlGrammar::addIndex
     */
    public function testAddIndex()
    {
        $expected = "ALTER TABLE {$this->qn('foo')} ADD KEY {$this->qn('idx_alias')} ({$this->qn('alias')}, {$this->qn('name')}) COMMENT 'Test Index'";

        $actual = MysqlGrammar::addIndex('foo', 'KEY', ['alias', 'name'], 'idx_alias', 'Test Index');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );

        $expected = "ALTER TABLE {$this->qn('foo')} ADD KEY {$this->qn('idx_alias')} ({$this->qn('alias')}) COMMENT 'Test Index'";

        $actual = MysqlGrammar::addIndex('foo', 'KEY', 'alias', 'idx_alias', 'Test Index');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );
    }

    /**
     * Method to test buildIndexDeclare().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Mysql\MysqlGrammar::buildIndexDeclare
     */
    public function testBuildIndexDeclare()
    {
        $expected = "{$this->qn('idx_alias')} ({$this->qn('alias')})";

        $actual = MysqlGrammar::buildIndexDeclare('idx_alias', 'alias');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );

        $expected = "{$this->qn('idx_alias')} ({$this->qn('alias')}, {$this->qn('name')})";

        $actual = MysqlGrammar::buildIndexDeclare('idx_alias', ['alias', 'name']);

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );
    }

    /**
     * Method to test dropIndex().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Mysql\MysqlGrammar::dropIndex
     */
    public function testDropIndex()
    {
        $expected = "DROP INDEX {$this->qn('bar')} ON {$this->qn('foo')}";

        $actual = MysqlGrammar::dropIndex('foo', 'bar');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );
    }

    /**
     * Method to test build().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Mysql\MysqlGrammar::build
     */
    public function testBuild()
    {
        $expected = "FLOWER SAKURA SUNFLOWER OLIVE";

        $actual = MysqlGrammar::build('FLOWER', 'SAKURA', 'SUNFLOWER', 'OLIVE');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );
    }

    /**
     * Method to test replace().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Mysql\MysqlGrammar::replace
     */
    public function testReplace()
    {
        $expected = "REPLACE INTO {$this->qn('foo')} (a,b) VALUES (c, d, e), (f, g, h)";

        $actual = MysqlGrammar::replace('foo', ['a', 'b'], ['c, d, e', 'f, g, h']);

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );
    }

    /**
     * Method to test getQuery().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Mysql\MysqlGrammar::getQuery
     */
    public function testGetQuery()
    {
        $this->assertInstanceOf('Windwalker\\Query\\Mysql\\MysqlQuery', MysqlGrammar::getQuery());

        $this->assertSame(MysqlGrammar::getQuery(), MysqlGrammar::getQuery());

        $this->assertNotSame(MysqlGrammar::getQuery(), MysqlGrammar::getQuery(true));
    }
}
