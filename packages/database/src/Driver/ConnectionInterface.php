<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Driver;

/**
 * Interface ConnectionInterface
 */
interface ConnectionInterface
{
    /**
     * connect
     *
     * @return  mixed
     */
    public function connect();

    /**
     * disconnect
     *
     * @return  mixed
     */
    public function disconnect();

    /**
     * isConnected
     *
     * @return  bool
     */
    public function isConnected(): bool;

    /**
     * @return mixed
     */
    public function get();

    /**
     * @return string
     */
    public static function getName(): string;
}
