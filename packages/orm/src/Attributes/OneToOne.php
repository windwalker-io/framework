<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Attribute;
use ReflectionProperty;
use Windwalker\ORM\Relation\RelationManager;
use Windwalker\ORM\Relation\Strategy\RelationConfigureInterface;

/**
 * The OneToOne class.
 */
#[Attribute]
class OneToOne extends AbstractRelationAttribute
{
    protected function createRelation(RelationManager $rm, ReflectionProperty $prop): RelationConfigureInterface
    {
        return $rm->oneToOne($prop->getName())
            ->targetTo($this->target, ...$this->columns);
    }
}
