<?php

declare(strict_types=1);

namespace Windwalker\Queue\Driver;

use DateTimeImmutable;
use Exception;
use InvalidArgumentException;
use PDO;
use Throwable;
use Windwalker\Queue\QueueMessage;

/**
 * The PdoQueueDriver class.
 *
 * @since  3.3
 */
class PdoQueueDriver implements QueueDriverInterface
{
    /**
     * DatabaseQueueDriver constructor.
     *
     * @param  PDO     $pdo
     * @param  string  $channel
     * @param  string  $table
     * @param  int     $timeout
     */
    public function __construct(
        protected PDO $pdo,
        protected string $channel = 'default',
        protected string $table = 'queue_jobs',
        protected int $timeout = 60
    ) {
    }

    /**
     * push
     *
     * @param  QueueMessage  $message
     *
     * @return string
     * @throws Exception
     */
    public function push(QueueMessage $message): string
    {
        $time = new DateTimeImmutable('now');

        $data = [
            ':channel' => $message->getChannel() ?: $this->channel,
            ':body' => json_encode($message),
            ':attempts' => 0,
            ':created' => $time->format('Y-m-d H:i:s'),
            ':visibility' => $time->modify(sprintf('+%dseconds', $message->getDelay()))->format('Y-m-d H:i:s'),
            ':reserved' => null,
        ];

        $sql = 'INSERT INTO ' . $this->table .
            ' (channel, body, attempts, created, visibility, reserved)' .
            ' VALUES (:channel, :body, :attempts, :created, :visibility, :reserved)';

        $this->pdo->prepare($sql)->execute($data);

        return (string) $this->pdo->lastInsertId();
    }

    /**
     * pop
     *
     * @param  string|null  $channel
     *
     * @return QueueMessage|null
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws Throwable
     */
    public function pop(?string $channel = null): ?QueueMessage
    {
        $channel = $channel ?: $this->channel;

        $now = new DateTimeImmutable('now');

        $sql = 'SELECT * FROM ' . $this->table .
            ' WHERE channel = :channel AND visibility <= :visibility' .
            ' AND (reserved IS NULL OR reserved < :reserved)' .
            ' FOR UPDATE';

        $this->pdo->beginTransaction();

        $stat = $this->pdo->prepare($sql);
        $stat->bindValue(':channel', $channel, PDO::PARAM_STR);
        $stat->bindValue(':visibility', $now->format('Y-m-d H:i:s'), PDO::PARAM_STR);
        $stat->bindValue(
            ':reserved',
            $now->modify('-' . $this->timeout . 'seconds')->format('Y-m-d H:i:s'),
            PDO::PARAM_STR
        );

        try {
            $stat->execute();

            $data = $stat->fetch(PDO::FETCH_ASSOC);

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
        } catch (Throwable $t) {
            $this->pdo->rollBack();
            throw $t;
        }

        $message = new QueueMessage();

        $message->setId($data['id']);
        $message->setAttempts((int) $data['attempts']);
        $message->setBody(json_decode($data['body'], true));
        $message->setRawBody($data['body']);
        $message->setChannel($channel);

        return $message;
    }

    /**
     * delete
     *
     * @param  QueueMessage  $message
     *
     * @return PdoQueueDriver
     */
    public function delete(QueueMessage $message): static
    {
        $channel = $message->getChannel() ?: $this->channel;

        $sql = 'DELETE FROM ' . $this->table .
            ' WHERE id = :id AND channel = :channel';

        $stat = $this->pdo->prepare($sql);
        $stat->bindValue(':id', $message->getId());
        $stat->bindValue(':channel', $channel);

        $stat->execute();

        return $this;
    }

    /**
     * @param  QueueMessage  $message
     *
     * @return static
     * @throws \DateMalformedStringException
     */
    public function release(QueueMessage $message): static
    {
        $channel = $message->getChannel() ?: $this->channel;

        $time = new DateTimeImmutable('now');
        $time = $time->modify('+' . $message->getDelay() . 'seconds');

        $values = [
            'id' => $message->getId(),
            'channel' => $channel,
            'reserved' => null,
            'visibility' => $time->format('Y-m-d H:i:s'),
            'attempts' => $message->getAttempts(),
        ];

        $sql = 'UPDATE ' . $this->table .
            ' SET reserved = :reserved, visibility = :visibility, attempts = :attempts' .
            ' WHERE id = :id AND channel = :channel';

        $stat = $this->pdo->prepare($sql);

        $stat->execute($values);

        return $this;
    }

    public function defer(QueueMessage $message): static
    {
        $this->delete($message);

        $message->setDeleted(false);
        $message->setId('');
        $message->setAttempts($message->getAttempts() - 1);

        $this->push($message);

        return $this;
    }

    /**
     * Method to get property Table
     *
     * @return  string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Method to set property table
     *
     * @param  string  $table
     *
     * @return  static  Return self to support chaining.
     */
    public function setTable(string $table): static
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Method to get property Db
     *
     * @return  PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Method to set property db
     *
     * @param  PDO  $pdo
     *
     * @return  static  Return self to support chaining.
     */
    public function setPdo(PDO $pdo): static
    {
        $this->pdo = $pdo;

        return $this;
    }

    /**
     * Reconnect database to avoid long connect issues.
     *
     * @return  static
     */
    public function reconnect(): static
    {
        // PDO cannot reconnect.

        return $this;
    }
}
