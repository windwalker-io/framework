<?php

declare(strict_types=1);

namespace Windwalker\ORM\Event;

use Windwalker\ORM\ORMOptions;

class AbstractCreateBulkEvent extends AbstractEntityEvent
{
    public function __construct(
        public mixed $conditions = null,
        public iterable $items = [],
        /** @var object[] $entities */
        public array $entities = [],
        array $data = [],
        public ORMOptions $options = new ORMOptions(),
    ) {
        parent::__construct($data);
    }
}
