<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Attribute;
use Windwalker\ORM\Relation\Strategy\RelationConfigureInterface;

/**
 * The TargetTo class.
 */
#[Attribute]
class TargetTo implements RelationConfigureAttributeInterface
{
    public array $columns;

    /**
     * OneToOne constructor.
     *
     * @param  string  $target
     * @param  mixed   ...$columns
     */
    public function __construct(
        public string $target,
        ...$columns
    ) {
        $this->columns = $columns;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(RelationConfigureInterface $relation): void
    {
        $relation->targetTo($this->target, $this->columns);
    }
}
