<?php

declare(strict_types=1);

namespace Windwalker\ORM\Relation\Strategy;

/**
 * The RelationStrategyInterface class.
 */
interface RelationStrategyInterface
{
    /**
     * Load all relative children data.
     *
     * @param  array   $data
     * @param  object  $entity
     *
     * @return array
     */
    public function load(array $data, object $entity): array;

    /**
     * Store all relative children data.
     *
     * The onUpdate option will work in this method.
     *
     * @param  array       $data
     * @param  object      $entity
     * @param  array|null  $oldData
     *
     * @return  void
     */
    public function save(array $data, object $entity, ?array $oldData = null): void;

    /**
     * Run before save, if there has child data, will reject operations.
     *
     * @param  array       $data
     * @param  object      $entity
     * @param  array|null  $oldData
     *
     * @return  void
     */
    public function beforeUpdate(array $data, object $entity, ?array $oldData = null): void;

    /**
     * Delete all relative children data.
     *
     * The onDelete option will work in this method.
     *
     * @param  array   $data
     * @param  object  $entity
     *
     * @return  void
     */
    public function delete(array $data, object $entity): void;

    /**
     * Run before delete, if there has child data, will reject operations.
     *
     * @param  array   $data
     * @param  object  $entity
     *
     * @return  void
     */
    public function beforeDelete(array $data, object $entity): void;
}
