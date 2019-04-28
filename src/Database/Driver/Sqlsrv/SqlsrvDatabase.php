<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 $Asikart.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Database\Driver\Sqlsrv;

use Windwalker\Database\Command\AbstractDatabase;
use Windwalker\Query\Sqlsrv\SqlsrvGrammar;

/**
 * The SqlsrvDatabase class.
 *
 * @since  3.5
 */
class SqlsrvDatabase extends AbstractDatabase
{
    /**
     * select
     *
     * @return  static
     */
    public function select()
    {
        $this->db->setQuery('USE ' . $this->db->quoteName($this->name))->execute();

        return $this;
    }

    /**
     * createDatabase
     *
     * @param bool   $ifNotExists
     * @param string $charset
     * @param string $collate
     *
     * @return  static
     */
    public function create($ifNotExists = false, $charset = null, $collate = null)
    {
        if ($ifNotExists && $this->exists()) {
            return $this;
        }

        $query = SqlsrvGrammar::createDatabase($this->name, $collate);

        $this->db->setQuery($query)->execute();

        return $this;
    }

    /**
     * dropDatabase
     *
     * @param bool $ifExists
     *
     * @return  static
     */
    public function drop($ifExists = false)
    {
        $databases = $this->db->listDatabases();

        if (!in_array($this->getName(), $databases, true)) {
            return $this;
        }

        if ($this->getName() === $this->db->getDatabase()->getName()) {
            $this->db->disconnect();
            $this->db->setDatabaseName(null);
            $this->db->connect();
        }

        $query = $this->db->getQuery(true);

        $this->db->setQuery(
            $query->format('ALTER DATABASE %n SET SINGLE_USER WITH ROLLBACK IMMEDIATE', $this->name)
        )->execute();

        $sql = SqlsrvGrammar::dropDatabase($this->name, $ifExists);

        $this->db->setQuery($sql)->execute();

        return $this;
    }

    /**
     * renameDatabase
     *
     * @param string $newName
     * @param boolean $returnNew
     *
     * @return  static
     */
    public function rename($newName, $returnNew = true)
    {
        // TODO: handle rename files issues.
        // phpcs:disable
        // @see https://www.mssqltips.com/sqlservertip/4419/renaming-physical-database-file-names-for-a-sql-server-database/
        throw new \LogicException('Sqlsrv dose not support rename DB now.');

//        $query = $this->db->getQuery(true);
//
//        $filename = $this->db->setQuery(
//            $query->format('SELECT physical_name FROM sys.master_files WHERE name = %q', $this->name)
//        )->loadResult();
//
//        $path = dirname($filename);
//
//        $this->db->setQuery(
//            $query->format('ALTER DATABASE %n SET SINGLE_USER WITH ROLLBACK IMMEDIATE', $this->name)
//        )->execute();
//
//        $this->db->setQuery(
//            $query->format('ALTER DATABASE %n SET OFFLINE', $this->name)
//        )->execute();
//
//        $this->db->setQuery(
//            $query->format(
//                'ALTER DATABASE %n MODIFY FILE (Name=%q, FILENAME=%q)',
//                $this->name,
//                $this->name,
//                $path . '\\' . $newName . '.mdf'
//            )
//        )->execute();
//
//        $this->db->setQuery(
//            $query->format(
//                'ALTER DATABASE %n MODIFY FILE (Name=%q, FILENAME=%q)',
//                $this->name,
//                $this->name . '_log',
//                $path . '\\' . $newName . '_log.ldf'
//            )
//        )->execute();
//
//        $this->db->setQuery(
//            $query->format('ALTER DATABASE %n SET ONLINE', $this->name)
//        )->execute();
//
//        $this->db->setQuery(
//            $query->format('ALTER DATABASE %n MODIFY NAME = %n', $this->name, $newName)
//        )->execute();
//
//        $this->db->setQuery(
//            $query->format('ALTER DATABASE %n SET MULTI_USER WITH ROLLBACK IMMEDIATE', $newName)
//        )->execute();
//
//        if ($returnNew) {
//            return $this->db->getDatabase($newName)->select();
//        }

        return $this;
    }
}
