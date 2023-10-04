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
use ReflectionProperty;
use Windwalker\ORM\Relation\RelationManager;
use Windwalker\ORM\Relation\Strategy\RelationConfigureInterface;

/**
 * The ManyToOne class.
 */
#[Attribute]
class ManyToOne extends AbstractRelationAttribute
{
    protected function createRelation(RelationManager $rm, ReflectionProperty $prop): RelationConfigureInterface
    {
        return $rm->manyToOne($prop->getName())->targetTo($this->target, ...$this->columns);
    }
}
