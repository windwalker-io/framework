<?php

declare(strict_types=1);

namespace Windwalker\Session\Handler;

use Exception;
use Windwalker\Database\DatabaseAdapter;
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
     * @var  DatabaseAdapter
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
     * @param  array{
     *     table?: string,
     *     columns?: array{
     *      id?: string,
     *      data?: string,
     *      time?: string
     *     },
     *     gc_maxlifetime?: int|string|null
     * } $options
     * @param  (\Closure(array<string, mixed> $values, string $id, string $data): array<string, mixed>)|null  $writeHandler
     *                                                                                                                       Customize the column values before they are written to the session table.
     */
    public function __construct(
        DatabaseAdapter $db,
        array $options = [],
        public ?\Closure $writeHandler = null,
    ) {
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
     * @throws  Exception
     * @since   2.0
     */
    protected function doRead(string $id): ?string
    {
        $columns = $this->getOption('columns');
        $lifetime = (int) ($this->getOption('gc_maxlifetime') ?? ini_get('session.gc_maxlifetime'));

        return $this->db->select($columns['data'])
            ->from($this->getOption('table'))
            ->where($columns['id'], (string) $id)
            ->where($columns['time'], '>', time() - $lifetime)
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

        $values = [
            $columns['id'] => $id,
            $columns['data'] => $data,
            $columns['time'] => time(),
        ];

        if ($this->writeHandler) {
            $values = ($this->writeHandler)($values, $id, $data);
            $values[$columns['id']] = $id;
        }

        $this->db->getWriter()->upsert(
            $this->getOption('table'),
            $values,
            $columns['id'],
            array_values(array_diff(array_keys($values), [$columns['id']]))
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
     * @throws  Exception
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
}
