<?php

declare(strict_types=1);

namespace Windwalker\Database\Driver;

use Windwalker\Pool\ConnectionInterface as PoolConnectionInterface;

/**
 * Interface ConnectionInterface
 */
interface ConnectionInterface extends PoolConnectionInterface
{
    /**
     * Get exists connection.
     *
     * @return mixed
     */
    public function get(): mixed;

    /**
     * @return string
     */
    public static function getName(): string;
}
