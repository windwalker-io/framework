<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2017 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Windwalker\Queue\Driver;

use Windwalker\DateTime\Chronos;
use Windwalker\Queue\QueueMessage;

/**
 * The PdoQueueDriver class.
 *
 * @since  3.3
 */
class PdoQueueDriver implements QueueDriverInterface
{
    /**
     * Property db.
     *
     * @var  \PDO
     */
    protected $pdo;
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
     * @param \PDO   $db
     * @param string $queue
     * @param string $table
     * @param int    $timeout
     */
    public function __construct(\PDO $db, $queue = 'default', $table = 'queue_jobs', $timeout = 60)
    {
        $this->pdo     = $db;
        $this->table   = $table;
        $this->queue   = $queue;
        $this->timeout = $timeout;
    }

    /**
     * push
     *
     * @param QueueMessage $message
     *
     * @return int|string
     */
    public function push(QueueMessage $message)
    {
        $time = new \DateTimeImmutable('now');

        $data = [
            ':queue' => $message->getQueueName() ?: $this->queue,
            ':body' => json_encode($message),
            ':attempts' => 0,
            ':created' => $time->format('Y-m-d H:i:s'),
            ':visibility' => $time->modify(sprintf('+%dseconds', $message->getDelay()))->format('Y-m-d H:i:s'),
            ':reserved' => null,
        ];

        $sql = 'INSERT INTO ' . $this->table .
            ' (queue, body, attempts, created, visibility, reserved)' .
            ' VALUES (:queue, :body, :attempts, :created, :visibility, :reserved)';

        $this->pdo->prepare($sql)->execute($data);

        return $this->pdo->lastInsertId();
    }

    /**
     * pop
     *
     * @param string $queue
     *
     * @return QueueMessage
     * @throws \Exception
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function pop($queue = null)
    {
        $queue = $queue ?: $this->queue;

        $now = new \DateTimeImmutable('now');

        $sql = 'SELECT * FROM ' . $this->table .
            ' WHERE queue = :queue AND visibility < :visibility' .
            ' AND (reserved IS NULL OR reserved < :reserved)' .
            ' FOR UPDATE';

        $this->pdo->beginTransaction();

        $stat = $this->pdo->prepare($sql);
        $stat->bindValue(':queue', $queue);
        $stat->bindValue(':visibility', $now->format('Y-m-d H:i:s'), \PDO::PARAM_STR);
        $stat->bindValue(':reserved', $now->modify('-' . $this->timeout . 'seconds')->format('Y-m-d H:i:s'), \PDO::PARAM_STR);

        try {
            $stat->execute();

            $data = $stat->fetch(\PDO::FETCH_ASSOC);

            if (!$data) {
                $this->pdo->commit();

                return null;
            }

            $data['attempts']++;

            $sql = 'UPDATE ' . $this->table . ' SET reserved = :reserved, attempts = :attempts WHERE id = :id';

            $stat = $this->pdo->prepare($sql);
            $stat->bindValue(':reserved', $now->format('Y-m-d H:i:s'));
            $stat->bindValue(':attempts', $data['attempts'] + 1);
            $stat->bindValue(':id', $data['id']);

            $stat->execute();

            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        } catch (\Throwable $t) {
            $this->pdo->rollBack();
            throw $t;
        }

        $message = new QueueMessage;

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

        $sql = 'DELETE FROM ' . $this->table .
            ' WHERE id = :id AND queue = :queue';

        $stat = $this->pdo->prepare($sql);
        $stat->bindValue(':id', $message->getId());
        $stat->bindValue(':queue', $queue);

        $stat->execute();

        return $this;
    }

    /**
     * release
     *
     * @param QueueMessage|string $message
     *
     * @return static
     */
    public function release(QueueMessage $message)
    {
        $queue = $message->getQueueName() ?: $this->queue;

        $time = new \DateTimeImmutable('now');
        $time = $time->modify('+' . $message->getDelay() . 'seconds');

        $values = [
            'id' => $message->getId(),
            'queue' => $queue,
            'reserved' => null,
            'visibility' => $time->format('Y-m-d H:i:s'),
        ];

        $sql = 'UPDATE ' . $this->table .
            ' SET reserved = :reserved, visibility = :visibility' .
            ' WHERE id = :id AND queue = :queue';

        $stat = $this->pdo->prepare($sql);

        $stat->execute($values);

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
     * @return  \PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * Method to set property db
     *
     * @param   \PDO $pdo
     *
     * @return  static  Return self to support chaining.
     */
    public function setPdo(\PDO $pdo)
    {
        $this->pdo = $pdo;

        return $this;
    }

    /**
     * Reconnect database to avoid long connect issues.
     *
     * @return  static
     */
    public function reconnect()
    {
        // PDO cannot reconnect.

        return $this;
    }
}
