<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Event;

use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Event\AbstractEvent;

/**
 * The ItemFetchedEvent class.
 */
class ItemFetchedEvent extends AbstractEvent
{
    use QueryEventTrait;

    protected ?array $item;

    /**
     * @return object|null
     */
    public function &getItem(): ?array
    {
        return $this->item;
    }

    /**
     * @param  array|null  $item
     *
     * @return  static  Return self to support chaining.
     */
    public function setItem(?array $item): static
    {
        $this->item = $item;

        return $this;
    }
}
