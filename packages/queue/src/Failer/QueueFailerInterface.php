<?php

declare(strict_types=1);

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
     * @param  string  $connection
     * @param  string  $channel
     * @param  string  $body
     * @param  string  $exception
     *
     * @return  int|string
     */
    public function add(string $connection, string $channel, string $body, string $exception): int|string;

    /**
     * all
     *
     * @return  iterable
     */
    public function all(): iterable;

    /**
     * get
     *
     * @param  mixed  $conditions
     *
     * @return array|null
     */
    public function get(mixed $conditions): ?array;

    /**
     * remove
     *
     * @param  mixed  $conditions
     *
     * @return  bool
     */
    public function remove(mixed $conditions): bool;

    /**
     * clear
     *
     * @return  bool
     */
    public function clear(): bool;
}
