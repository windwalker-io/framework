<?php

declare(strict_types=1);

namespace Windwalker\Session\Handler;

use BadMethodCallException;
use Exception;
use PDO;
use Throwable;
use Windwalker\Query\Grammar\AbstractGrammar;
use Windwalker\Query\Query;
use Windwalker\Utilities\Options\OptionAccessTrait;

/**
 * The PdoHandler class.
 */
class PdoHandler extends AbstractHandler
{
    use OptionAccessTrait;

    protected PDO $db;

    /**
     * isSupported
     *
     * @return  bool
     */
    public static function isSupported(): bool
    {
        return class_exists(PDO::class);
    }

    /**
     * Class init.
     *
     * @param  PDO  $db
     * @param  array{
     *     table?: string,
     *     columns?: array{
     *      id?: string,
     *      data?: string,
     *      time?: string,
     *     },
     *     gc_maxlifetime?: int|string|null
     * } $options
     */
    public function __construct(PDO $db, array $options = [])
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
     * @throws  Exception
     * @since   2.0
     */
    protected function doRead(string $id): ?string
    {
        $columns = $this->getOption('columns');
        $lifetime = (int) ($this->getOption('gc_maxlifetime') ?? ini_get('session.gc_maxlifetime'));

        $query = $this->createQuery()
            ->select($columns['data'])
            ->from($this->getOption('table'))
            ->where($columns['id'], $id)
            ->where($columns['time'], '>', time() - $lifetime);

        $stmt = $this->db->prepare($sql = $query->forPDO($params));

        $stmt->execute($params);
        $item = $stmt->fetchAll(PDO::FETCH_NUM);

        if ($item) {
            return $item[0][0] ?? null;
        }

        return null;
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

        $query = $this->createQuery();

        try {
            $query->upsert($this->getOption('table'))
                ->set(
                    [
                        $columns['id'] => $id,
                        $columns['data'] => $data,
                        $columns['time'] => time(),
                    ]
                )
                ->onConflict($columns['id'])
                ->onConflictUpdateColumns($columns['data'], $columns['time']);

            $stmt = $this->db->prepare($query->forPDO($upsertParams));
            $stmt->execute($upsertParams);

            return true;
        } catch (BadMethodCallException) {
            // Grammar does not support upsert, fallback to select-then-write.
        }

        $this->db->beginTransaction();

        $item = [
            $columns['data'] => $data,
            $columns['time'] => (int) time(),
            $columns['id'] => $id,
        ];

        $query = $this->createQuery()
            ->select('*')
            ->from($this->getOption('table'))
            ->where($columns['id'], $id)
            ->forUpdate();

        try {
            $stmt = $this->db->prepare($query->forPDO($params));
            $stmt->execute($params);
            $sess = $stmt->fetchObject();

            if ($sess === null) {
                $query = $this->createQuery()
                    ->insert($this->getOption('table'))
                    ->columns(array_values($this->getOption('columns')))
                    ->values(array_values($item));

                $stmt = $this->db->prepare($query->forPDO($params));
                $stmt->execute($params);
                $this->db->commit();

                return true;
            }

            $query = $this->createQuery()
                ->update($this->getOption('table'))
                ->where($columns['id'], $id);

            foreach ($item as $k => $v) {
                $query->set($k, $v);
            }

            $stmt = $this->db->prepare($query->forPDO($updateParams));
            $stmt->execute($updateParams);
            $this->db->commit();

            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();

            throw $e;
        }

        return true;
    }

    /**
     * updateTimestamp
     *
     * @param  string  $id
     * @param  string  $data
     *
     * @return  bool
     */
    public function updateTimestamp($id, $data): bool
    {
        $columns = $this->getOption('columns');

        $query = $this->createQuery()
            ->update($this->getOption('table'))
            ->set($columns['time'], time())
            ->where($columns['id'], $id);

        $stmt = $this->db->prepare($query->forPDO($params));
        $stmt->execute($params);

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

        $query = $this->createQuery()
            ->delete($this->getOption('table'))
            ->where($columns['id'], $id);

        $stmt = $this->db->prepare($query->forPDO($params));
        $stmt->execute($params);

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

        $query = $this->createQuery()
            ->delete($this->getOption('table'))
            ->where($this->getOption('columns')['time'], '<', $past);

        $stmt = $this->db->prepare($query->forPDO($params));
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    /**
     * Returns a merge/upsert (i.e. insert or update) SQL query when supported by the database.
     *
     * @return  string|null  The SQL string or null when not supported
     */
    protected function createQuery(): Query
    {
        $platform = $this->getPlatformName();

        return new Query($this->db, AbstractGrammar::create($platform));
    }

    /**
     * getPlatformName
     *
     * @return  string
     */
    protected function getPlatformName(): string
    {
        $platform = (string) $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);

        switch (strtolower($platform)) {
            case 'pgsql':
            case 'postgresql':
                $platform = 'PostgreSQL';
                break;

            case 'sqlsrv':
            case 'sqlserver':
                $platform = 'SQLServer';
                break;

            case 'mysql':
                $platform = 'MySQL';
                break;

            case 'oci':
            case 'oracle':
                $platform = 'Oracle';
                break;

            case 'sqlite':
                $platform = 'SQLite';
                break;
        }

        return $platform;
    }
}
