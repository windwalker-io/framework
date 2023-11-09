<?php

declare(strict_types=1);

namespace Windwalker\ORM;

use DateTimeInterface;
use InvalidArgumentException;
use JsonException;
use LogicException;
use ReflectionAttribute;
use ReflectionProperty;
use Windwalker\Attributes\AttributesAccessor;
use Windwalker\Data\Collection;
use Windwalker\Database\DatabaseAdapter;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Database\Schema\Ddl\Column as DbColumn;
use Windwalker\Event\Event;
use Windwalker\Event\EventAwareInterface;
use Windwalker\Event\EventAwareTrait;
use Windwalker\Event\EventInterface;
use Windwalker\ORM\Attributes\CastForSave;
use Windwalker\ORM\Event\{AbstractSaveEvent,
    AfterCopyEvent,
    AfterDeleteEvent,
    AfterSaveEvent,
    AfterUpdateWhereEvent,
    BeforeCopyEvent,
    BeforeDeleteEvent,
    BeforeSaveEvent,
    BeforeStoreEvent,
    BeforeUpdateWhereEvent};
use Windwalker\ORM\Hydrator\EntityHydrator;
use Windwalker\ORM\Iterator\ResultIterator;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\Query\Clause\ClauseInterface;
use Windwalker\Query\Exception\NoResultException;
use Windwalker\Query\Query;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Assert\TypeAssert;
use Windwalker\Utilities\Reflection\ReflectAccessor;
use Windwalker\Utilities\TypeCast;
use Windwalker\Utilities\Wrapper\RawWrapper;

use function is_object;
use function Windwalker\collect;
use function Windwalker\raw;

/**
 * EntityMapper is an entity & database mapping object.
 *
 * Similar to DataMapper pattern.
 *
 * @template T
 *
 * @psalm-type Conditions = array|int|string|\Closure|null
 */
class EntityMapper implements EventAwareInterface
{
    use EventAwareTrait;
    use EntityMapperConstantsTrait;

    /**
     * @var ORM
     */
    protected ORM $orm;

    /**
     * @var EntityMetadata
     */
    protected EntityMetadata $metadata;

    /**
     * EntityManager constructor.
     *
     * @param  EntityMetadata  $metadata
     * @param  ORM             $orm
     */
    public function __construct(EntityMetadata $metadata, ORM $orm)
    {
        $this->orm = $orm;
        $this->metadata = $metadata;

        $this->init();
    }

    protected function init(): void
    {
        //
    }

    /**
     * Create Query with select from.
     *
     * @param  mixed        $tables
     * @param  string|null  $alias
     *
     * @return  SelectorQuery
     */
    public function from(mixed $tables, ?string $alias = null): SelectorQuery
    {
        return $this->createSelectorQuery()->from($tables, $alias);
    }

    /**
     * Create Query with select.
     *
     * Use `select('...')` to select columns.
     * Use `select()` to create select query without any settings.
     *
     * @param  mixed  ...$columns
     *
     * @return  SelectorQuery
     */
    public function select(...$columns): SelectorQuery
    {
        return $this->createSelectorQuery()
            ->from($this->getMetadata()->getClassName())
            ->select(...$columns);
    }

    public function insert(bool $incrementField = false): SelectorQuery
    {
        return $this->createSelectorQuery()->insert($this->getMetadata()->getClassName(), $incrementField);
    }

    public function update(?string $alias = null): SelectorQuery
    {
        return $this->createSelectorQuery()->update($this->getMetadata()->getClassName(), $alias);
    }

    public function delete(?string $alias = null): SelectorQuery
    {
        return $this->createSelectorQuery()->delete($this->getMetadata()->getClassName(), $alias);
    }

    /**
     * Create Selector query.
     *
     * @return  SelectorQuery
     */
    public function createSelectorQuery(): SelectorQuery
    {
        $selector = new SelectorQuery($this->getORM());

        $selector->getEventDispatcher()->addDealer($this->getEventDispatcher());

        return $selector;
    }

    /**
     * findOne
     *
     * @param  Conditions        $conditions
     * @param  ?class-string<T>  $className
     *
     * @return  object|null|T
     */
    public function findOne(mixed $conditions = [], ?string $className = null): ?object
    {
        $metadata = $this->getMetadata();

        return $this->from($metadata->getClassName())
            ->where($this->conditionsToWheres($conditions))
            ->get($className ?? $metadata->getClassName());
    }

