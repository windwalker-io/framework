<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Database\Test\Postgresql;

use Windwalker\Database\Driver\Postgresql\PostgresqlTransaction;

/**
 * Test class of PostgresqlTransaction
 *
 * @since 2.0
 */
class PostgresqlTransactionTest extends AbstractPostgresqlTestCase
{
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
     * Method to test getNested().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Command\AbstractTransaction::getNested
     */
    public function testGetNested()
    {
        $tran = new PostgresqlTransaction($this->db, false);

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
        $tran = new PostgresqlTransaction($this->db);

        $tran->setNested(false);

        $this->assertFalse($tran->getNested());
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

    /**
     * Method to test start().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Driver\Postgresql\PostgresqlTransaction::start
     * @covers \Windwalker\Database\Driver\Postgresql\PostgresqlTransaction::rollback
     */
    public function testTransactionRollback()
    {
        $table = '#__flower';

        $sql = "INSERT INTO {$table} (catid, title) VALUES (1, 'A'), (2, 'B'), (3, 'C')";

        $tran = $this->db->getTransaction()->start();

        $this->db->setQuery($sql)->execute();

        $tran->rollback();

        $result = $this->db->getReader('SELECT title FROM #__flower WHERE title = \'A\'')->loadResult();

        $this->assertFalse($result);
    }

    /**
     * Method to test start().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Driver\Postgresql\PostgresqlTransaction::start
     * @covers \Windwalker\Database\Driver\Postgresql\PostgresqlTransaction::commit
     */
    public function testTransactionCommit()
    {
        $table = '#__flower';

        $sql = "INSERT INTO {$table} (catid, title) VALUES (1, 'A'), (2, 'B'), (3, 'C')";

        $tran = $this->db->getTransaction()->start();

        $this->db->setQuery($sql)->execute();

        $tran->commit();

        $result = $this->db->getReader('SELECT title FROM #__flower WHERE title = \'A\'')->loadResult();

        $this->assertEquals('A', $result);
    }
}
