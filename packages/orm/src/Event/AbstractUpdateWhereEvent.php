<?php

declare(strict_types=1);

namespace Windwalker\ORM\Event;

/**
 * The AbstractUpdateBatchEvent class.
 */
abstract class AbstractUpdateWhereEvent extends AbstractEntityEvent
{
    public function __construct(
        public mixed $conditions,
        public int $options = 0,
        public array|object $source = [],
        public array $data = []
    ) {
        parent::__construct($data);
    }
}