    /**
     * @param  Conditions        $conditions
     * @param  ?class-string<T>  $className
     *
     * @return  object|T
     */
    public function mustFindOne(mixed $conditions = [], ?string $className = null): object
    {
        if (!$item = $this->findOne($conditions, $className)) {
            throw new NoResultException($this->getTableName(), $conditions);
        }

        return $item;
    }

    /**
     * @param  Conditions            $conditions
     * @param  class-string<T>|null  $className
     *
     * @return  ResultIterator<T>
     */
    public function findList(mixed $conditions = [], ?string $className = null): ResultIterator
    {
        $metadata = $this->getMetadata();

        return new ResultIterator(
            $this->select()
                ->where($this->conditionsToWheres($conditions))
                ->getIterator($className ?? $metadata->getClassName())
        );
    }

    /**
     * @param  string|RawWrapper  $column
     * @param  Conditions         $conditions
     *
     * @return  string|null
     */
    public function findResult(string|RawWrapper $column, mixed $conditions = []): ?string
    {
        return $this->select($column)
            ->where($this->conditionsToWheres($conditions))
            ->result();
    }

    /**
     * @param  string      $column
     * @param  Conditions  $conditions
     *
     * @return  Collection
     */
    public function findColumn(string $column, mixed $conditions = []): Collection
    {
        return $this->select($column)
            ->where($this->conditionsToWheres($conditions))
            ->loadColumn();
    }

    /**
     * @param  string             $column
     * @param  Conditions         $conditions
     * @param  array|string|null  $groups
     *
     * @return  int
     */
    public function countColumn(string $column, mixed $conditions = [], array|string $groups = null): int
    {
        return (int) $this->select()
            ->selectRaw('IFNULL(COUNT(%n), 0)', $column)
            ->where($this->conditionsToWheres($conditions))
            ->tapIf(
                $groups !== null,
                fn(Query $query) => $query->group($groups)
            )
            ->result();
    }

    /**
     * @param  string             $column
     * @param  Conditions         $conditions
     * @param  array|string|null  $groups
     *
     * @return  float
     */
    public function sumColumn(string $column, mixed $conditions = [], array|string $groups = null): float
    {
        return (float) $this->select()
            ->selectRaw('IFNULL(SUM(%n), 0)', $column)
            ->where($this->conditionsToWheres($conditions))
            ->tapIf(
                $groups !== null,
                fn(Query $query) => $query->group($groups)
            )
            ->result();
    }

    /**
     * createOne
     *
     * @param  array|object  $source
     * @param  int           $options
     *
     * @return  object|T
     *
     * @throws JsonException
     * @throws \ReflectionException
     */
    public function createOne(array|object $source = [], int $options = 0): object
    {
        $pk = $this->getMainKey();
        $metadata = $this->getMetadata();
        $aiColumn = $this->getAutoIncrementColumn();
        // $className = $metadata->getClassName();

        TypeAssert::assert(
            is_object($source) || is_array($source),
            '{caller} item must be array or object, {value} given',
            $source
        );

        $data = $this->extract($source);

        // Keep data field same as source, that developers can know what data has sent.
        $type = AbstractSaveEvent::TYPE_CREATE;
        $event = $this->emitEvent(
            BeforeSaveEvent::class,
            compact('data', 'type', 'metadata', 'source', 'options')
        );

        // Hydrate data into entity after event, to make sure all fields has default value.
        $fullData = $event->getData();
        $entity = $this->hydrate($fullData, $this->toEntity($source));

        $data = $this->castForSave($this->extract($entity), true, $entity);

        $type = BeforeStoreEvent::TYPE_CREATE;
        $event = $this->emitEvent(
            BeforeStoreEvent::class,
            compact('data', 'type', 'metadata', 'source', 'options')
        );

        $data = $event->getData();

        if ($aiColumn && array_key_exists($aiColumn, $data) && !$data[$aiColumn]) {
            unset($data[$aiColumn]);
        }

        $data = $this->getDb()->getWriter()->insertOne(
            $metadata->getTableName(),
            $data,
            $pk,
            [
                'incrementField' => $aiColumn && !empty($data[$aiColumn]),
            ]
        );

        if ($pk && isset($data[$pk])) {
            $fullData[$pk] = $data[$pk];

            $entity = $this->hydrate(
                [$pk => $data[$pk]],
                $entity
            );
        }

        $event = $this->emitEvent(
            AfterSaveEvent::class,
            compact('data', 'type', 'metadata', 'entity', 'source', 'fullData', 'options')
        );

        $entity = $this->hydrate(
            $event->getData(),
            $event->getEntity()
        );

        $metadata->getRelationManager()->save($event->getData(), $entity);

        return $entity;
    }

