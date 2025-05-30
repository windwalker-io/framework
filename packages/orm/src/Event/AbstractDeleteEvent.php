<?php

declare(strict_types=1);

namespace Windwalker\ORM\Event;

/**
 * The AbstractDeleteEvent class.
 */
abstract class AbstractDeleteEvent extends AbstractEntityEvent
{
    public function __construct(
        public mixed $conditions = null,
        public ?object $entity = null,
        public array $data = [],
        public int $options = 0,
    ) {
        parent::__construct(data: $data);
    }

    /**
     * @deprecated  Use property instead.
     */
    public function &getConditions(): mixed
    {
        return $this->conditions;
    }

    /**
     * @deprecated  Use property instead.
     */
    public function &getOptions(): int
    {
        return $this->options;
    }

}
