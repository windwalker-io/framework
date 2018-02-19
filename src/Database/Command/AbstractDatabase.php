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
 * Class DatabaseDatabase
 *
 * @since 2.0
 */
abstract class AbstractDatabase
{
    /**
     * Property database.
     *
     * @var  string
     */
    protected $name = null;

    /**
     * Property driver.
     *
     * @var  AbstractDatabaseDriver
     */
    protected $db;

    /**
     * Property tablesCache.
     *
     * @var  array
     */
    protected $tableCache = [];

    /**
     * Constructor.
     *
     * @param string                 $name
     * @param AbstractDatabaseDriver $db
     */
    public function __construct($name, AbstractDatabaseDriver $db)
    {
        $this->name = $name;

        $this->db = $db;
    }

    /**
     * select
     *
     * @return  static
     */
    abstract public function select();

    /**
     * createDatabase
     *
     * @param bool   $ifNotExists
     * @param string $charset
     *
     * @return  static
     */
    abstract public function create($ifNotExists = false, $charset = null);

    /**
     * dropDatabase
     *
     * @param bool $ifExists
     *
     * @return  static
     */
    abstract public function drop($ifExists = false);

    /**
     * exists
     *
     * @return  boolean
     */
    public function exists()
    {
        $databases = $this->db->listDatabases();

        return in_array($this->name, $databases);
    }

    /**
     * renameDatabase
     *
     * @param string  $newName
     * @param boolean $returnNew
     *
     * @return  static
     */
    abstract public function rename($newName, $returnNew = true);

    /**
     * getTable
     *
     * @param string $name
     * @param bool   $new
     *
     * @return  AbstractTable
     */
    public function getTable($name, $new = false)
    {
        $table = $this->db->getTable($name, $new);

        $table->setDatabase($this);

        return $table;
    }

    /**
     * Method to get an array of all tables in the database.
     *
     * @param bool $refresh
     *
     * @return  array  An array of all the tables in the database.
     *
     * @since   2.0
     */
    public function getTables($refresh = false)
    {
        return array_keys($this->getTableDetails($refresh));
    }

    /**
     * getTableDetails
     *
     * @param  boolean $refresh
     *
     * @return \stdClass[]
     */
    public function getTableDetails($refresh = false)
    {
        if (!isset($this->tableCache[$this->name]) || $refresh) {
            $builder = $this->db->getQuery(true)->getGrammar();

            $query = $builder::showDbTables($this->name);

            $details = $this->db->setQuery($query)->loadAll('Name');

            $this->tableCache[$this->name] = $details;
        }

        return $this->tableCache[$this->name];
    }

    /**
     * getTableDetail
     *
     * @param bool $table
     *
     * @return  mixed
     */
    public function getTableDetail($table)
    {
        $tables = $this->getTableDetails();

        $table = $this->db->replacePrefix($table);

        if (!isset($tables[$table])) {
            return false;
        }

        return $tables[$table];
    }

    /**
     * tableExists
     *
     * @param string $table
     *
     * @return  boolean
     */
    public function tableExists($table)
    {
        return (bool) $this->getTableDetail($table);
    }

    /**
     * Method to get property Table
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Method to set property table
     *
     * @param   string $name
     *
     * @return  static  Return self to support chaining.
     */
    public function setName($name)
    {
        $this->name = $name;

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

    /**
     * resetCache
     *
     * @return  static
     */
    public function reset()
    {
        $this->tableCache = [];

        return $this;
    }
}

