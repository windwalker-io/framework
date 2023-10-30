<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Session\Handler;

use Exception;
use Windwalker\Database\DatabaseAdapter;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Database\Platform\AbstractPlatform;
use Windwalker\Query\Bounded\ParamType;
use Windwalker\Query\Query;
use Windwalker\Utilities\Options\OptionAccessTrait;

/**
 * Database session storage handler for PHP
 *
 * @see    http://www.php.net/manual/en/function.session-set-save-handler.php
 * @since  2.0
 */
class DatabaseHandler extends AbstractHandler
{
    use OptionAccessTrait;

    /**
     * The DatabaseAdapter to use when querying.
     *
     * @var DatabaseAdapter
     */
    protected DatabaseAdapter $db;

    /**
     * isSupported
     *
     * @return  bool
     */
    public static function isSupported(): bool
    {
        return class_exists(DatabaseAdapter::class);
    }

    /**
     * Class init.
     *
     * @param  DatabaseAdapter  $db
     * @param  array            $options
     */
    public function __construct(DatabaseAdapter $db, array $options = [])
    {
        $this->db = $db;

        $this->prepareOptions(
            [
                'table' => 'windwalker_sessions',
                'columns' => [
                    'id' => 'id',
                    'data' => 'data',
                    'time' => 'time',
                ],
            ],
            $options
        );
    }

    /**
     * Read the data for a particular session identifier from the SessionHandler backend.
     *
     * @param  string  $id  The session identifier.
     *
     * @return  string  The session data.
     *
     * @throws Exception
     * @since   2.0
     */
    protected function doRead(string $id): ?string
    {
        return $this->db->select($this->getOption('columns')['data'])
            ->from($this->getOption('table'))
            ->where($this->getOption('columns')['id'], (string) $id)
            ->result();
    }

    /**
     * Write session data to the SessionHandler backend.
     *
     * @param  string  $id    The session identifier.
     * @param  string  $data  The session data.
     *
     * @return  boolean  True on success, false otherwise.
     * @since   2.0
     */
    public function write($id, $data): bool
    {
        $columns = $this->getOption('columns');

        $mergeSql = $this->getMergeSql($id, $data);

        if ($mergeSql !== null) {
            $mergeSql->execute();

            return true;
        }

        $this->db->transaction(
            function () use ($id, $data, $columns) {
                $item = [
                    $columns['data'] => $data,
                    $columns['time'] => (int) time(),
                    $columns['id'] => $id,
                ];

                $sess = $this->db->createQuery()
                    ->select('*')
                    ->from($this->getOption('table'))
                    ->where($columns['id'], $id)
                    ->forUpdate()
                    ->get();

                if ($sess === null) {
                    return $this->db->getWriter()->insertOne(
                        $this->getOption('table'),
                        $item
                    );
                }

                return $this->db->getWriter()->updateOne(
                    $this->getOption('table'),
                    $item,
                    $columns['id']
                );
            }
        );

        return true;
    }

    /**
     * Destroy the data for a particular session identifier in the SessionHandler backend.
     *
     * @param  string  $id  The session identifier.
     *
     * @return  boolean  True on success, false otherwise.
     *
     * @throws Exception
     * @since   2.0
     */
    public function destroy($id): bool
    {
        $columns = $this->getOption('columns');

        $this->db->delete($this->getOption('table'))
            ->where($columns['id'], $id)
            ->execute();

        return true;
    }

    /**
     * Garbage collect stale sessions from the SessionHandler backend.
     *
     * @param  int  $lifetime  The maximum age of a session.
     *
     * @return  int|false  Returns the number of deleted sessions on success, or false on failure.
     *
     * @throws  Exception
     * @since   2.0
     */
    public function gc($lifetime): int|false
    {
        // Determine the timestamp threshold with which to purge old sessions.
        $past = time() - $lifetime;

        $this->db->delete($this->getOption('table'))
            ->where($this->getOption('columns')['time'], '<', $past)
            ->execute();

        return $this->db->getWriter()->countAffected();
    }

