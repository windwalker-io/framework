<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Relation\Strategy;

use ReflectionException;
use Windwalker\Data\Collection;
use Windwalker\ORM\Exception\RelationRejectException;
use Windwalker\ORM\Relation\Action;
use Windwalker\Utilities\Reflection\ReflectAccessor;

/**
 * The HaasManyTrait class.
 */
trait HasManyTrait
{
    /**
     * diffChildren
     *
     * @param  array       $data
     * @param  object      $entity
     * @param  array|null  $oldData
     *
     * @return array
     *
     * @throws ReflectionException
     */
    public function diffRelated(array $data, object $entity, ?array $oldData): array
    {
        $collection = ReflectAccessor::getValue($entity, $this->getPropName())
            ?? $this->createCollection($data);

        $foreignMetadata = $this->getForeignMetadata();

        $attachEntities = null;
        $detachEntities = null;
        $keepEntities = null;

        if ($collection->isSync()) {
            $entities = [];

            foreach ($collection->all(Collection::class) as $k => $item) {
                $entities[$k] = $this->getORM()->extractEntity($item);
            }

            if ($this->isFlush()) {
                // If is flush, let's delete all relations and make all attaches
                if ($oldData !== null) {
                    $this->deleteAllRelatives($oldData);
                }

                $attachEntities = $entities;
            } else {
                // If not flush let's make attach and detach diff
                $oldItems = $this->createCollection($oldData)
                    ->all(Collection::class)
                    ->dump(true);

                [$detachEntities,] = $this->getDetachDiff(
                    $entities,
                    $oldItems,
                    $foreignMetadata->getKeys(),
                    $data
                );
                [$attachEntities, $keepEntities] = $this->getAttachDiff(
                    $entities,
                    $oldItems,
                    $foreignMetadata->getKeys(),
                    $data
                );
            }
        } else {
            // Not sync, manually set attach/detach
            $attachEntities = $collection->getAttachedEntities();

            if ($this->isFlush()) {
                // If is flush, let's delete all relations and make all attaches
                if ($oldData !== null) {
                    $this->deleteAllRelatives($oldData);
                }
            } else {
                $detachEntities = $collection->getDetachedEntities();
            }
        }

        return [$attachEntities, $detachEntities, $keepEntities];
    }

    abstract public function attachEntities(iterable $entities, array $data): void;

    abstract public function detachEntities(iterable $entities, ?array $oldData): void;

    abstract public function changeEntities(iterable $entities, array $data, ?array $oldData): void;

    public function beforeUpdate(array $data, object $entity, ?array $oldData = null): void
    {
        if ($this->onDelete !== Action::RESTRICT) {
            return;
        }

        if ($this->isChanged($data, $oldData)) {
            $collection = ReflectAccessor::getValue($entity, $this->getPropName())
                ?? $this->createCollection($data);

            if ($collection->count()) {
                throw new RelationRejectException(
                    sprintf(
                        'Unable to update %s fields: %s because foreign data exists.',
                        $this->getMetadata()->getClassName(),
                        implode(', ', $this->getForeignKeys())
                    )
                );
            }
        }
    }

    public function beforeDelete(array $data, object $entity): void
    {
        if ($this->onDelete !== Action::RESTRICT) {
            return;
        }

        $collection = ReflectAccessor::getValue($entity, $this->getPropName())
            ?? $this->createCollection($data);

        if ($collection->count()) {
            throw new RelationRejectException(
                sprintf(
                    'Unable to delete %s because foreign data exists.',
                    $this->getMetadata()->getClassName()
                )
            );
        }
    }
}
