<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Database\Command;

use Windwalker\Database\Driver\AbstractDatabaseDriver;

/**
 * Class DatabaseTransaction
 *
 * @since 2.0
 */
abstract class AbstractTransaction
{
    /**
     * Property driver.
     *
     * @var  \Windwalker\Database\Driver\AbstractDatabaseDriver
     */
    protected $db;

    /**
     * The depth of the current transaction.
     *
     * @var    integer
     * @since  2.0
     */
    protected $depth = 0;

    /**
     * Property nested.
     *
     * @var  boolean
     */
    protected $nested = true;

    /**
     * Constructor.
     *
     * @param AbstractDatabaseDriver $db
     * @param bool                   $nested
     */
    public function __construct(AbstractDatabaseDriver $db, $nested = true)
    {
        $this->nested = $nested;

        $this->db = $db;
    }

    /**
     * start
     *
     * @return  static
     */
    abstract public function start();

    /**
     * commit
     *
     * @return  static
     */
    abstract public function commit();

    /**
     * rollback
     *
     * @return  static
     */
    abstract public function rollback();

    /**
     * getNested
     *
     * @return  boolean
     */
    public function getNested()
    {
        return $this->nested;
    }

    /**
     * setNested
     *
     * @param   boolean $nested
     *
     * @return  static  Return self to support chaining.
     */
    public function setNested($nested)
    {
        $this->nested = $nested;

        return $this;
    }

    /**
     * Method to get property Db
     *
     * @return  \Windwalker\Database\Driver\AbstractDatabaseDriver
     */
    public function getDriver()
    {
        return $this->db;
    }

    /**
     * Method to set property db
     *
     * @param   \Windwalker\Database\Driver\AbstractDatabaseDriver $db
     *
     * @return  static  Return self to support chaining.
     */
    public function setDriver($db)
    {
        $this->db = $db;

        return $this;
    }
}
