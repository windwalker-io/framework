<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Session\Database;

/**
 * Class PdoAdapter
 *
 * The class is based on Symfony PdoSessionHandler
 *
 * @since 2.0
 */
class PdoAdapter extends AbstractDatabaseAdapter
{
    /**
     * Property db.
     *
     * @var  \PDO
     */
    protected $db = null;

    /**
     * Class init.
     *
     * @param \PDO  $db
     * @param array $options
     */
    public function __construct(\PDO $db, $options = [])
    {
        parent::__construct($db, $options);
    }

    /**
     * destroy
     *
     * @param int|string $sessionId
     *
     * @return  bool
     *
     * @throws \RuntimeException
     */
    public function destroy($sessionId)
    {
        // delete the record associated with this id
        $sql = "DELETE FROM {$this->options['table']} WHERE {$this->options['id_col']} = :id";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);

            $stmt->execute();
        } catch (\PDOException $e) {
            throw new \RuntimeException(
                sprintf(
                    'PDOException was thrown when trying to delete a session: %s',
                    $e->getMessage()
                ), 0, $e
            );
        }

        return true;
    }

    /**
     * gc
     *
     * @param string $past
     *
     * @return  bool
     *
     * @throws \RuntimeException
     */
    public function gc($past)
    {
        // delete the session records that have expired
        $sql = "DELETE FROM {$this->options['table']} WHERE {$this->options['time_col']} < :time";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->bindValue(':time', $past, \PDO::PARAM_INT);

            $stmt->execute();
        } catch (\PDOException $e) {
            throw new \RuntimeException(
                sprintf(
                    'PDOException was thrown when trying to delete expired sessions: %s',
                    $e->getMessage()
                ), 0, $e
            );
        }

        return true;
    }

    /**
     * read
     *
     * @param int|string $sessionId
     *
     * @return  string
     *
     * @throws \RuntimeException
     */
    public function read($sessionId)
    {
        $sql = "SELECT {$this->options['data_col']} FROM {$this->options['table']} WHERE {$this->options['id_col']} = :id";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);

            $stmt->execute();

            // We use fetchAll instead of fetchColumn to make sure the DB cursor gets closed
            $sessionRows = $stmt->fetchAll(\PDO::FETCH_NUM);

            if ($sessionRows) {
                return base64_decode($sessionRows[0][0]);
            }

            return '';
        } catch (\PDOException $e) {
            throw new \RuntimeException(
                sprintf(
                    'PDOException was thrown when trying to read the session data: %s',
                    $e->getMessage()
                ), 0, $e
            );
        }
    }

    /**
     * write
     *
     * @param int|string $sessionId
     * @param string     $data
     *
     * @return  bool
     *
     * @throws \RuntimeException
     */
    public function write($sessionId, $data)
    {
        $encoded = base64_encode($data);

        try {
            // We use a single MERGE SQL query when supported by the database.
            $mergeSql = $this->getMergeSql();

            if (null !== $mergeSql) {
                $mergeStmt = $this->db->prepare($mergeSql);

                $mergeStmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
                $mergeStmt->bindParam(':data', $encoded, \PDO::PARAM_STR);
                $mergeStmt->bindValue(':time', time(), \PDO::PARAM_INT);

                $mergeStmt->execute();

                return true;
            }

            $updateStmt = $this->db->prepare(
                "UPDATE {$this->options['table']} SET {$this->options['data_col']} = :data, {$this->options['time_col']} = :time WHERE {$this->options['id_col']} = :id"
            );

            $updateStmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
            $updateStmt->bindParam(':data', $encoded, \PDO::PARAM_STR);
            $updateStmt->bindValue(':time', time(), \PDO::PARAM_INT);

            $updateStmt->execute();

            // When MERGE is not supported, like in Postgres, we have to use this approach that can result in
            // duplicate key errors when the same session is written simultaneously. We can just catch such an
            // error and re-execute the update. This is similar to a serializable transaction with retry logic
            // on serialization failures but without the overhead and without possible false positives due to
            // longer gap locking.
            if (!$updateStmt->rowCount()) {
                try {
                    $insertStmt = $this->db->prepare(
                        "INSERT INTO {$this->options['table']} ({$this->options['id_col']}, {$this->options['data_col']}, {$this->options['time_col']}) VALUES (:id, :data, :time)"
                    );

                    $insertStmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
                    $insertStmt->bindParam(':data', $encoded, \PDO::PARAM_STR);
                    $insertStmt->bindValue(':time', time(), \PDO::PARAM_INT);

                    $insertStmt->execute();
                } catch (\PDOException $e) {
                    // Handle integrity violation SQLSTATE 23000 (or a subclass like 23505 in Postgres) for duplicate keys
                    if (0 === strpos($e->getCode(), '23')) {
                        $updateStmt->execute();
                    } else {
                        throw $e;
                    }
                }
            }
        } catch (\PDOException $e) {
            throw new \RuntimeException(
                sprintf(
                    'PDOException was thrown when trying to write the session data: %s',
                    $e->getMessage()
                ), 0, $e
            );
        }

        return true;
    }

    /**
     * Returns a merge/upsert (i.e. insert or update) SQL query when supported by the database.
     *
     * @return string|null The SQL string or null when not supported
     */
    private function getMergeSql()
    {
        $driver = $this->db->getAttribute(\PDO::ATTR_DRIVER_NAME);

        switch ($driver) {
            case 'mysql':
                return "INSERT INTO {$this->options['table']} ({$this->options['id_col']}, {$this->options['data_col']}, {$this->options['time_col']}) VALUES (:id, :data, :time) " .
                    "ON DUPLICATE KEY UPDATE {$this->options['data_col']} = VALUES({$this->options['data_col']}), {$this->options['time_col']} = VALUES({$this->options['time_col']})";

            case 'oci':
                // DUAL is Oracle specific dummy table
                return "MERGE INTO {$this->options['table']} USING DUAL ON ({$this->options['id_col']} = :id) " .
                    "WHEN NOT MATCHED THEN INSERT ({$this->options['id_col']}, {$this->options['data_col']}, {$this->options['time_col']}) VALUES (:id, :data, :time) " .
                    "WHEN MATCHED THEN UPDATE SET {$this->options['data_col']} = :data, {$this->options['time_col']} = :time";

            case 'sqlsrv' === $driver && version_compare(
                    $this->db->getAttribute(\PDO::ATTR_SERVER_VERSION), '10',
                    '>='
                ):
                // MERGE is only available since SQL Server 2008 and must be terminated by semicolon
                // It also requires HOLDLOCK according to http://weblogs.sqlteam.com/dang/archive/2009/01/31/UPSERT-Race-Condition-With-MERGE.aspx
                return "MERGE INTO {$this->options['table']} WITH (HOLDLOCK) USING (SELECT 1 AS dummy) AS src ON ({$this->options['id_col']} = :id) " .
                    "WHEN NOT MATCHED THEN INSERT ({$this->options['id_col']}, {$this->options['data_col']}, {$this->options['time_col']}) VALUES (:id, :data, :time) " .
                    "WHEN MATCHED THEN UPDATE SET {$this->options['data_col']} = :data, {$this->options['time_col']} = :time;";

            case 'sqlite':
                return "INSERT OR REPLACE INTO {$this->options['table']} ({$this->options['id_col']}, {$this->options['data_col']}, {$this->options['time_col']}) VALUES (:id, :data, :time)";
        }

        return '';
    }
}
