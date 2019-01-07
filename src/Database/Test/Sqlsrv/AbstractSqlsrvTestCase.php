<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Database\Test\Sqlsrv;

use Windwalker\Database\DatabaseFactory;
use Windwalker\Database\Driver\Mysql\MysqlDriver;
use Windwalker\Database\Test\AbstractDatabaseTestCase;

/**
 * The AbstractMysqlTest class.
 *
 * @since  2.0
 */
abstract class AbstractSqlsrvTestCase extends AbstractDatabaseTestCase
{
    /**
     * Property driver.
     *
     * @var  string
     */
    protected static $driver = 'sqlsrv';

    /**
     * Property quote.
     *
     * @var  array
     */
    protected static $quote = ['[', ']'];

    /**
     * Property db.
     *
     * @var MysqlDriver
     */
    protected $db;

    /**
     * Property connection.
     *
     * @var \PDO
     */
    protected $connection;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->db = DatabaseFactory::getDbo();
        $this->connection = $this->db->getConnection();
    }
}