    /**
     * updateTimestamp
     *
     * @param  string  $session_id
     * @param  string  $session_data
     *
     * @return  bool
     */
    public function updateTimestamp($session_id, $session_data): bool
    {
        $columns = $this->getOption('columns');

        $this->db->createQuery()
            ->update($this->getOption('table'))
            ->set($columns['time'], time())
            ->where($columns['id'], $session_id)
            ->execute();

        return true;
    }

    /**
     * Returns a merge/upsert (i.e. insert or update) SQL query when supported by the database.
     *
     * @return Query|null The SQL string or null when not supported
     */
    private function getMergeSql(string $id, string $data): ?Query
    {
        $platformName = $this->db->getPlatform()->getName();

        $columns = $this->getOption('columns');
        $table = $this->getOption('table');
        $time = time();

        $query = $this->db->createQuery();

        switch ($platformName) {
            case AbstractPlatform::MYSQL:
                $query->bind('id', $id, ParamType::STRING)
                    ->bind('data', $data, ParamType::STRING)
                    ->bind('time', $time, ParamType::INT);

                return $query->sql(
                    $query->format(
                        "INSERT INTO %n (%n, %n, %n) VALUES (:id, :data, :time)
ON DUPLICATE KEY UPDATE %n = VALUES(%n), %n = VALUES(%n)",
                        $table,
                        $columns['id'],
                        $columns['data'],
                        $columns['time'],
                        $columns['data'],
                        $columns['data'],
                        $columns['time'],
                        $columns['time']
                    )
                );

            case 'oci':
                $query->bind('id1', $id, ParamType::STRING)
                    ->bind('id2', $id, ParamType::STRING)
                    ->bind('data1', $data, ParamType::STRING)
                    ->bind('time1', $time, ParamType::INT)
                    ->bind('data2', $data, ParamType::STRING)
                    ->bind('time2', $time, ParamType::INT);

                // DUAL is Oracle specific dummy table
                return $query->sql(
                    $query->format(
                        " MERGE INTO %n USING DUAL ON (%n = :id1) WHEN NOT MATCHED
                    THEN INSERT (%n, %n, %n) VALUES (:id2, :data1, :time1) WHEN MATCHED
                    THEN UPDATE SET %n = :data2, %n = :time2",
                        $table,
                        $columns['id'],
                        $columns['id'],
                        $columns['data'],
                        $columns['time'],
                        $columns['data'],
                        $columns['time']
                    )
                );

            case AbstractPlatform::SQLSERVER === $platformName
                && version_compare(
                    $this->db->getDriver()->getVersion(),
                    '10',
                    '>='
                ):
                $query->bind('id1', $id, ParamType::STRING)
                    ->bind('id2', $id, ParamType::STRING)
                    ->bind('data1', $data, ParamType::STRING)
                    ->bind('time1', $time, ParamType::INT)
                    ->bind('data2', $data, ParamType::STRING)
                    ->bind('time2', $time, ParamType::INT);

                // phpcs:disable
                // MERGE is only available since SQL Server 2008 and must be terminated by semicolon
                // It also requires HOLDLOCK according to http://weblogs.sqlteam.com/dang/archive/2009/01/31/UPSERT-Race-Condition-With-MERGE.aspx
                return $query->sql(
                    $query->format(
                        "MERGE INTO %n WITH (HOLDLOCK) USING (SELECT 1 AS dummy) AS src ON (%n = :id1)
                    WHEN NOT MATCHED THEN INSERT (%n, %n, %n) VALUES (:id2, :data1, :time1)
                    WHEN MATCHED THEN UPDATE SET %n = :data2, %n = :time2;",
                        $table,
                        $columns['id'],
                        $columns['id'],
                        $columns['data'],
                        $columns['time'],
                        $columns['data'],
                        $columns['time']
                    )
                );

            case AbstractPlatform::SQLITE:
                $query->bind('id', $id, ParamType::STRING)
                    ->bind('data', $data, ParamType::STRING)
                    ->bind('time', $time, ParamType::INT);

                return $query->sql(
                    $query->format(
                        "INSERT OR REPLACE INTO %n (%n, %n, %n) VALUES (:id, :data, :time)",
                        $table,
                        $columns['id'],
                        $columns['data'],
                        $columns['time']
                    )
                );
        }

        return null;
    }
}
