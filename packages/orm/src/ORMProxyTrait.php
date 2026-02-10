<?php

declare(strict_types=1);

namespace Windwalker\ORM;

use Windwalker\Data\Collection;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Utilities\Wrapper\RawWrapper;

/**
 * @psalm-type Conditions = array|int|string|\Closure|null
 *
 * @internal
 */
trait ORMProxyTrait
{
    /**
     * @template C of Collection
     * @template T of C
     *
     * @param  class-string<T>       $entityClass
     * @param  class-string<C>|null  $className
     *
     * @return  T|null
     *
     * @throws \ReflectionException
     */
    public function findOne(
        string $entityClass,
        mixed $conditions = [],
        ?string $className = null,
        ORMOptions|int $options = new ORMOptions()
    ): ?object {
        return $this->mapper($entityClass)->findOne($conditions, $className, $options);
    }

    /**
     * @template C of Collection
     * @template T of C
     *
     * @param  class-string<T>       $entityClass
     * @param  Conditions            $conditions
     * @param  class-string<C>|null  $className
     *
     * @return  T
     *
     * @throws \ReflectionException
     */
    public function mustFindOne(
        string $entityClass,
        mixed $conditions = [],
        ?string $className = null,
        ORMOptions|int $options = new ORMOptions()
    ): object {
        return $this->mapper($entityClass)->mustFindOne($conditions, $className, $options);
    }

    /**
     * @template C of Collection
     * @template T of C
     *
     * @param  class-string<T>       $entityClass
     * @param  Conditions            $conditions
     * @param  class-string<C>|null  $className
     *
     * @return  iterable<T>
     *
     * @throws \ReflectionException
     */
    public function findList(
        string $entityClass,
        mixed $conditions = [],
        ?string $className = null,
        ORMOptions|int $options = new ORMOptions()
    ): Iterator\ResultIterator {
        return $this->mapper($entityClass)->findList($conditions, $className, $options);
    }

    /**
     * @param  string             $entityClass
     * @param  string|RawWrapper  $column
     * @param  Conditions         $conditions
     * @param  ORMOptions|int     $options
     *
     * @return  mixed
     *
     * @throws \ReflectionException
     */
    public function findResult(
        string $entityClass,
        string|RawWrapper $column,
        mixed $conditions = [],
        ORMOptions|int $options = new ORMOptions()
    ): mixed {
        return $this->mapper($entityClass)->findResult($column, $conditions, $options);
    }

    /**
     * @param  string          $entityClass
     * @param  string          $column
     * @param  Conditions      $conditions
     * @param  ORMOptions|int  $options
     *
     * @return  Collection
     *
     * @throws \ReflectionException
     */
    public function findColumn(
        string $entityClass,
        string $column,
        mixed $conditions = [],
        ORMOptions|int $options = new ORMOptions()
    ): Collection {
        return $this->mapper($entityClass)->findColumn($column, $conditions, $options);
    }

    /**
     * @param  string             $entityClass
     * @param  string             $column
     * @param  Conditions         $conditions
     * @param  array|string|null  $groups
     *
     * @return  int
     *
     * @throws \ReflectionException
     */
    public function countColumn(
        string $entityClass,
        string $column,
        mixed $conditions = [],
        array|string|null $groups = null
    ): int {
        return $this->mapper($entityClass)->countColumn($column, $conditions, $groups);
    }

    /**
     * @param  string             $entityClass
     * @param  string             $column
     * @param  Conditions         $conditions
     * @param  array|string|null  $groups
     *
     * @return  float
     *
     * @throws \ReflectionException
     */
    public function sumColumn(
        string $entityClass,
        string $column,
        mixed $conditions = [],
        array|string|null $groups = null
    ): float {
        return $this->mapper($entityClass)->sumColumn($column, $conditions, $groups);
    }

    /**
     * @template T of object
     *
     * @param  class-string<T>|T  $entityClass
     * @param  array|object       $item
     * @param  ORMOptions|int     $options
     *
     * @return  T
     *
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function createOne(
        string|object $entityClass,
        array|object $item = [],
        ORMOptions|int $options = new ORMOptions()
    ): object {
        if (is_object($entityClass)) {
            $item = $entityClass;
            $entityClass = $entityClass::class;
        }

        return $this->mapper($entityClass)->createOne($item, $options);
    }

    /**
     * @template T
     *
     * @param  string          $entityClass
     * @param  iterable        $items
     * @param  ORMOptions|int  $options
     *
     * @return  array<T>
     *
     * @throws \ReflectionException
     */
    public function createBulk(string $entityClass, iterable $items, ORMOptions|int $options = new ORMOptions()): array
    {
        return $this->mapper($entityClass)->createBulk($items, $options);
    }

