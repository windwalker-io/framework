<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\ORM\Relation\Strategy;

use ReflectionException;
use Windwalker\ORM\EntityMapper;
use Windwalker\ORM\Relation\Action;
use Windwalker\ORM\Relation\RelationCollection;
use Windwalker\ORM\Relation\RelationProxies;
use Windwalker\ORM\SelectorQuery;
use Windwalker\Utilities\Arr;

/**
 * The OneToMany class.
 */
class OneToMany extends AbstractRelation
{
    use HasManyTrait;

    /**
     * @inheritDoc
     */
    public function load(array $data, object $entity): array
    {
        $getter = fn() => $this->createCollection($data);

        RelationProxies::set($entity, $this->getPropName(), $getter);

        return $data;
    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function save(array $data, object $entity, ?array $oldData = null): void
    {
        if ($this->onUpdate === Action::IGNORE) {
            return;
        }

        [$attachEntities, $detachEntities, $keepEntities] = $this->diffRelated($data, $entity, $oldData);

        // Handle Attach
        if ($attachEntities) {
            $this->attachEntities($attachEntities, $data);
        }

        // Handle Detach
        if ($detachEntities) {
            $this->detachEntities($detachEntities, $oldData);
        }

        // Handle changed
        if ($this->isChanged($data, $oldData)) {
            if ($keepEntities === null) {
                $keepEntities = $this->createCollectionQuery($oldData);
            }

            $this->changeEntities($keepEntities, $data, $oldData);
        }
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
        $conditions = $this->syncValuesToForeign($data, []);

        $items = $this->getORM()
            ->from($this->getForeignMetadata()->getClassName())
            ->where($conditions);

        foreach ($items as $foreignEntity) {
            $delItem = $this->getORM()->extractEntity($foreignEntity);

            $delItem = $this->clearRelativeFields($delItem);

            $this->getORM()->updateOne(
                $this->getForeignMetadata()->getClassName(),
                $delItem,
                null,
                EntityMapper::UPDATE_NULLS
            );
        }
    }

    protected function createCollectionQuery(array $data): SelectorQuery
    {
        return $this->getORM()
            ->from($this->getTargetTable())
            ->where($this->createLoadConditions($data));
    }

    protected function createCollection(array $data): RelationCollection
    {
        return new RelationCollection(
            $this->getTargetTable(),
            $this->createCollectionQuery($data)
        );
    }

    protected function getDetachDiff(iterable $items, array $oldItems, array $compareKeys, array $ownerData): array
    {
        $keep = [];
        $detaches = [];

        foreach ($oldItems as $old) {
            $oldValues = Arr::only($old, $compareKeys);

            foreach ($items as $item) {
                // Check this old item has at-least 1 new item matched.
                if (Arr::arrayEquals($oldValues, Arr::only($item, $compareKeys))) {
                    $keep[] = $old;
                    continue 2;
                }
            }

            // If no matched, mark this old item to be delete.
            $detaches[] = $old;
        }

        return [$detaches, $keep];
    }

    protected function getAttachDiff(iterable $items, array $oldItems, array $compareKeys, array $ownerData): array
    {
        $keep = [];
        $creates = [];

        foreach ($items as $item) {
            $values = Arr::only($item, $compareKeys);

            foreach ($oldItems as $old) {
                // Check this new item has at-least 1 old item matched.
                if (Arr::arrayEquals(Arr::only($old, $compareKeys), $values)) {
                    $keep[] = $item;
                    continue 2;
                }
            }

            // If no matched, mark this new item to be create.
            $creates[] = $item;
        }

        return [$creates, $keep];
    }

    public function attachEntities(iterable $entities, array $data): void
    {
        foreach ($entities as $foreignEntity) {
            $foreignData = $this->getORM()->extractEntity($foreignEntity);
            $foreignData = $this->syncValuesToForeign($data, $foreignData);
            $foreignData = $this->mergeMorphValues($foreignData);

            $this->getORM()
                ->mapper($this->getTargetTable())
                ->saveOne($foreignData);
        }
    }

    public function detachEntities(iterable $entities, ?array $oldData): void
    {
        if ($oldData === null) {
            return;
        }

        foreach ($entities as $foreignEntity) {
            $foreignData = $this->getORM()->extractEntity($foreignEntity);

            $foreignData = $this->clearRelativeFields($foreignData);
            $this->getORM()
                ->mapper($this->getTargetTable())
                ->updateOne($foreignData, null, EntityMapper::UPDATE_NULLS);
        }
    }

    public function changeEntities(iterable $entities, array $data, ?array $oldData): void
    {
        if ($oldData === null) {
            return;
        }

        foreach ($entities as $keepEntity) {
            $keepData = $this->getORM()->extractEntity($keepEntity);

            $keepData = $this->handleUpdateRelations($data, $keepData);
            $keepData = $this->mergeMorphValues($keepData);

            $this->getORM()->updateOne(
                $this->getForeignMetadata()->getClassName(),
                $keepData,
                null,
                EntityMapper::UPDATE_NULLS
            );
        }
    }
}
