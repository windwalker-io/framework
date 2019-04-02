<?php
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
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

    /**
     * testTransactionNested
     *
     * @return  void
     *
     * @since  3.5
     */
    public function testTransactionNested()
    {
        $table = '#__flower';

        // Level 1
        $sql = "INSERT INTO {$table} (catid, title, meaning, params) VALUES (0, 'D', '', '')";

        $tran = $this->db->getTransaction()->start();

        $this->db->execute($sql);

        // Level 2
        $sql = "INSERT INTO {$table} (catid, title, meaning, params) VALUES (0, 'E', '', '')";

        $tran = $tran->start();

        $this->db->execute($sql);

        $tran->rollback();
        $tran->commit();

        $result = $this->db->getReader('SELECT title FROM #__flower WHERE title = \'D\'')->loadResult();
        $this->assertEquals('D', $result);

        $result2 = $this->db->getReader('SELECT title FROM #__flower WHERE title = \'E\'')->loadResult();
        $this->assertNotEquals('E', $result2);
    }
}
