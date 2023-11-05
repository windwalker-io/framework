<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Attribute;
use ReflectionProperty;
use Windwalker\ORM\Relation\RelationManager;
use Windwalker\ORM\Relation\Strategy\RelationConfigureInterface;

/**
 * The OneToMany class.
 */
#[Attribute]
class OneToMany extends AbstractRelationAttribute
{
    protected function createRelation(RelationManager $rm, ReflectionProperty $prop): RelationConfigureInterface
    {
        return $rm->oneToMany($prop->getName())->targetTo($this->target, ...$this->columns);
    }
}