    /**
     * @param  iterable  $items
     * @param  int       $options
     *
     * @return  iterable<T>
     */
    public function createMultiple(iterable $items, int $options = 0): iterable
    {
        /** @var array|object $item */
        foreach ($items as $k => $item) {
            $items[$k] = $this->createOne($item, $options);
        }

        return $items;
    }

    public function updateOne(
        array|object $source = [],
        array|string $condFields = null,
        int $options = 0,
    ): ?StatementInterface {
        $metadata = $this->getMetadata();
        $updateNulls = (bool) ($options & static::UPDATE_NULLS);

        if (!$condFields) {
            $condFields = $this->getKeys();
        }

        if (!$condFields) {
            throw new InvalidArgumentException(
                'Condition fields empty or Entity has no keys when updating data.'
            );
        }

        TypeAssert::assert(
            is_object($source) || is_array($source),
            '{caller} item must be array or object, {value} given',
            $source
        );

        $data = $this->extract($source);

        // Get old data
        $oldData = null;

        if (!($options & static::IGNORE_OLD_DATA) && $this->getKeys() && !empty($data[$this->getMainKey()])) {
            $oldData = $this->getDb()->select('*')
                ->from($metadata->getTableName())
                ->where(Arr::only($data, $this->getKeys()))
                ->get()
                ?->dump();

            $data = array_merge($oldData ?? [], $data);
        }

        $type = AbstractSaveEvent::TYPE_UPDATE;
        $event = $this->emitEvent(
            BeforeSaveEvent::class,
            compact('data', 'type', 'metadata', 'oldData', 'source', 'options')
        );

        // Hydrate data into entity after event, to make sure all fields has default value.
        $fullData = $event->getData();
        $entity = $this->hydrate($fullData, $this->toEntity($source));

        $data = $this->castForSave($this->extract($entity), $updateNulls, $entity);

        $metadata = $event->getMetadata();

        $writeData = $data;

        $keyValues = Arr::only($writeData, (array) $condFields);

        // Only compare if write data use full keys
        if ($oldData !== null && count(array_intersect_key($writeData, $oldData)) === count($oldData)) {
            $writeData = array_diff_assoc($writeData, $oldData);
        }

        if ($writeData !== []) {
            $writeData = array_merge($keyValues, $writeData);

            $metadata->getRelationManager()->beforeUpdate($writeData, $entity, $oldData);

            $type = BeforeStoreEvent::TYPE_UPDATE;
            $event = $this->emitEvent(
                BeforeStoreEvent::class,
                [
                    'data' => $writeData,
                    'type' => $type,
                    'metadata' => $metadata,
                    'source' => $source,
                    'options' => $options,
                ]
            );

            $result = $this->getDb()->getWriter()->updateOne(
                $metadata->getTableName(),
                $event->getData(),
                $condFields,
                [
                    'updateNulls' => $updateNulls,
                ]
            );
        }

        $event = $this->emitEvent(
            AfterSaveEvent::class,
            compact('data', 'type', 'metadata', 'entity', 'oldData', 'source', 'options', 'fullData')
        );

        $metadata->getRelationManager()->save($event->getData(), $entity, $oldData);

        // Event

        return $result ?? null;
    }

    /**
     * updateMultiple
     *
     * @param  iterable           $items
     * @param  array|string|null  $condFields
     * @param  int                $options
     *
     * @return  StatementInterface[]
     */
    public function updateMultiple(iterable $items, array|string $condFields = null, int $options = 0): array
    {
        $results = [];

        foreach ($items as $k => $item) {
            $results[$k] = $this->updateOne($item, $condFields, $options);
        }

        // Event

        return $results;
    }

    /**
     * Using one data to update multiple rows, filter by where conditions.
     * Example:
     * `$mapper->updateWhere(new Data(array('published' => 0)), array('date' => '2014-03-02'))`
     * Means we make every records which date is 2014-03-02 unpublished.
     *
     * @param  mixed       $source      The data we want to update to every rows.
     * @param  Conditions  $conditions  Where conditions, you can use array or Compare object.
     * @param  int         $options     The options.
     *
     * @return StatementInterface
     * @throws \ReflectionException
     */
    public function updateWhere(array|object $source, mixed $conditions = null, int $options = 0): StatementInterface
    {
        $metadata = $this->getMetadata();

        $data = $this->extract($source);
        $fields = array_keys($data);
        $data = $this->castForSave($data, true, $this->toEntity($source));
        $data = Arr::only($data, $fields);

        // Event
        $event = $this->emitEvent(
            BeforeUpdateWhereEvent::class,
            compact('data', 'metadata', 'conditions', 'source', 'options')
        );

        $metadata = $event->getMetadata();

        $statement = $this->getDb()->getWriter()->updateWhere(
            $metadata->getTableName(),
            $data = $event->getData(),
            $conditions = $event->getConditions()
        );

        // Event
        $event = $this->emitEvent(
            AfterUpdateWhereEvent::class,
            compact('data', 'metadata', 'conditions', 'statement', 'options')
        );

        return $event->getStatement();
    }

