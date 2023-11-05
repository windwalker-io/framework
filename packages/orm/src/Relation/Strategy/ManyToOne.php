<?php

declare(strict_types=1);

namespace Windwalker\ORM\Relation\Strategy;

use Windwalker\ORM\Relation\RelationProxies;

/**
 * The ManyToOne class.
 */
class ManyToOne extends AbstractRelation
{
    /**
     * @inheritDoc
     */
    public function load(array $data, object $entity): array
    {
        $getter = fn() => $this->getORM()
            ->findOne(
                $this->getTargetTable(),
                $this->createLoadConditions($data)
            );

        RelationProxies::set($entity, $this->getPropName(), $getter);

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function save(array $data, object $entity, ?array $oldData = null): void
    {
        // Many to one does not support save
    }

    /**
     * @inheritDoc
     */
    public function delete(array $data, object $entity): void
    {
        // Many to one does not support delete
    }

    public function beforeUpdate(array $data, object $entity, ?array $oldData = null): void
    {
        // Many to one does not support save
    }

    public function beforeDelete(array $data, object $entity): void
    {
        // Many to one does not support save
    }
}
