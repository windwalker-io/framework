<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Queue\Driver;

use Windwalker\Database\Driver\AbstractDatabaseDriver;
use Windwalker\DateTime\Chronos;
use Windwalker\Query\Query;
use Windwalker\Queue\QueueMessage;

/**
 * The DatabaseQueueDriver class.
 *
 * @since  3.2
 */
class DatabaseQueueDriver implements QueueDriverInterface
{
    /**
     * Property db.
     *
     * @var  AbstractDatabaseDriver
     */
    protected $db;

    /**
     * Property table.
     *
     * @var
     */
    protected $table;

    /**
     * Property queue.
     *
     * @var  string
     */
    protected $queue;

    /**
     * Property timeout.
     *
     * @var  int
     */
    protected $timeout;

    /**
     * DatabaseQueueDriver constructor.
     *
     * @param AbstractDatabaseDriver $db
     * @param string                 $queue
     * @param string                 $table
     * @param int                    $timeout
     */
    public function __construct(AbstractDatabaseDriver $db, $queue = 'default', $table = 'queue_jobs', $timeout = 60)
    {
        $this->db = $db;
        $this->table = $table;
        $this->queue = $queue;
        $this->timeout = $timeout;
    }

    /**
     * push
     *
     * @param QueueMessage $message
     *
     * @return int|string
     * @throws \Exception
     */
    public function push(QueueMessage $message)
    {
        $time = new \DateTimeImmutable('now');

        $data = [
            'queue' => $message->getQueueName() ?: $this->queue,
            'body' => json_encode($message),
            'attempts' => 0,
            'created' => $time->format('Y-m-d H:i:s'),
            'visibility' => $time->modify(sprintf('+%dseconds', $message->getDelay()))->format('Y-m-d H:i:s'),
            'reserved' => null,
        ];

        $this->db->getWriter()->insertOne($this->table, $data, 'id');

        return $data['id'];
    }

    /**
     * pop
     *
     * @param string $queue
     *
     * @return QueueMessage
     * @throws \Exception
     */
    public function pop($queue = null)
    {
        $queue = $queue ?: $this->queue;

        $now = new \DateTimeImmutable('now');

        $query = $this->db->getQuery(true);

        $query->select('*')
            ->from($query->quoteName($this->table))
            ->where('queue = %q', $queue)
            ->where('visibility <= %q', $now->format('Y-m-d H:i:s'))
            ->orWhere(
                function (Query $query) use ($now) {
                    $query->where('reserved IS NULL')
                        ->where('reserved < %q', $now->modify('-' . $this->timeout . 'seconds')->format('Y-m-d H:i:s'));
                }
            );

        $trans = $this->db->getTransaction()->start();

        try {
            $data = $this->db->setQuery($query . ' FOR UPDATE')->loadOne('assoc');

            if (!$data) {
                return null;
            }

            $data['attempts']++;

            $values = ['reserved' => $now->format('Y-m-d H:i:s'), 'attempts' => $data['attempts']];

            $this->db->getWriter()->updateBatch($this->table, $values, ['id' => $data['id']]);

            $trans->commit();
        } catch (\Throwable $t) {
            $trans->rollback();
        }

        $message = new QueueMessage();

        $message->setId($data['id']);
        $message->setAttempts($data['attempts']);
        $message->setBody(json_decode($data['body'], true));
        $message->setRawBody($data['body']);
        $message->setQueueName($queue);

        return $message;
    }

    /**
     * delete
     *
     * @param QueueMessage|string $message
     *
     * @return static
     */
    public function delete(QueueMessage $message)
    {
        $queue = $message->getQueueName() ?: $this->queue;

        $query = $this->db->getQuery(true);

        $query->delete($query->quoteName($this->table))
            ->where('id = :id')
            ->where('queue = :queue')
            ->bind('id', $message->getId())
            ->bind('queue', $queue);

        $this->db->setQuery($query)->execute();

        return $this;
    }

    /**
     * release
     *
     * @param QueueMessage|string $message
     *
     * @return static
     * @throws \Exception
     */
    public function release(QueueMessage $message)
    {
        $queue = $message->getQueueName() ?: $this->queue;

        $time = new \DateTimeImmutable('now');
        $time = $time->modify('+' . $message->getDelay() . 'seconds');

        $values = [
            'reserved' => null,
            'visibility' => $time->format('Y-m-d H:i:s'),
        ];

        $this->db->getWriter()->updateBatch(
            $this->table,
            $values,
            [
                'id' => $message->getId(),
                'queue' => $queue,
            ]
        );

        return $this;
    }

    /**
     * Method to get property Table
     *
     * @return  mixed
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Method to set property table
     *
     * @param   mixed $table
     *
     * @return  static  Return self to support chaining.
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Method to get property Db
     *
     * @return  AbstractDatabaseDriver
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * Method to set property db
     *
     * @param   AbstractDatabaseDriver $db
     *
     * @return  static  Return self to support chaining.
     */
    public function setDb($db)
    {
        $this->db = $db;

        return $this;
    }

    /**
     * Reconnect database to avoid long connect issues.
     *
     * @return  static
     */
    public function reconnect()
    {
        $this->db->disconnect();

        $this->db->connect();

        return $this;
    }
}