    /**
     * updateWhere
     *
     * @param  array|object  $data
     * @param  Conditions    $conditions
     * @param  int           $options
     *
     * @return  StatementInterface[]
     * @throws \ReflectionException
     */
    public function updateBatch(array|object $data, mixed $conditions = null, int $options = 0): array
    {
        $dataToSave = $this->extract($data);

        $results = [];

        foreach ($this->findList($conditions) as $item) {
            $item = $this->hydrate($dataToSave, $item);
            $results[] = $this->updateOne($item, null, $options);
        }

        return $results;
    }

    /**
     * @param  iterable           $items
     * @param  string|array|null  $condFields
     * @param  int                $options
     *
     * @return  iterable<T>
     *
     * @throws \ReflectionException
     */
    public function saveMultiple(iterable $items, string|array $condFields = null, int $options = 0): iterable
    {
        // Event
        foreach ($items as $k => $item) {
            // Do save
            if ($this->isNew($item)) {
                $items[$k] = $this->createOne($item, $options);
            } else {
                $this->updateOne($item, $condFields, $options);

                $items[$k] = $this->toEntity($item);
            }
        }

        // Event

        return $items;
    }

    public function canCheckIsNew(): bool
    {
        $keys = $this->getKeys();

        return count($keys) === 1 || $this->getAutoIncrementColumn(true);
    }

    public function isNew(array|object $item): bool
    {
        $keys = $this->getKeys();
        $pk = null;

        if (\Windwalker\count($keys) > 1) {
            $aiColumnName = $this->getAutoIncrementColumn(true);
            $pk = $aiColumnName;
        } elseif (\Windwalker\count($keys) === 1) {
            $pk = $keys[0];
        }

        if ($pk === null) {
            throw new LogicException(
                sprintf(
                    '%s must has at least 1 primary key or an auto-increment column in Entity to check isNew.',
                    $this->getMetadata()->getClassName()
                )
            );
        }

        $metadata = $this->getMetadata();

        if (
            $pk
            && is_object($item)
            && $metadata::isEntity($item)
            && $aiColumn = $metadata->getColumn($pk)
        ) {
            // If is Entity
            $aiPropName = $aiColumn->getName();
            $keyValue = ReflectAccessor::getValue($item, $aiPropName);
        } else {
            // Is array, object or Collection
            $keyValue = Arr::get($item, $pk);
        }

        return empty($keyValue);
    }

    /**
     * @param  array|object       $item
     * @param  array|string|null  $condFields
     * @param  int                $options
     *
     * @return  object|T
     *
     * @throws \ReflectionException
     */
    public function saveOne(array|object $item, array|string $condFields = null, int $options = 0): object
    {
        return $this->saveMultiple([$item], $condFields, $options)[0];
    }

    /**
     * @param  Conditions  $conditions
     * @param  mixed|null  $initData
     * @param  bool        $mergeConditions
     * @param  int         $options
     *
     * @return  object|T
     */
    public function findOneOrCreate(
        mixed $conditions,
        mixed $initData = null,
        bool $mergeConditions = true,
        int $options = 0
    ): object {
        $item = $this->findOne($conditions);

        if ($item) {
            return $item;
        }

        $item = [];

        if ($mergeConditions && is_array($conditions)) {
            foreach ($conditions as $k => $v) {
                if (!is_numeric($k)) {
                    $item[$k] = $v;
                }
            }
        }

        if (is_callable($initData)) {
            $result = $initData($item, $conditions);

            if ($result) {
                $item = $result;
            }
        } else {
            $initData = TypeCast::toArray($initData);

            foreach ($initData as $key => $value) {
                if ($value !== null) {
                    $item[$key] = $value;
                }
            }
        }

        return $this->createOne($item, $options);
    }

