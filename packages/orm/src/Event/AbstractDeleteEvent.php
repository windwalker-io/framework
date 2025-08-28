<?php

declare(strict_types=1);

namespace Windwalker\ORM\Event;

use Windwalker\ORM\ORMOptions;

/**
 * The AbstractDeleteEvent class.
 */
abstract class AbstractDeleteEvent extends AbstractEntityEvent
{
    public function __construct(
        public mixed $conditions = null,
        public ?object $entity = null,
        public array $data = [],
        public ORMOptions $options = new ORMOptions(),
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
    public function getOptions(): ORMOptions
    {
        return $this->options;
    }

}
