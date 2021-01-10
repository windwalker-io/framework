<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Session\Database;

use Windwalker\Database\Driver\AbstractDatabaseDriver;

/**
 * The WindwalkerAdapter class.
 *
 * @since  2.0
 */
class WindwalkerAdapter extends AbstractDatabaseAdapter
{
    /**
     * Property db.
     *
     * @var  \Windwalker\Database\Driver\AbstractDatabaseDriver
     */
    protected $db = null;

    /**
     * Class init.
     *
     * @param AbstractDatabaseDriver $db
     * @param array                  $options
     */
    public function __construct(AbstractDatabaseDriver $db, $options = [])
    {
        parent::__construct($db, $options);
    }

    /**
     * read
     *
     * @param string|int $id
     *
     * @return  string
     */
    public function read($id)
    {
        // Get the session data from the database table.
        $query = $this->db->getQuery(true);
        $query->select($this->db->quoteName($this->options['data_col']))
            ->from($this->db->quoteName($this->options['table']))
            ->where($this->db->quoteName($this->options['id_col']) . ' = ' . $this->db->quote($id));

        $this->db->setQuery($query);

        return (string) $this->db->loadResult();
    }

    /**
     * destroy
     *
     * @param string|int $id
     *
     * @return  boolean
     */
    public function destroy($id)
    {
        $query = $this->db->getQuery(true);
        $query->delete($this->db->quoteName($this->options['table']))
            ->where($this->db->quoteName($this->options['id_col']) . ' = ' . $this->db->quote($id));

        // Remove a session from the database.
        $this->db->setQuery($query);

        return (bool) $this->db->execute();
    }

    /**
     * gc
     *
     * @param string $past
     *
     * @return  bool
     */
    public function gc($past)
    {
        $query = $this->db->getQuery(true);
        $query->delete($this->db->quoteName($this->options['table']))
            ->where($this->db->quoteName($this->options['time_col']) . ' < ' . $this->db->quote((int) $past));

        // Remove expired sessions from the database.
        $this->db->setQuery($query);

        return (bool) $this->db->execute();
    }

    /**
     * write
     *
     * @param int|string $id
     * @param string     $data
     *
     * @return  bool
     *
     * @throws \RuntimeException
     */
    public function write($id, $data)
    {
        $time = time();

        // We use a single MERGE SQL query when supported by the database.
        $mergeSql = $this->getMergeSql($id, $data, $time);

        if (null !== $mergeSql) {
            $this->db->execute($mergeSql);

            return true;
        }

        $writer = $this->db->getWriter();

        $data = [
            $this->options['data_col'] => $data,
            $this->options['time_col'] => $time,
            $this->options['id_col'] => $id,
        ];

        $writer->updateOne($this->options['table'], $data, $this->options['id_col']);

        if ($writer->countAffected()) {
            return true;
        }

        $writer->insertOne($this->options['table'], $data, $this->options['id_col']);

        return true;
    }

    /**
     * Returns a merge/upsert (i.e. insert or update) SQL query when supported by the database.
     *
     * @return string|null The SQL string or null when not supported
     */
    private function getMergeSql(string $sessionId, string $data, int $time)
    {
        /** @var \PDO $conn */
        $conn = $this->db->getConnection();
        $driver = $conn->getAttribute(\PDO::ATTR_DRIVER_NAME);

        $cols = $this->db->getTable($this->options['table'])->getColumnDetails();
        
        if ($cols[$this->options['id_col']]->Key !== 'PRI') {
            return null;
        }

        switch ($driver) {
            case 'mysql':
                return $this->db->getQuery(true)->format(
<<<SQL
INSERT INTO {$this->options['table']} ({$this->options['id_col']}, 
{$this->options['data_col']},
{$this->options['time_col']}) 
 VALUES (%q, %q, %q)
 ON DUPLICATE KEY UPDATE {$this->options['data_col']} = VALUES({$this->options['data_col']}), 
 {$this->options['time_col']} = VALUES({$this->options['time_col']}) 
SQL
                    ,
                    $sessionId,
                    $data,
                    $time
                );

            case 'oci':
                // DUAL is Oracle specific dummy table
                return $this->db->getQuery(true)->format(
<<<SQL
 MERGE INTO {$this->options['table']} USING DUAL ON ({$this->options['id_col']} = %q) 
 WHEN NOT MATCHED THEN INSERT ({$this->options['id_col']}, 
 {$this->options['data_col']}, 
 {$this->options['time_col']}) 
 VALUES (%q, %q, %q)  
 WHEN MATCHED THEN UPDATE SET {$this->options['data_col']} = %q, {$this->options['time_col']} = %q
SQL
                    ,
                    $sessionId,
                    $sessionId,
                    $data,
                    $time,
                    $data,
                    $time
                );

            case 'sqlsrv' === $driver && version_compare(
                    $conn->getAttribute(\PDO::ATTR_SERVER_VERSION),
                    '10',
                    '>='
                ):
                // @codingStandardsIgnoreStart
                // MERGE is only available since SQL Server 2008 and must be terminated by semicolon
                // It also requires HOLDLOCK according to http://weblogs.sqlteam.com/dang/archive/2009/01/31/UPSERT-Race-Condition-With-MERGE.aspx
                return $this->db->getQuery(true)->format(
                    "MERGE INTO {$this->options['table']} WITH (HOLDLOCK) USING (SELECT 1 AS dummy) AS src ON ({$this->options['id_col']} = %q) " .
                    "WHEN NOT MATCHED THEN INSERT ({$this->options['id_col']}, {$this->options['data_col']}, {$this->options['time_col']}) VALUES (%q, %q, %q) " .
                    "WHEN MATCHED THEN UPDATE SET {$this->options['data_col']} = %q, {$this->options['time_col']} = %q;",
                    $sessionId,
                    $sessionId,
                    $data,
                    $time,
                    $data,
                    $time
                );

            case 'sqlite':
                return $this->db->getQuery(true)->format(
                    "INSERT OR REPLACE INTO {$this->options['table']} ({$this->options['id_col']}, {$this->options['data_col']}, {$this->options['time_col']}) VALUES (%q, %q, %q)",
                    $sessionId,
                    $data,
                    $time
                );
        }

        // @codingStandardsIgnoreEnd
        return '';
    }
}
