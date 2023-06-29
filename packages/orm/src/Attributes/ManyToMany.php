<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Attribute;
use ReflectionProperty;
use Windwalker\ORM\Relation\RelationManager;
use Windwalker\ORM\Relation\Strategy\RelationConfigureInterface;

use function React\Promise\map;

/**
 * The ManyToMany class.
 */
#[Attribute]
class ManyToMany extends AbstractRelationAttribute
{
    /**
     * @var RelationConfigureAttributeInterface[]
     */
    public array $attributes;

    /**
     * ManyToMany constructor.
     *
     * @param  RelationConfigureAttributeInterface  ...$attributes
     */
    public function __construct(RelationConfigureAttributeInterface ...$attributes)
    {
        foreach ($attributes as $attribute) {
            if ($attribute instanceof TargetTo) {
                parent::__construct($attribute->target, ...$attribute->columns);
            }
        }

        $this->attributes = $attributes;
    }

    protected function createRelation(RelationManager $rm, ReflectionProperty $prop): RelationConfigureInterface
    {
        $relation = $rm->manyToMany($prop->getName());

        foreach ($this->attributes as $attribute) {
            if ($attribute instanceof TargetTo) {
                $relation = $relation->targetTo($attribute->target, ...$attribute->columns);
            }

            if ($attribute instanceof MapBy) {
                $relation = $relation->mapBy($attribute->target, ...$attribute->columns);
            }

            if ($attribute instanceof MapMorphBy) {
                $relation = $relation->mapMorphBy(...$attribute->columns);
            }

            if ($attribute instanceof MorphBy) {
                $relation = $relation->morphBy(...$attribute->columns);
            }
        }

        return $relation;
    }
}
