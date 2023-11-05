<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Attribute;
use Windwalker\ORM\Relation\Strategy\RelationConfigureInterface;

/**
 * The MorphBy class.
 */
#[Attribute]
class MorphBy implements RelationConfigureAttributeInterface
{
    public array $columns;

    /**
     * MorphBy constructor.
     *
     * @param  mixed  ...$columns
     */
    public function __construct(...$columns)
    {
        $this->columns = $columns;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(RelationConfigureInterface $relation): void
    {
        $relation->morphBy(...$this->columns);
    }
}
