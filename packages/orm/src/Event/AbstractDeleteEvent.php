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
        public int $options = 0,
        public ?object $entity = null,
        public array $data = []
    ) {
        parent::__construct(data: $data);
    }
}
