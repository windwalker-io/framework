<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Windwalker\ORM\Relation\Strategy\RelationConfigureInterface;

/**
 * Interface RelationAttributeInterface
 */
interface RelationConfigureAttributeInterface
{
    /**
     * __invoke
     *
     * @param  RelationConfigureInterface  $relation
     *
     * @return  void
     */
    public function __invoke(RelationConfigureInterface $relation): void;
}
