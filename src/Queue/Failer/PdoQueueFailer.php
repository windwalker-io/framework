<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2017 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Windwalker\Queue\Failer;

use Windwalker\Core\DateTime\Chronos;

/**
 * The DatabaseQueueFailer class.
 *
 * @since  3.3
 */
class PdoQueueFailer implements QueueFailerInterface
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
     * @var  string
     */
    protected $table;

    /**
     * DatabaseQueueFailer constructor.
     *
     * @param \PDO   $pdo
     * @param string $table
     */
    public function __construct(\PDO $pdo, $table = 'queue_failed_jobs')
    {
        $this->pdo   = $pdo;
        $this->table = $table;
    }

    /**
     * isSupported
     *
     * @return  bool
     */
    public function isSupported()
    {
        $sql = 'SHOW TABLES LIKE :table';

        $stat = $this->pdo->prepare($sql);
        $stat->bindValue(':table', $this->table);
        $stat->execute();

        return (bool) $stat->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * add
     *
     * @param string $connection
     * @param string $queue
     * @param string $body
     * @param string $exception
     *
     * @return  int|string
     */
    public function add($connection, $queue, $body, $exception)
    {
        // For B/C
        $created = (new \DateTime('now'))->format('Y-m-d H:i:s');

        $sql = 'INSERT INTO ' . $this->table .
            ' (connection, queue, body, exception, created)' .
            ' VALUES (:connection, :queue, :body, :exception, :created)';

        $stat = $this->pdo->prepare($sql);
        $stat->bindValue(':connection', $connection);
        $stat->bindValue(':queue', $queue);
        $stat->bindValue(':body', $body);
        $stat->bindValue(':exception', $exception);
        $stat->bindValue(':created', $created);

        $stat->execute();

        return $this->pdo->lastInsertId();
    }

    /**
     * all
     *
     * @return  array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function all()
    {
        $sql = 'SELECT * FROM ' . $this->table;

        $stat = $this->pdo->query($sql);

        return $stat->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * get
     *
     * @param mixed $conditions
     *
     * @return  array
     */
    public function get($conditions)
    {
        $sql = 'SELECT * FROM ' . $this->table .
            ' WHERE id = :id';

        $stat = $this->pdo->prepare($sql);
        $stat->bindValue(':id', $conditions);
        $stat->execute();

        return $stat->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * remove
     *
     * @param mixed $conditions
     *
     * @return  bool
     */
    public function remove($conditions)
    {
        $sql = 'DELETE FROM ' . $this->table .
            ' WHERE id = :id';

        $stat = $this->pdo->prepare($sql);
        $stat->bindValue(':id', $conditions);

        return $stat->execute();
    }

    /**
     * clear
     *
     * @return  bool
     */
    public function clear()
    {
        $sql = 'TRUNCATE TABLE ' . $this->table;

        return $this->pdo->prepare($sql)->execute();
    }

    /**
     * Method to get property Table
     *
     * @return  string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Method to set property table
     *
     * @param   string $table
     *
     * @return  static  Return self to support chaining.
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }
}
