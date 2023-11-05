<?php

declare(strict_types=1);

namespace Windwalker\ORM\Relation\Strategy;

use Windwalker\ORM\EntityMapper;
use Windwalker\ORM\Exception\RelationRejectException;
use Windwalker\ORM\Relation\Action;
use Windwalker\ORM\Relation\RelationProxies;
use Windwalker\Utilities\Reflection\ReflectAccessor;

/**
 * The OneToOne class.
 */
class OneToOne extends AbstractRelation
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
        if ($this->onUpdate === Action::IGNORE) {
            return;
        }

        // Get foreign entity
        $foreignData = $this->getForeignData($entity);

        if ($foreignData === null) {
            return;
        }

        $foreignData = $this->handleUpdateRelations($data, $foreignData);
        $foreignData = $this->mergeMorphValues($foreignData);

        if ($this->isFlush()) {
            $this->deleteAllRelatives($foreignData);
            $foreignData = $this->clearKeysValues($foreignData);
        }

        $this->getORM()
            ->mapper($this->getTargetTable())
            ->saveOne(
                $foreignData,
                null,
                EntityMapper::UPDATE_NULLS
            );
    }

    /**
     * @inheritDoc
     */
    public function delete(array $data, object $entity): void
    {
        if ($this->onDelete === Action::IGNORE) {
            return;
        }

        if ($this->onDelete === Action::CASCADE) {
            $this->deleteAllRelatives($data);

            return;
        }

        // SET NULL
        $foreignEntity = ReflectAccessor::getValue($entity, $this->getPropName())
            ?? RelationProxies::call($entity, $this->getPropName());

        if ($foreignEntity === null) {
            return;
        }

        $foreignData = $this->getORM()->extractEntity($foreignEntity);
        $foreignData = $this->clearRelativeFields($foreignData);

        $this->getORM()
            ->mapper($this->getTargetTable())
            ->updateOne(
                $foreignData,
                null,
                EntityMapper::UPDATE_NULLS
            );
    }

    public function beforeUpdate(array $data, object $entity, ?array $oldData = null): void
    {
        if ($this->onDelete !== Action::RESTRICT) {
            return;
        }

        $foreignData = $this->getForeignData($entity);

        if ($foreignData === null) {
            return;
        }

        if ($this->isForeignDataDifferent($data, $foreignData)) {
            throw new RelationRejectException(
                sprintf(
                    'Unable to update %s fields: %s because foreign data exists.',
                    $this->getMetadata()->getClassName(),
                    implode(', ', $this->getForeignKeys())
                )
            );
        }
    }

    public function beforeDelete(array $data, object $entity): void
    {
        if ($this->onDelete !== Action::RESTRICT) {
            return;
        }

        $foreignData = $this->getForeignData($entity);

        if ($foreignData === null) {
            return;
        }

        throw new RelationRejectException(
            sprintf(
                'Unable to delete %s because foreign data exists.',
                $this->getMetadata()->getClassName()
            )
        );
    }

    /**
     * getForeignData
     *
     * @param  object  $entity
     *
     * @return  array
     *
     * @throws \ReflectionException
     */
    protected function getForeignData(object $entity): ?array
    {
        $foreignEntity = ReflectAccessor::getValue($entity, $this->getPropName());

        // If no foreign entity exists but on update is CASCADE
        // try load it once.
        if ($foreignEntity === null && $this->onUpdate === Action::CASCADE) {
            $foreignEntity = RelationProxies::call($entity, $this->getPropName());
        }

        // If still no any relation found, return.
        if ($foreignEntity === null) {
            return null;
        }

        return $this->getORM()->extractEntity($foreignEntity);
    }
}
