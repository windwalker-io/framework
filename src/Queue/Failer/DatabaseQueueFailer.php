<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Queue\Failer;

use Windwalker\Core\DateTime\Chronos;
use Windwalker\Database\Driver\AbstractDatabaseDriver;

/**
 * The DatabaseQueueFailer class.
 *
 * @since  3.2
 */
class DatabaseQueueFailer implements QueueFailerInterface
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
     * @var  string
     */
    protected $table;

    /**
     * DatabaseQueueFailer constructor.
     *
     * @param AbstractDatabaseDriver $db
     * @param string                 $table
     */
    public function __construct(AbstractDatabaseDriver $db, $table = 'queue_failed_jobs')
    {
        $this->db = $db;
        $this->table = $table;
    }

    /**
     * isSupported
     *
     * @return  bool
     */
    public function isSupported()
    {
        return $this->db->getTable($this->table)->exists();
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
        $data = get_defined_vars();

        // For B/C
        if (class_exists(Chronos::class)) {
            $data['created'] = Chronos::create('now')->toSql();
        } else {
            $data['created'] = (new \DateTime('now'))->format('Y-m-d H:i:s');
        }

        $this->db->getWriter()->insertOne($this->table, $data, 'id');

        return $data['id'];
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
        $query = $this->db->getQuery(true);

        $query->select('*')
            ->from($query->quoteName($this->table));

        return $this->db->setQuery($query)->loadAll(null, 'assoc');
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
        $query = $this->db->getQuery(true);

        $query->select('*')
            ->from($query->quoteName($this->table))
            ->where('id = :id')
            ->bind('id', $conditions);

        return $this->db->setQuery($query)->loadOne('assoc');
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
        $query = $this->db->getQuery(true);

        $query->delete($query->quoteName($this->table))
            ->where('id = :id')
            ->bind('id', $conditions);

        $this->db->setQuery($query)->execute();

        return true;
    }

    /**
     * clear
     *
     * @return  bool
     */
    public function clear()
    {
        $this->db->getTable($this->table)->truncate();

        return true;
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
