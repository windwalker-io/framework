<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Database\Driver\Postgresql;

use Windwalker\Database\Driver\Pdo\PdoReader;
use Windwalker\Query\Query;

/**
 * Class PostgresqlReader
 *
 * @since 2.0
 */
class PostgresqlReader extends PdoReader
{
    /**
     * Method to get the auto-incremented value from the last INSERT statement.
     *
     * @param   string $name Name of the sequence object from which the ID should be returned.
     *
     * @return  string  The value of the auto-increment field from the last inserted row.
     *
     * @since   2.1
     */
    public function insertId($name = null)
    {
        if ($name) {
            $name = $this->db->replacePrefix($name);

            // Error suppress this to prevent PDO warning us that the driver doesn't support this operation.
            return @$this->db->getConnection()->lastInsertId($name);
        }

        $insertQuery = $this->db->getQuery();

        if ($insertQuery instanceof Query) {
            $table = $insertQuery->insert->getElements();
        } else {
            preg_match('/insert\s*into\s*[\"]*(\W\w+)[\"]*/i', $insertQuery, $matches);

            if (!isset($matches[1])) {
                return false;
            }

            $table = [$matches[1]];
        }

        /* find sequence column name */
        $colNameQuery = $this->db->getQuery(true);

        $colNameQuery->select('column_default')
            ->from('information_schema.columns')
            ->where("table_name=" . $this->db->quote($this->db->replacePrefix(trim($table[0], '"'))))
            ->where("column_default LIKE '%nextval%'");

        $colName = $this->db->getReader($colNameQuery)->loadArray();

        $changedColName = str_replace('nextval', 'currval', $colName);

        $insertidQuery = $this->db->getQuery(true);

        $insertidQuery->select($changedColName);

        try {
            return $this->db->getReader($insertidQuery)->loadResult();
        } catch (\PDOException $e) {
            // 55000 means we trying to insert value to serial column
            // Just return because insertedId get the last generated value.
            if ($e->getCode() !== 55000) {
                throw $e;
            }
        }

        return null;
    }
}
