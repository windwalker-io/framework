<?php
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Database\Test\Mysql;

use Windwalker\Database\Driver\Mysql\MysqlTransaction;

/**
 * Test class of MysqlTransaction
 *
 * @since 2.0
 */
class MysqlTransactionTest extends AbstractMysqlTestCase
{
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
     * Method to test getNested().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Command\AbstractTransaction::getNested
     */
    public function testGetNested()
    {
        $tran = new MysqlTransaction($this->db, false);

        $this->assertFalse($tran->getNested());
    }

    /**
     * Method to test setNested().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Command\AbstractTransaction::setNested
     */
    public function testSetNested()
    {
        $tran = new MysqlTransaction($this->db);

        $tran->setNested(false);

        $this->assertFalse($tran->getNested());
    }

    /**
     * Method to test start().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Driver\Mysql\MysqlTransaction::start
     * @covers \Windwalker\Database\Driver\Mysql\MysqlTransaction::rollback
     */
    public function testTransactionRollback()
    {
        $table = '#__flower';

        $sql = "INSERT INTO {$table} (title, meaning, params) VALUES ('A', '', ''), ('B', '', ''), ('C', '', '')";

        $tran = $this->db->getTransaction()->start();

        $this->db->setQuery($sql)->execute();

        $tran->rollback();

        $result = $this->db->getReader('SELECT title FROM #__flower WHERE title = "A"')->loadResult();

        $this->assertFalse($result);
    }

    /**
     * Method to test start().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Driver\Mysql\MysqlTransaction::start
     * @covers \Windwalker\Database\Driver\Mysql\MysqlTransaction::commit
     */
    public function testTransactionCommit()
    {
        $table = '#__flower';

        $sql = "INSERT INTO {$table} (title, meaning, params) VALUES ('A', '', ''), ('B', '', ''), ('C', '', '')";

        $tran = $this->db->getTransaction()->start();

        $this->db->setQuery($sql)->execute();

        $tran->commit();

        $result = $this->db->getReader('SELECT title FROM #__flower WHERE title = "A"')->loadResult();

        $this->assertEquals('A', $result);
    }

    /**
     * Method to test getDriver().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Command\AbstractTransaction::getDriver
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
     * @covers \Windwalker\Database\Command\AbstractTransaction::setDriver
     * @TODO   Implement testSetDriver().
     */
    public function testSetDriver()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
