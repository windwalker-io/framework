<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Iterator;

use InvalidArgumentException;
use SplPriorityQueue;

/**
 * The PriorityQueue class.
 *
 * @since  2.1.1
 */
class PriorityQueue extends SplPriorityQueue
{
    public const MIN = -300;

    public const LOW = -200;

    public const BELOW_NORMAL = -100;

    public const NORMAL = 0;

    public const ABOVE_NORMAL = 100;

    public const HIGH = 200;

    public const MAX = 300;

    /**
     * @var int Seed used to ensure queue order for items of the same priority
     */
    protected int $serial = PHP_INT_MAX;

    /**
     * Class init.
     *
     * @param  array|SplPriorityQueue  $items
     * @param  int                     $priority
     */
    public function __construct(array|SplPriorityQueue $items = [], mixed $priority = self::NORMAL)
    {
        if ($items instanceof SplPriorityQueue) {
            $this->merge($items);
        } else {
            $this->join($items, $priority);
        }
    }

    /**
     * @param  array  $items
     * @param  int    $priority
     *
     * @return  static
     */
    public function join(iterable $items = [], mixed $priority = self::NORMAL): PriorityQueue
    {
        foreach ($items as $item) {
            $this->insert($item, $priority);
        }

        return $this;
    }

    /**
     * @param  array  $items
     *
     * @return  static
     */
    public function insertArray(array $items): PriorityQueue
    {
        foreach ($items as $priority => $item) {
            $this->insert($item, $priority);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function insert(mixed $value, mixed $priority): true
    {
        if (!is_array($priority)) {
            $priority = [$priority, $this->serial--];
        } else {
            $priority[] = $this->serial--;
        }

        return parent::insert($value, $priority);
    }

    /**
     * Serialize to an array
     *
     * Array will be priority => data pairs
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = [];

        foreach (clone $this as $item) {
            $array[] = $item;
        }

        return $array;
    }

    /**
     * Serialize
     *
     * @return array
     */
    public function __serialize(): array
    {
        $clone = clone $this;

        $clone->setExtractFlags(self::EXTR_BOTH);

        $data = [];

        foreach ($clone as $item) {
            $data[] = $item;
        }

        return $data;
    }

    /**
     * Deserialize
     *
     * @param  array  $data
     *
     * @return void
     */
    public function __unserialize(array $data): void
    {
        foreach ($data as $item) {
            $this->insert($item['data'], $item['priority']);
        }
    }

    /**
     * @return static;
     */
    public function merge(mixed ...$args): static
    {
        foreach ($args as $arg) {
            if (!($arg instanceof SplPriorityQueue)) {
                throw new InvalidArgumentException('Only \SplPriorityQueue can merge.');
            }

            $queue = clone $arg;

            $queue->setExtractFlags(self::EXTR_BOTH);

            foreach ($queue as $item) {
                $this->insert($item['data'], $item['priority']);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function compare(mixed $priority1, mixed $priority2): int
    {
        $p1Count = count($priority1);
        $p2Count = count($priority2);

        $count = min($p1Count, $p2Count);

        foreach (range(1, $count) as $i) {
            $k = $i - 1;

            if ($priority1[$k] == $priority2[$k]) {
                continue;
            }

            if ($priority1[$k] > $priority2[$k]) {
                return 1;
            }

            return -1;
        }

        return 0;
    }

    /**
     * Method to get property Serial
     *
     * @return  int
     */
    public function getSerial(): int
    {
        return $this->serial;
    }

    /**
     * Method to set property serial
     *
     * @param  int  $serial
     *
     * @return  static  Return self to support chaining.
     */
    public function setSerial(int $serial): PriorityQueue
    {
        $this->serial = $serial;

        return $this;
    }
}
