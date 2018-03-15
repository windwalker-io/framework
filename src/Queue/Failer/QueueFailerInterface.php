<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2017 $Asikart.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Queue\Failer;

/**
 * The QueueFailerInterface class.
 *
 * @since  3.2
 */
interface QueueFailerInterface
{
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
    public function add($connection, $queue, $body, $exception);

    /**
     * all
     *
     * @return  array
     */
    public function all();

    /**
     * get
     *
     * @param mixed $conditions
     *
     * @return  array
     */
    public function get($conditions);

    /**
     * remove
     *
     * @param mixed $conditions
     *
     * @return  bool
     */
    public function remove($conditions);

    /**
     * clear
     *
     * @return  bool
     */
    public function clear();
}
