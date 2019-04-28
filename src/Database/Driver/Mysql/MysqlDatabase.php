<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Database\Driver\Mysql;

use Windwalker\Database\Command\AbstractDatabase;
use Windwalker\Query\Mysql\MysqlGrammar;

/**
 * Class MysqlDatabase
 *
 * @since 2.0
 */
class MysqlDatabase extends AbstractDatabase
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
    public function create($ifNotExists = false, $charset = 'utf8', $collate = 'utf8_unicode_ci')
    {
        $query = MysqlGrammar::createDatabase($this->name, $ifNotExists, $charset, $collate);

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
        $query = MysqlGrammar::dropDatabase($this->name, $ifExists);

        $this->db->setQuery($query)->execute();

        return $this;
    }

    /**
     * renameDatabase
     *
     * @param string  $newName
     * @param boolean $returnNew
     *
     * @return  static
     */
    public function rename($newName, $returnNew = true)
    {
        // Mysql 5.1.7 do not have RENAME DATABASE syntax anymore, so we use rename tables to do that.
        // @see: http://stackoverflow.com/questions/67093/how-do-i-quickly-rename-a-mysql-database-change-schema-name?page=1&tab=votes#tab-top
        $newDatabase = $this->db->getDatabase($newName)->create();

        $tables = $this->db->getReader(MysqlGrammar::showDbTables($this->name))->loadObjectList();

        foreach ($tables as $table) {
            $name = $table->Name;

            $this->db->setQuery(
                sprintf(
                    'RENAME TABLE %s.%s TO %s.%s',
                    $this->db->quoteName($this->name),
                    $this->db->quoteName($name),
                    $this->db->quoteName($newName),
                    $this->db->quoteName($name)
                )
            )->execute();
        }

        $this->drop(true);

        if ($returnNew) {
            return $newDatabase;
        }

        return $this;
    }
}