    /**
     * @template L of iterable
     * @template T
     *
     * @param  class-string<T>  $entityClass
     * @param  L<T>             $items
     * @param  ORMOptions|int   $options
     *
     * @return  iterable<T>|L<T>
     *
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function createMultiple(
        string $entityClass,
        iterable $items = [],
        ORMOptions|int $options = new ORMOptions()
    ): iterable {
        return $this->mapper($entityClass)->createMultiple($items, $options);
    }

    public function updateOne(
        string|object $entityClass,
        array|object $item = [],
        array|string|null $condFields = null,
        ORMOptions|int $options = new ORMOptions()
    ): ?StatementInterface {
        if (is_object($entityClass)) {
            $item = $entityClass;
            $entityClass = $entityClass::class;
        }

        return $this->mapper($entityClass)->updateOne($item, $condFields, $options);
    }

    public function updateMultiple(
        string|object $entityClass,
        iterable $items = [],
        array|string|null $condFields = null,
        ORMOptions|int $options = new ORMOptions()
    ): array {
        return $this->mapper($entityClass)->updateMultiple($items, $condFields, $options);
    }

    /**
     * @deprecated  Use updateBulk() instead.
     */
    public function updateWhere(
        string $entityClass,
        array|object $data,
        mixed $conditions = null,
        ORMOptions|int $options = new ORMOptions()
    ): StatementInterface {
        return $this->updateBulk($entityClass, $data, $conditions, $options);
    }

    /**
     * Update items in single SQL and not trigger events, be careful when using this.
     *
     * @param  string          $entityClass
     * @param  array|object    $data
     * @param  mixed|null      $conditions
     * @param  ORMOptions|int  $options
     *
     * @return  StatementInterface
     *
     * @throws \ReflectionException
     */
    public function updateBulk(
        string $entityClass,
        array|object $data,
        mixed $conditions = null,
        ORMOptions|int $options = new ORMOptions()
    ): ?StatementInterface {
        return $this->mapper($entityClass)->updateBulk($data, $conditions, $options);
    }

    /**
     * Find items and update them one by one to trigger events.
     *
     * @param  string          $entityClass
     * @param  array|object    $data
     * @param  mixed|null      $conditions
     * @param  ORMOptions|int  $options
     *
     * @return  array
     *
     * @throws \ReflectionException
     */
    public function updateBatch(
        string $entityClass,
        array|object $data,
        mixed $conditions = null,
        ORMOptions|int $options = new ORMOptions()
    ): array {
        return $this->mapper($entityClass)->updateBatch($data, $conditions, $options);
    }

    /**
     * @template L of iterable
     * @template T
     *
     * @param  class-string<T>|T  $entityClass
     * @param  L<T>               $items
     * @param  ORMOptions|int     $options
     *
     * @return  iterable<T>|L<T>
     *
     * @throws \ReflectionException
     */
    public function saveMultiple(
        string|object $entityClass,
        iterable $items = [],
        string|array|null $condFields = null,
        ORMOptions|int $options = new ORMOptions()
    ): iterable {
        return $this->mapper($entityClass)->saveMultiple($items, $condFields, $options);
    }

    /**
     * @template T of object
     *
     * @param  class-string<T>|T  $entityClass
     *
     * @return  T
     *
     * @throws \ReflectionException
     */
    public function saveOne(
        string|object $entityClass,
        array|object $item = [],
        array|string|null $condFields = null,
        ORMOptions|int $options = new ORMOptions()
    ): object {
        if (is_object($entityClass)) {
            $item = $entityClass;
            $entityClass = $entityClass::class;
        }

        return $this->mapper($entityClass)->saveOne($item, $condFields, $options);
    }