    /**
     * @param  array|object  $item
     * @param  mixed|null    $initData
     * @param  array|null    $condFields
     * @param  int           $options
     *
     * @return  object|T
     *
     * @throws \ReflectionException
     */
    public function updateOneOrCreate(
        array|object $item,
        mixed $initData = null,
        ?array $condFields = null,
        int $options = 0
    ): object {
        $condFields = $condFields ?: $this->getKeys();

        $conditions = [];

        $data = $this->extract($item);

        foreach ($condFields as $field) {
            $conditions[$field] = $data[$field];
        }

        if ($found = $this->findOne($conditions)) {
            $this->updateOne($item, $condFields, $options);

            return $this->hydrate(array_filter($data), $found);
        }

        if (is_callable($initData)) {
            $data = $initData($data, $conditions);
        } else {
            $initData = TypeCast::toArray($initData);

            foreach ($initData as $key => $value) {
                if ($value !== null) {
                    $data[$key] = $value;
                }
            }
        }

        return $this->createOne($data, $options);
    }

    /**
     * @param  Conditions  $conditions
     * @param  int         $options
     *
     * @return  void
     */
    public function deleteWhere(mixed $conditions, int $options = 0): void
    {
        // Event

        $metadata = $this->getMetadata();
        $writer = $this->getDb()->getWriter();
        $entityObject = null;

        // Handle Entity
        if (is_object($conditions) && EntityMetadata::isEntity($conditions)) {
            $entityObject = $conditions;

            $conditions = Arr::only($this->extract($conditions), $this->getKeys());

            if (in_array(null, $conditions, true) || in_array('', $conditions, true)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Unable to delete Entity: %s since the keys value contains NULL or empty string.',
                        $conditions::class
                    )
                );
            }
        }

        $keys = $this->getKeys();

        if (!$keys) {
            // If Entity has no keys, just use conditions to delete batch.
            $delItems = [$conditions];
        } elseif ($entityObject !== null) {
            $delItems = [$entityObject];
        } else {
            // If Entity has keys, use this keys to delete once per item.
            $delItems = (function () use ($metadata, $conditions) {
                while (
                    $item = $this->getORM()
                        ->from($metadata->getClassName())
                        ->where($this->conditionsToWheres($conditions))
                        ->get($metadata->getClassName())
                ) {
                    yield $item;
                }
            })();
        }

        foreach ($delItems as $item) {
            $handleRelations = true;

            if (!$keys) {
                $conditions = $this->conditionsToWheres($item);
                $data = [];
                $entity = null;
                $handleRelations = false;
            } elseif ($entityObject !== null) {
                $entity = $entityObject;
                $data = $this->extract($entityObject);
                $conditions = Arr::only($data, $keys);
            } else {
                /** @var object $item */
                $entity = $item;
                $data = $this->extract($entity);
                $conditions = Arr::only($data, $keys);
            }

            // Event
            $event = $this->emitEvent(
                BeforeDeleteEvent::class,
                compact('data', 'conditions', 'metadata', 'entity', 'options')
            );

            if ($handleRelations) {
                $metadata->getRelationManager()->beforeDelete($event->getData(), $entity);
            }

            $statement = $writer->delete($metadata->getTableName(), $conditions = $event->getConditions());

            // Event
            $event = $this->emitEvent(
                AfterDeleteEvent::class,
                compact('data', 'conditions', 'metadata', 'statement', 'entity', 'options')
            );

            $event->getStatement();

            if ($handleRelations) {
                $metadata->getRelationManager()->delete($event->getData(), $entity);
            }
        }

        // Event
    }

    /**
     * @param  iterable    $items
     * @param  Conditions  $conditions
     * @param  int         $options
     *
     * @return  iterable<T>
     */
    public function flush(iterable $items, mixed $conditions = [], int $options = 0): iterable
    {
        // Handling conditions
        $conditions = $this->conditionsToWheres($conditions);

        // Event

        $this->deleteWhere($conditions, $options | static::IGNORE_EVENTS);

        $items = $this->createMultiple($items, $options);

        // Event

        return $items;
    }

    /**
     * @param  Conditions              $conditions
     * @param  callable|iterable|null  $newValue
     * @param  int                     $options
     *
     * @return  array<T>
     * @throws JsonException
     * @throws \ReflectionException
     */
    public function copy(mixed $conditions = [], callable|iterable $newValue = null, int $options = 0): array
    {
        $items = $this->findList($conditions, Collection::class);
        $key = $this->getMainKey();
        $metadata = $this->getMetadata();
        $source = $conditions;

        $creates = [];

        /** @var Collection $item */
        foreach ($items as $i => $item) {
            $oldData = $item->dump();
            $data = $item->dump();

            unset($data[$key]);

            if (is_callable($newValue)) {
                $result = $newValue($data, $oldData, $conditions);

                if ($result) {
                    $data = $result;
                }
            } else {
                foreach ($newValue as $field => $value) {
                    if ($value !== null) {
                        $data[$field] = $value;
                    }
                }
            }

            $type = BeforeCopyEvent::TYPE_COPY;
            $event = $this->emitEvent(
                BeforeCopyEvent::class,
                compact('data', 'type', 'metadata', 'oldData', 'source', 'options')
            );

            $entity = $this->createOne($data = $event->getData(), $option = $event->getOptions());

            $newData = $this->extract($entity);

            $data[$key] = $newData[$key];

            $event = $this->emitEvent(
                AfterCopyEvent::class,
                compact('data', 'type', 'metadata', 'entity', 'oldData', 'source', 'options')
            );

            $creates[] = $event->getEntity();
        }

        return $creates;
    }

    /**
     * @param  iterable    $items
     * @param  Conditions  $conditions
     * @param  array|null  $compareKeys
     * @param  int         $options
     *
     * @return  array<array<T>>
     *
     * @throws \ReflectionException
     */
    public function sync(iterable $items, mixed $conditions = [], ?array $compareKeys = null, int $options = 0): array
    {
        // Handling conditions
        $metadata = $this->getMetadata();
        $conditions = $this->conditionsToWheres($conditions);

        $oldItems = $this->getORM()
            ->from($metadata->getClassName())
            ->where($conditions)
            ->all()
            ->dump(true);

        $compareKeys = $compareKeys ?? array_keys($conditions);

        // Event

        // Get diff
        $arrayItems = [];

        foreach ($items as $k => $item) {
            $arrayItems[$k] = $this->extract($item);
        }

        [$delItems,] = $this->getDeleteDiff($arrayItems, $oldItems, $compareKeys);
        [$createItems, $keepItems] = $this->getCreateDiff($arrayItems, $oldItems, $compareKeys);

        // Delete
        foreach ($delItems as $k => $delItem) {
            $this->deleteWhere(Arr::only($delItem, $compareKeys), $options);

            $delItems[$k] = $this->toEntity($delItem);
        }

        // Create
        $createItems = $this->createMultiple($createItems, $options);

        // Update
        foreach ($keepItems as $k => $keepItem) {
            $this->updateOne($keepItem, null, $options);

            $keepItems[$k] = $this->toEntity($keepItem);
        }

        // Event

        return [$keepItems, $createItems, $delItems];
    }

    protected function getDeleteDiff(iterable $items, array $oldItems, array $compareKeys): array
    {
        $keep = [];
        $deletes = [];

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
            $deletes[] = $old;
        }

        return [$deletes, $keep];
    }

    protected function getCreateDiff(iterable $items, array $oldItems, array $compareKeys): array
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

    public function increment(string|array $fields, mixed $conditions, int|float $num = 1, int $options = 0): void
    {
        if ($num < 0) {
            throw new InvalidArgumentException('Increment value should be positive number.');
        }

        $this->fieldOffsets('+', $fields, $conditions, $num, $options);
    }

    public function decrement(string|array $fields, mixed $conditions, int|float $num = 1, int $options = 0): void
    {
        if ($num < 0) {
            throw new InvalidArgumentException('Decrement value should be positive number.');
        }

        $this->fieldOffsets('-', $fields, $conditions, $num, $options);
    }

    public function fieldOffsets(
        string $operator,
        string|array $fields,
        mixed $conditions,
        int|float $num,
        int $options = 0
    ): void {
        if ($fields === '' || $fields === []) {
            throw new InvalidArgumentException('Fields is invalid or empty');
        }

        $fields = (array) $fields;

        $transaction = (bool) ($options & static::TRANSACTION);

        $this->getDb()->transaction(
            function () use ($transaction, $operator, $num, $options, $fields, $conditions) {
                $ignoreEvents = (bool) ($options & static::IGNORE_EVENTS);

                if ($ignoreEvents) {
                    $query = $this->update()
                        ->where($this->conditionsToWheres($conditions));

                    foreach ($fields as $field) {
                        $expr = $operator . abs($num);

                        $query->set($field, raw($query->qn($field) . ' ' . $expr));
                    }

                    $query->execute();

                    return;
                }

                $items = $this->select()
                    ->where($this->conditionsToWheres($conditions))
                    ->tapIf(
                        $transaction,
                        fn(Query $query) => $query->forUpdate()
                    )
                    ->getIterator();

                foreach ($items as $item) {
                    foreach ($fields as $field) {
                        if ($operator === '+') {
                            $item->$field += $num;
                        } else {
                            $item->$field -= $num;
                        }
                    }

                    $this->updateOne($item, options: $options);
                }
            },
            enabled: $transaction
        );
    }

    public function prepareRelations(object $entity): object
    {
        $data = $this->extract($entity);

        $this->getMetadata()
            ->getRelationManager()
            ->load($data, $entity);

        return $entity;
    }

    public function getKeys(): array
    {
        return $this->getMetadata()->getKeys();
    }

    public function getMainKey(): ?string
    {
        return $this->getMetadata()->getMainKey();
    }

    public function getTableName(): string
    {
        return $this->getMetadata()->getTableName();
    }

    /**
     * createEntity
     *
     * @return  object|T
     *
     * @throws \ReflectionException
     */
    public function createEntity(): object
    {
        $metadata = $this->getMetadata();

        if (!$entity = $metadata->getCachedEntity()) {
            $class = $metadata->getClassName();

            $entity = $this->getORM()->getAttributesResolver()->createObject($class);

            $metadata->setCachedEntity($entity);
        }

        return clone $entity;
    }

    /**
     * @param  array|object  $data
     *
     * @return  object|T
     *
     * @throws \ReflectionException
     */
    public function toEntity(array|object $data): object
    {
        $class = $this->getMetadata()->getClassName();

        if ($data instanceof $class) {
            return $data;
        }

        if (is_object($data)) {
            $data = TypeCast::toArray($data);
        }

        return $this->getORM()->hydrateEntity(
            $data,
            $this->createEntity()
        );
    }

    /**
     * @param  array|object|null  $data
     *
     * @return  object|T|null
     *
     * @throws \ReflectionException
     */
    public function toEntityOrNull(object|array|null $data): ?object
    {
        if ($data === null) {
            return null;
        }

        return $this->toEntity($data);
    }

    public function toCollection(array|object $data): Collection
    {
        if (is_array($data)) {
            return collect($data);
        }

        if (!EntityMetadata::isEntity($data)) {
            return collect($data);
        }

        $class = $this->getMetadata()->getClassName();

        if ($data instanceof $class) {
            $data = $this->extract($data);
        } elseif (is_object($data)) {
            $data = TypeCast::toArray($data);
        }

        return collect($data);
    }

    /**
     * hydrate
     *
     * @param  array     $data
     * @param  object|T  $entity
     *
     * @return  object|T
     *
     * @throws \ReflectionException
     */
    public function hydrate(array $data, object $entity): object
    {
        return $this->getORM()->hydrateEntity($data, $entity);
    }

    public function extract(object|array $entity): array
    {
        if ($entity instanceof Collection) {
            $entity = $entity->dump();
        }

        if (is_array($entity)) {
            return EntityHydrator::castArray($this->getMetadata(), $entity);
        }

        return $this->getORM()->extractEntity($entity);
    }

    public function extractField(object|array $entity, string $field): mixed
    {
        return $this->getORM()->extractField($entity, $field);
    }

    public function conditionsToWheres(mixed $conditions): array|\Closure
    {
        if ($conditions instanceof \Closure) {
            return $conditions;
        }

        if (is_array($conditions)) {
            return $conditions;
        }

        $metadata = $this->getMetadata();

        $key = $metadata->getMainKey();

        if ($key) {
            return [$key => $conditions];
        }

        throw new LogicException(
            sprintf(
                'Conditions cannot be scalars since %s has no keys',
                $metadata->getClassName()
            )
        );
    }

    protected function extractForSave(object|array $data, bool $updateNulls = true): array
    {
        $data = $this->extract($data);
        $entity = $this->toEntity($data);

        return $this->castForSave($data, $updateNulls, $entity);
    }

    protected function castForSave(array $data, bool $updateNulls = true, ?object $entity = null): array
    {
        $entity ??= $this->toEntity($data);

        $metadata = $this->getMetadata();

        $item = [];

        $db = $this->getDb();
        $dataType = $db->getPlatform()->getDataType();

        foreach ($this->getTableColumns() as $field => $column) {
            $value = $data[$field] ?? null;

            // Handler property attributes
            if ($prop = $metadata->getColumn($field)?->getProperty()) {
                $value = $this->castProperty($prop, $value, $entity);
            }

            if (!$updateNulls && $value === null) {
                continue;
            }

            // Convert value type
            if ($value instanceof DateTimeInterface) {
                $value = $this->orm->getCaster()->castDateTime($value);
            }

            if ($value instanceof \JsonSerializable) {
                $value = $this->orm->getCaster()->castJsonSerializable($value);
            }

            // Todo: Check why we need detect which is not ClauseInterface
            if (!$value instanceof ClauseInterface) {
                $value = $this->orm->getCaster()->castValue($value);
            }

            if ($value === null) {
                // This field is null and the db column is not nullable, use db default value.
                if ($column->getIsNullable()) {
                    $item[$field] = null;
                } elseif ($column->getColumnDefault() !== null) {
                    $item[$field] = $column->getColumnDefault();
                } elseif ($column->getErratas()['is_json'] ?? false) {
                    $item[$field] = '[]';
                } elseif ($column->isAutoIncrement()) {
                    $item[$field] = null;
                } else {
                    $def = $dataType::getDefaultValue($column->getDataType());
                    $item[$field] = $def !== false ? $def : '';
                }
            } elseif ($value === '') {
                // This field is null and the db column is not nullable, use db default value.
                if ($column->getIsNullable()) {
                    $item[$field] = null;
                } elseif ($column->getErratas()['is_json'] ?? false) {
                    $item[$field] = '[]';
                } else {
                    $def = $dataType::getDefaultValue($column->getDataType());
                    $item[$field] = $def !== false ? $def : '';
                }
            } elseif ($value instanceof ClauseInterface) {
                $item[$field] = $value;
            } else {
                $item[$field] = $dataType::castForSave($value, $column);
            }
        }

        return $item;
    }

    protected function castProperty(ReflectionProperty $prop, mixed $value, object $entity): mixed
    {
        $castManager = $this->getMetadata()->getCastManager();

        AttributesAccessor::runAttributeIfExists(
            $prop,
            CastForSave::class,
            function (CastForSave $attr) use ($entity, $castManager, &$value) {
                $caster = $castManager->wrapCastCallback(
                    $castManager->castToCallback($attr->getCaster() ?? $attr, $attr->options ?? 0),
                    $attr->options
                );

                $value = $caster($value, $this->getORM(), $entity);
            },
            ReflectionAttribute::IS_INSTANCEOF
        );

        return $value;
    }

    /**
     * getTableColumns
     *
     * @return  DbColumn[]
     */
    protected function getTableColumns(): array
    {
        return $this->getDb()
            ->getTable(
                $this->getMetadata()->getTableName()
            )
            ->getColumns();
    }

    protected function getAutoIncrementColumn(bool $checkDB = false): ?string
    {
        $ai = $this->getMetadata()->getAutoIncrementColumn()?->getName();

        if ($ai) {
            return $ai;
        }

        if ($checkDB) {
            foreach ($this->getTableColumns() as $column) {
                if ($column->isAutoIncrement()) {
                    return $column->getColumnName();
                }
            }
        }

        return null;
    }

    /**
     * @return ORM
     */
    public function getORM(): ORM
    {
        return $this->orm;
    }

    public function getDb(): DatabaseAdapter
    {
        return $this->getORM()->getDb();
    }

    /**
     * @return EntityMetadata
     */
    public function getMetadata(): EntityMetadata
    {
        return $this->metadata;
    }

    public function emitEvent(EventInterface|string $event, array $args = []): EventInterface
    {
        if (($args['options'] ?? null) && ($args['options'] & static::IGNORE_EVENTS)) {
            if (is_string($event) || $event instanceof EventInterface) {
                $event = Event::wrap($event, $args);
            }

            return $event;
        }

        $event = $this->emit($event, $args);

        $methods = $this->getMetadata()->getMethodsOfAttribute($event::class);

        foreach ($methods as $method) {
            if (!$method->isStatic()) {
                throw new LogicException(
                    sprintf(
                        "Entity event hook: %s::%s must be static method.",
                        $this->metadata->getClassName(),
                        $method->getName()
                    )
                );
            }

            $result = $this->getORM()->getAttributesResolver()->call(
                $method->getClosure(),
                [
                    $event::class => $event,
                    'event' => $event,
                ]
            );

            if ($result instanceof EventInterface) {
                $event = $result;
            }
        }

        return $event;
    }

    protected function hasEvents(...$events): bool
    {
        foreach ($events as $event) {
            foreach ($this->getEventDispatcher()->getListeners($event) as $listener) {
                return true;
            }

            $methods = $this->getMetadata()->getMethodsOfAttribute($event);

            if ($methods !== []) {
                return true;
            }
        }

        return false;
    }
}
