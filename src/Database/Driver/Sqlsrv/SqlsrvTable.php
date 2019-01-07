<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Windwalker\Database\Driver\Sqlsrv;

use Windwalker\Database\Command\AbstractTable;
use Windwalker\Database\Schema\Column;
use Windwalker\Database\Schema\Schema;

/**
 * The SqlsrvTable class.
 *
 * @since  __DEPLOY_VERSION__
 */
class SqlsrvTable extends AbstractTable
{
    /**
     * create
     *
     * @param   callable|Schema $callback
     * @param   bool            $ifNotExists
     * @param   array           $options
     *
     * @return  static
     */
    public function create($callback, $ifNotExists = true, $options = [])
    {
    }

    /**
     * rename
     *
     * @param string  $newName
     * @param boolean $returnNew
     *
     * @return  $this
     */
    public function rename($newName, $returnNew = true)
    {
    }

    /**
     * getColumnDetails
     *
     * @param bool $refresh
     *
     * @return mixed
     */
    public function getColumnDetails($refresh = false)
    {
    }

    /**
     * addColumn
     *
     * @param string $name
     * @param string $type
     * @param bool   $signed
     * @param bool   $allowNull
     * @param string $default
     * @param string $comment
     * @param array  $options
     *
     * @return  static
     */
    public function addColumn(
        $name,
        $type = 'text',
        $signed = true,
        $allowNull = true,
        $default = '',
        $comment = '',
        $options = []
    ) {
    }

    /**
     * modifyColumn
     *
     * @param string|Column $name
     * @param string        $type
     * @param bool          $signed
     * @param bool          $allowNull
     * @param string        $default
     * @param string        $comment
     * @param array         $options
     *
     * @return  static
     */
    public function modifyColumn(
        $name,
        $type = 'text',
        $signed = true,
        $allowNull = true,
        $default = '',
        $comment = '',
        $options = []
    ) {
    }

    /**
     * changeColumn
     *
     * @param string        $oldName
     * @param string|Column $newName
     * @param string        $type
     * @param bool          $signed
     * @param bool          $allowNull
     * @param string        $default
     * @param string        $comment
     * @param array         $options
     *
     * @return  static
     */
    public function changeColumn(
        $oldName,
        $newName,
        $type = 'text',
        $signed = true,
        $allowNull = true,
        $default = '',
        $comment = '',
        $options = []
    ) {
    }

    /**
     * addIndex
     *
     * @param string $type
     * @param array  $columns
     * @param string $name
     * @param string $comment
     * @param array  $options
     *
     * @return static
     */
    public function addIndex($type, $columns = [], $name = null, $comment = null, $options = [])
    {
    }

    /**
     * dropIndex
     *
     * @param string $name
     *
     * @return  static
     */
    public function dropIndex($name)
    {
    }

    /**
     * getIndexes
     *
     * @return  array
     */
    public function getIndexes()
    {
    }
}