    /**
     * @template T of object
     *
     * @param  class-string<T>  $entityClass
     * @param  Conditions       $conditions
     *
     * @return  T
     *
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function findOneOrCreate(
        string $entityClass,
        mixed $conditions,
        mixed $initData = null,
        bool $mergeConditions = true,
        ORMOptions|int $options = new ORMOptions()
    ): object {
        return $this->mapper($entityClass)->findOneOrCreate($conditions, $initData, $mergeConditions, $options);
    }

    /**
     * @template T of object
     *
     * @param  class-string<T>  $entityClass
     *
     * @return  T
     *
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function updateOneOrCreate(
        string $entityClass,
        array|object $item,
        mixed $initData = null,
        ?array $condFields = null,
        ORMOptions|int $options = new ORMOptions()
    ): object {
        return $this->mapper($entityClass)->updateOneOrCreate($item, $initData, $condFields, $options);
    }

    /**
     * Delete items in single SQL and not trigger events, be careful when using this.
     *
     * @param  string          $entityClass
     * @param  mixed           $conditions
     * @param  ORMOptions|int  $options
     *
     * @return  StatementInterface
     *
     * @throws \ReflectionException
     */
    public function deleteBulk(
        string $entityClass,
        mixed $conditions,
        ORMOptions|int $options = new ORMOptions()
    ): StatementInterface {
        return $this->mapper($entityClass)->deleteBulk($conditions, $options);
    }

    /**
     * @deprecated  Use deleteBatch() instead.
     */
    public function deleteWhere(
        string $entityClass,
        mixed $conditions,
        ORMOptions|int $options = new ORMOptions()
    ): void {
        $this->deleteBatch($entityClass, $conditions, $options);
    }

    public function deleteOne(string $entityClass, mixed $conditions, ORMOptions|int $options = new ORMOptions()): void
    {
        $this->mapper($entityClass)->deleteOne($conditions, $options);
    }

    /**
     * Find items and delete them one by one to trigger events.
     *
     * @param  string          $entityClass
     * @param  mixed           $conditions
     * @param  ORMOptions|int  $options
     *
     * @return  void
     *
     * @throws \ReflectionException
     */
    public function deleteBatch(
        string $entityClass,
        mixed $conditions,
        ORMOptions|int $options = new ORMOptions()
    ): void {
        $this->mapper($entityClass)->deleteBatch($conditions, $options);
    }

    /**
     * @template T
     *
     * @param  class-string<T>  $entityClass
     *
     * @return  iterable<T>
     *
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function flush(
        string $entityClass,
        iterable $items,
        mixed $conditions = [],
        ORMOptions|int $options = new ORMOptions()
    ): iterable {
        return $this->mapper($entityClass)->flush($items, $conditions, $options);
    }

    /**
     * @template T
     *
     * @param  class-string<T>  $entityClass
     * @param  Conditions       $conditions
     *
     * @return  array{ array<T>, array<T>, array<T> }
     *
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function sync(
        string $entityClass,
        iterable $items,
        mixed $conditions = [],
        ?array $compareKeys = null,
        ORMOptions|int $options = new ORMOptions()
    ): array {
        return $this->mapper($entityClass)->sync($items, $conditions, $compareKeys, $options);
    }

    /**
     * @template T
     *
     * @param  class-string<T>  $entityClass
     * @param  Conditions       $conditions
     *
     * @return  array<T>
     *
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function copy(
        string $entityClass,
        mixed $conditions = [],
        callable|iterable|null $newValue = null,
        ORMOptions|int $options = new ORMOptions()
    ): array {
        return $this->mapper($entityClass)->copy($conditions, $newValue, $options);
    }

    public function increment(
        string $entityClass,
        string|array $fields,
        mixed $conditions,
        int|float $num = 1,
        ORMOptions|int $options = new ORMOptions()
    ): void {
        $this->mapper($entityClass)->increment($fields, $conditions, $num, $options);
    }

    public function decrement(
        string $entityClass,
        string|array $fields,
        mixed $conditions,
        int|float $num = 1,
        ORMOptions|int $options = new ORMOptions()
    ): void {
        $this->mapper($entityClass)->decrement($fields, $conditions, $num, $options);
    }

    public function incrementOrCreate(
        string $entityClass,
        string|array $fields,
        mixed $conditions,
        int|float $num = 1,
        mixed $initData = null,
        ORMOptions|int $options = new ORMOptions(),
    ): void {
        $this->mapper($entityClass)->incrementOrCreate($fields, $conditions, $num, $initData, $options);
    }

    public function decrementOrCreate(
        string $entityClass,
        string|array $fields,
        mixed $conditions,
        int|float $num = 1,
        mixed $initData = null,
        ORMOptions|int $options = new ORMOptions(),
    ): void {
        $this->mapper($entityClass)->decrementOrCreate($fields, $conditions, $num, $initData, $options);
    }

    /**
     * @template T of object
     *
     * @param  T  $entity
     *
     * @return  T
     *
     * @throws \ReflectionException
     */
    public function pushNextVersion(object $entity): object
    {
        return $this->mapper($entity::class)->pushNextVersion($entity);
    }
}
