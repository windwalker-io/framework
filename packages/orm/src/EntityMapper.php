<?php

declare(strict_types=1);

namespace Windwalker\ORM;

use Asika\ObjectMetadata\ObjectMetadata;
use DateTimeInterface;
use InvalidArgumentException;
use JsonException;
use LogicException;
use ReflectionAttribute;
use ReflectionProperty;
use Windwalker\Data\Collection;
use Windwalker\Database\DatabaseAdapter;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Database\Schema\Ddl\Column as DbColumn;
use Windwalker\Event\Event;
use Windwalker\Event\EventAwareInterface;
use Windwalker\Event\EventAwareTrait;
use Windwalker\Event\EventInterface;
use Windwalker\ORM\Attributes\CastForSaveInterface;
use Windwalker\ORM\Attributes\UUIDBin;
use Windwalker\ORM\Event\{AbstractEntityEvent,
    AbstractSaveEvent,
    AfterCopyEvent,
    AfterCreateBulkEvent,
    AfterDeleteEvent,
    AfterSaveEvent,
    AfterUpdateWhereEvent,
    BeforeCopyEvent,
    BeforeCreateBulkEvent,
    BeforeDeleteEvent,
    BeforeSaveEvent,
    BeforeStoreEvent,
    BeforeUpdateWhereEvent,
    EnergizeEvent};
use Windwalker\ORM\Hydrator\EntityHydrator;
use Windwalker\ORM\Iterator\ResultIterator;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\Query\Clause\ClauseInterface;
use Windwalker\Query\Exception\NoResultException;
use Windwalker\Query\Query;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Assert\TypeAssert;
use Windwalker\Utilities\Attributes\AttributesAccessor;
use Windwalker\Utilities\Reflection\ReflectAccessor;
use Windwalker\Utilities\TypeCast;
use Windwalker\Utilities\Wrapper\RawWrapper;

use function is_object;
use function Windwalker\collect;
use function Windwalker\raw;
use function Windwalker\try_wrap_uuid;

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
     * @param  Conditions        $conditions
     * @param  ?class-string<T>  $className
     * @param  ORMOptions|int    $options
     *
     * @return  object|null|T
     */
    public function findOne(
        mixed $conditions = [],
        ?string $className = null,
        ORMOptions|int $options = new ORMOptions()
    ): ?object {
        $options = ORMOptions::wrap($options);
        $metadata = $this->getMetadata();

        return $this->from($metadata->getClassName())
            ->where($this->conditionsToWheres($conditions))
            ->tapIf(
                (bool) $options->forUpdate,
                fn(Query $query) => $query->forUpdate($options->forUpdateDo)
            )
            ->tapIf(
                (bool) $options->forShare,
                fn(Query $query) => $query->forShare($options->forShareDo)
            )
            ->get($className ?? $metadata->getClassName());
    }

    /**
     * @param  Conditions        $conditions
     * @param  ?class-string<T>  $className
     * @param  ORMOptions|int    $options
     *
     * @return  object|T
     */
    public function mustFindOne(
        mixed $conditions = [],
        ?string $className = null,
        ORMOptions|int $options = new ORMOptions()
    ): object {
        if (!$item = $this->findOne($conditions, $className, $options)) {
            throw new NoResultException($this->getTableName(), $conditions);
        }

        return $item;
    }

    /**
     * @param  Conditions            $conditions
     * @param  class-string<T>|null  $className
     * @param  ORMOptions|int        $options
     *
     * @return  ResultIterator<T>
     */
    public function findList(
        mixed $conditions = [],
        ?string $className = null,
        ORMOptions|int $options = new ORMOptions()
    ): ResultIterator {
        $options = ORMOptions::wrap($options);
        $metadata = $this->getMetadata();

        return new ResultIterator(
            $this->select()
                ->where($this->conditionsToWheres($conditions))
                ->tapIf(
                    (bool) $options->forUpdate,
                    fn(Query $query) => $query->forUpdate($options->forUpdateDo)
                )
                ->tapIf(
                    (bool) $options->forShare,
                    fn(Query $query) => $query->forShare($options->forShareDo)
                )
                ->getIterator($className ?? $metadata->getClassName())
        );
    }

    /**
     * @param  string|RawWrapper  $column
     * @param  Conditions         $conditions
     * @param  ORMOptions|int     $options
     *
     * @return  mixed
     */
    public function findResult(
        string|RawWrapper $column,
        mixed $conditions = [],
        ORMOptions|int $options = new ORMOptions()
    ): mixed {
        $options = ORMOptions::wrap($options);

        return $this->select($column)
            ->where($this->conditionsToWheres($conditions))
            ->tapIf(
                (bool) $options->forUpdate,
                fn(Query $query) => $query->forUpdate($options->forUpdateDo)
            )
            ->tapIf(
                (bool) $options->forShare,
                fn(Query $query) => $query->forShare($options->forShareDo)
            )
            ->result();
    }

    /**
     * @param  string          $column
     * @param  Conditions      $conditions
     * @param  ORMOptions|int  $options
     *
     * @return  Collection
     */
    public function findColumn(
        string $column,
        mixed $conditions = [],
        ORMOptions|int $options = new ORMOptions()
    ): Collection {
        $options = ORMOptions::wrap($options);

        return $this->select($column)
            ->where($this->conditionsToWheres($conditions))
            ->tapIf(
                (bool) $options->forUpdate,
                fn(Query $query) => $query->forUpdate($options->forUpdateDo)
            )
            ->tapIf(
                (bool) $options->forShare,
                fn(Query $query) => $query->forShare($options->forShareDo)
            )
            ->loadColumn();
    }

    /**
     * @param  string             $column
     * @param  Conditions         $conditions
     * @param  array|string|null  $groups
     *
     * @return  int
     */
    public function countColumn(string $column, mixed $conditions = [], array|string|null $groups = null): int
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
    public function sumColumn(string $column, mixed $conditions = [], array|string|null $groups = null): float
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
     * @param  array|object    $source
     * @param  ORMOptions|int  $options
     *
     * @return  object|T
     *
     * @throws JsonException
     * @throws \ReflectionException
     */
    public function createOne(array|object $source = [], ORMOptions|int $options = new ORMOptions()): object
    {
        $options = ORMOptions::wrap($options);
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
        $event = $this->emits(
            new BeforeSaveEvent(
                type: AbstractSaveEvent::TYPE_CREATE,
                source: $source,
                data: $data,
                options: $options,
            )
        );

        // Hydrate data into entity after event, to make sure all fields has default value.
        $fullData = $event->data;
        $entity = $this->hydrate($fullData, $this->toEntity($source));

        $data = $this->castForSave($this->extract($entity), true, $entity, true);

        $event = $this->emits(
            new BeforeStoreEvent(
                type: BeforeStoreEvent::TYPE_CREATE,
                source: $source,
                data: $data,
                options: $options,
                extra: $event->extra
            )
        );

        $data = $event->data;
        $extra = $event->extra;

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

        $event = $this->emits(
            new AfterSaveEvent(
                type: AfterSaveEvent::TYPE_CREATE,
                entity: $entity,
                source: $source,
                data: $data,
                fullData: $fullData,
                options: $options,
                extra: $extra
            )
        );

        $entity = $this->hydrate(
            $event->data,
            $event->entity
        );

        $metadata->getRelationManager()->save($event->data, $entity);

        return $entity;
    }

    /**
     * @param  iterable        $items
     * @param  ORMOptions|int  $options
     *
     * @return  iterable<T>
     * @throws JsonException
     * @throws \ReflectionException
     */
    public function createMultiple(iterable $items, ORMOptions|int $options = new ORMOptions()): iterable
    {
        /** @var array|object $item */
        foreach ($items as $k => $item) {
            $items[$k] = $this->createOne($item, $options);
        }

        return $items;
    }

    /**
     * @param  iterable        $items
     * @param  ORMOptions|int  $options
     *
     * @return  array<T>
     *
     * @throws \ReflectionException
     */
    public function createBulk(iterable $items, ORMOptions|int $options = new ORMOptions()): array
    {
        $options = ORMOptions::wrap($options);
        $metadata = $this->getMetadata();

        $dataSet = [];
        $entities = [];

        foreach ($items as $item) {
            TypeAssert::assert(
                is_object($item) || is_array($item),
                '{caller} item must be array or object, {value} given',
                $item
            );

            $data = $this->extract($item);
            $data = $this->castForSave($data, true, $entities[] = $this->toEntity($item));

            $dataSet[] = $data;
        }

        // Event
        $event = $this->emits(
            new BeforeCreateBulkEvent(
                items: $dataSet,
                entities: $entities,
                options: $options,
            )
        );

        $items = $this->getDb()->getWriter()->insertBulk(
            $metadata->getTableName(),
            $event->items,
        );

        // Event
        $event = $this->emits(
            new AfterCreateBulkEvent(
                items: $items,
                entities: $event->entities,
                options: $event->options,
            )
        );

        return $event->entities;
    }

    public function updateOne(
        array|object $source = [],
        array|string|null $condFields = null,
        ORMOptions|int $options = new ORMOptions(),
    ): ?StatementInterface {
        if ($source === []) {
            return null;
        }

        $options = ORMOptions::wrap($options);
        $metadata = $this->getMetadata();
        $updateNulls = $options->updateNulls;

        if ($this->metadata::isEntity($source)) {
            $updateNulls = true;
        }

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

        if (!$options->ignoreOldData && $this->getKeys() && !empty($data[$this->getMainKey()])) {
            $oldData = $this->getDb()->select('*')
                ->from($metadata->getTableName())
                ->where($this->conditionsToWheres(Arr::only($data, $this->getKeys())))
                ->get()
                ?->dump();

            $data = array_merge($oldData ?? [], $data);
        }

        $event = $this->emits(
            new BeforeSaveEvent(
                type: AbstractSaveEvent::TYPE_UPDATE,
                source: $source,
                data: $data,
                oldData: $oldData,
                options: $options
            )
        );

        // Hydrate data into entity after event, to make sure all fields has default value.
        $fullData = $event->data;
        $entity = $this->hydrate($fullData, $this->toEntity($source));

        $data = $this->castForSave($this->extract($entity), $updateNulls, $entity);

        $writeData = $data;

        $keyValues = Arr::only($writeData, (array) $condFields);

        // Only compare if write data use full keys
        if ($oldData !== null && count(array_intersect_key($writeData, $oldData)) === count($oldData)) {
            $writeData = array_diff_assoc($writeData, $oldData);
        }

        if ($writeData !== []) {
            $writeData = array_merge($keyValues, $writeData);

            $metadata->getRelationManager()->beforeUpdate($writeData, $entity, $oldData);

            $event = $this->emits(
                new BeforeStoreEvent(
                    type: BeforeStoreEvent::TYPE_UPDATE,
                    source: $source,
                    data: $writeData,
                    options: $options,
                    extra: $event->extra,
                )
            );

            $result = $this->getDb()->getWriter()->updateOne(
                $metadata->getTableName(),
                $event->data,
                $condFields,
                [
                    'updateNulls' => $updateNulls,
                ]
            );
        }

        $event = $this->emits(
            new AfterSaveEvent(
                type: AfterSaveEvent::TYPE_UPDATE,
                entity: $entity,
                source: $source,
                data: $data,
                oldData: $oldData,
                fullData: $fullData,
                options: $options,
                extra: $event->extra
            )
        );

        $metadata->getRelationManager()->save($event->data, $entity, $oldData);

        // Event

        return $result ?? null;
    }

    /**
     * @param  iterable           $items
     * @param  array|string|null  $condFields
     * @param  ORMOptions|int     $options
     *
     * @return  StatementInterface[]
     */
    public function updateMultiple(
        iterable $items,
        array|string|null $condFields = null,
        ORMOptions|int $options = new ORMOptions()
    ): array {
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
     * @param  mixed           $source      The data we want to update to every rows.
     * @param  Conditions      $conditions  Where conditions, you can use array or Compare object.
     * @param  ORMOptions|int  $options     The options.
     *
     * @return StatementInterface
     * @throws \ReflectionException
     */
    public function updateWhere(
        array|object $source,
        mixed $conditions = null,
        ORMOptions|int $options = new ORMOptions()
    ): StatementInterface {
        $options = ORMOptions::wrap($options);
        $metadata = $this->getMetadata();

        $data = $this->extract($source);
        $fields = array_keys($data);
        $data = $this->castForSave($data, true, $this->toEntity($source));
        $data = Arr::only($data, $fields);

        // Event
        $event = $this->emits(
            new BeforeUpdateWhereEvent(
                conditions: $conditions,
                source: $source,
                data: $data,
                options: $options
            )
        );

        $statement = $this->getDb()->getWriter()->updateWhere(
            $metadata->getTableName(),
            $data = $event->data,
            $this->conditionsToWheres($conditions = $event->conditions)
        );

        // Event
        $event = $this->emits(
            new AfterUpdateWhereEvent(
                statement: $statement,
                conditions: $conditions,
                data: $data,
                options: $options
            )
        );

        return $event->statement;
    }

    /**
     * @param  array|object    $data
     * @param  Conditions      $conditions
     * @param  ORMOptions|int  $options
     *
     * @return  StatementInterface[]
     * @throws \ReflectionException
     */
    public function updateBatch(
        array|object $data,
        mixed $conditions = null,
        ORMOptions|int $options = new ORMOptions()
    ): array {
        $options = ORMOptions::wrap($options);

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
    public function saveMultiple(
        iterable $items,
        string|array|null $condFields = null,
        ORMOptions|int $options = new ORMOptions()
    ): iterable {
        $options = ORMOptions::wrap($options);

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
        $pk = $this->getMainKey();

        if (!$pk) {
            throw new LogicException(
                sprintf(
                    '%s must has at least 1 primary key or an auto-increment column in Entity to check isNew.',
                    $this->getMetadata()->getClassName()
                )
            );
        }

        $metadata = $this->getMetadata();

        if (
            is_object($item)
            && $aiColumn = $metadata->getColumn($pk)
        ) {
            // camel
            $aiPropName = $aiColumn->getProperty()->getName();

            if (!ReflectAccessor::hasProperty($item, $aiPropName)) {
                // snake
                $aiPropName = $aiColumn->getName();
            }

            if (!ReflectAccessor::hasProperty($item, $aiPropName)) {
                if (isset($item->$aiPropName)) {
                    $keyValue = $item->$aiPropName;
                } else {
                    return true;
                }
            } else {
                $keyValue = ReflectAccessor::getValue($item, $aiPropName);
            }
        } else {
            // Is array, object or Collection
            $keyValue = Arr::get($item, $pk);
        }

        return empty($keyValue);
    }

    /**
     * @param  array|object       $item
     * @param  array|string|null  $condFields
     * @param  ORMOptions|int     $options
     *
     * @return  object|T
     *
     * @throws \ReflectionException
     */
    public function saveOne(
        array|object $item,
        array|string|null $condFields = null,
        ORMOptions|int $options = new ORMOptions()
    ): object {
        return $this->saveMultiple([$item], $condFields, $options)[0];
    }

    /**
     * @param  Conditions      $conditions
     * @param  mixed|null      $initData
     * @param  bool            $mergeConditions
     * @param  ORMOptions|int  $options
     *
     * @return  object|T
     * @throws JsonException
     * @throws \ReflectionException
     */
    public function findOneOrCreate(
        mixed $conditions,
        mixed $initData = null,
        bool $mergeConditions = true,
        ORMOptions|int $options = new ORMOptions()
    ): object {
        $options = clone ORMOptions::wrap($options);

        return $this->orm->transaction(
            function () use ($options, $initData, $mergeConditions, $conditions) {
                $options->forUpdate = $options->transaction;

                $item = $this->findOne($conditions, options: $options);

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

                $item = $this->prepareCreateInitData($initData, $item, $conditions);

                return $this->createOne($item, $options);
            },
            enabled: $options->transaction
        );
    }

    /**
     * @param  array|object    $item
     * @param  mixed|null      $initData
     * @param  array|null      $condFields
     * @param  ORMOptions|int  $options
     *
     * @return  object|T
     *
     * @throws JsonException
     * @throws \ReflectionException
     */
    public function updateOneOrCreate(
        array|object $item,
        mixed $initData = null,
        ?array $condFields = null,
        ORMOptions|int $options = new ORMOptions()
    ): object {
        $options = clone ORMOptions::wrap($options);
        $condFields = $condFields ?: $this->getKeys();

        $conditions = [];

        $data = $this->extract($item);

        foreach ($condFields as $field) {
            $conditions[$field] = $data[$field];
        }

        return $this->orm->transaction(
            function () use ($initData, $data, $item, $options, $condFields, $conditions) {
                $options->forUpdate = $options->transaction;

                if ($found = $this->findOne($conditions, options: $options)) {
                    $this->updateOne($item, $condFields, $options);

                    return $this->hydrate(array_filter($data), $found);
                }

                $data = $this->prepareCreateInitData($initData, $data, $conditions);

                return $this->createOne($data, $options);
            },
            enabled: $options->transaction
        );
    }

    protected function prepareCreateInitData(mixed $initData, array $item, mixed $conditions): array
    {
        if (is_callable($initData)) {
            $item = $initData($item, $conditions) ?? $item;
        } else {
            $initData = TypeCast::toArray($initData);

            foreach ($initData as $key => $value) {
                if ($value !== null) {
                    $item[$key] = $value;
                }
            }
        }

        return $item;
    }

    /**
     * @param  Conditions      $conditions
     * @param  ORMOptions|int  $options
     *
     * @return  void
     * @throws \ReflectionException
     */
    public function deleteWhere(mixed $conditions, ORMOptions|int $options = new ORMOptions()): void
    {
        $options = ORMOptions::wrap($options);

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
                $query = $this->getORM()
                    ->from($metadata->getClassName())
                    ->where($this->conditionsToWheres($conditions));

                while ($item = $query->get($metadata->getClassName())) {
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
                $conditions = $this->conditionsToWheres(Arr::only($data, $keys));
            } else {
                /** @var object $item */
                $entity = $item;
                $data = $this->extract($entity);
                $conditions = $this->conditionsToWheres(Arr::only($data, $keys));
            }

            // Event
            $event = $this->emits(
                new BeforeDeleteEvent(
                    conditions: $conditions,
                    entity: $entity,
                    data: $data,
                    options: $options
                )
            );

            if ($handleRelations) {
                $metadata->getRelationManager()->beforeDelete($event->data, $entity);
            }

            $statement = $writer->delete($metadata->getTableName(), $conditions = $event->conditions);

            // Event
            $event = $this->emits(
                new AfterDeleteEvent(
                    statement: $statement,
                    conditions: $conditions,
                    entity: $entity,
                    data: $event->data,
                    options: $options
                )
            );

            if ($handleRelations) {
                $metadata->getRelationManager()->delete($event->data, $entity);
            }
        }
        // Event
    }

    /**
     * @param  iterable        $items
     * @param  Conditions      $conditions
     * @param  ORMOptions|int  $options
     *
     * @return  iterable<T>
     * @throws JsonException
     * @throws \ReflectionException
     */
    public function flush(
        iterable $items,
        mixed $conditions = [],
        ORMOptions|int $options = new ORMOptions()
    ): iterable {
        $options = ORMOptions::wrap($options);

        // Handling conditions
        $conditions = $this->conditionsToWheres($conditions);

        // Event
        $options->ignoreEvents = true;

        $this->deleteWhere($conditions, $options);

        $items = $this->createMultiple($items, $options);

        // Event

        return $items;
    }

    /**
     * @param  Conditions              $conditions
     * @param  callable|iterable|null  $newValue
     * @param  ORMOptions|int          $options
     *
     * @return  array<T>
     * @throws JsonException
     * @throws \ReflectionException
     */
    public function copy(
        mixed $conditions = [],
        callable|iterable|null $newValue = null,
        ORMOptions|int $options = new ORMOptions()
    ): array {
        $options = ORMOptions::wrap($options);

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

            $event = $this->emits(
                new BeforeCopyEvent(
                    type: BeforeCopyEvent::TYPE_COPY,
                    source: $source,
                    data: $data,
                    oldData: $oldData,
                    options: $options
                )
            );

            $entity = $this->createOne($data = $event->data, $options = $event->options);

            $newData = $this->extract($entity);

            $data[$key] = $newData[$key];

            $event = $this->emits(
                new AfterCopyEvent(
                    type: AfterCopyEvent::TYPE_COPY,
                    entity: $entity,
                    source: $source,
                    data: $data,
                    oldData: $oldData,
                    fullData: $data,
                    options: $options,
                    extra: $event->extra
                )
            );

            $creates[] = $event->entity;
        }

        return $creates;
    }

    /**
     * @param  iterable        $items
     * @param  Conditions      $conditions
     * @param  array|null      $compareKeys
     * @param  ORMOptions|int  $options
     *
     * @return  array{ array<T>, array<T>, array<T> }
     *
     * @throws JsonException
     * @throws \ReflectionException
     */
    public function sync(
        iterable $items,
        mixed $conditions = [],
        ?array $compareKeys = null,
        ORMOptions|int $options = new ORMOptions()
    ): array {
        $options = ORMOptions::wrap($options);

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
            $this->deleteWhere(
                $this->conditionsToWheres(Arr::only($delItem, $compareKeys)),
                $options
            );

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

    public function increment(
        string|array $fields,
        mixed $conditions,
        int|float $num = 1,
        ORMOptions|int $options = new ORMOptions(),
    ): void {
        if ($num < 0) {
            throw new InvalidArgumentException('Increment value should be positive number.');
        }

        $this->fieldOffsets($fields, $conditions, abs($num), $options);
    }

    public function incrementOrCreate(
        string|array $fields,
        mixed $conditions,
        int|float $num = 1,
        mixed $initData = null,
        ORMOptions|int $options = new ORMOptions(),
    ): void {
        if ($num < 0) {
            throw new InvalidArgumentException('Increment value should be positive number.');
        }

        $this->orm->transaction(
            function () use ($options, $num, $fields, $initData, $conditions) {
                $this->findOneOrCreate(
                    $conditions,
                    initData: $initData,
                    options: $options->with(transaction: true)
                );

                $this->increment($fields, $conditions, $num, $options->with(transaction: true));
            }
        );
    }

    public function decrement(
        string|array $fields,
        mixed $conditions,
        int|float $num = 1,
        ORMOptions|int $options = new ORMOptions()
    ): void {
        if ($num < 0) {
            throw new InvalidArgumentException('Decrement value should be positive number.');
        }

        $this->fieldOffsets($fields, $conditions, -abs($num), $options);
    }

    public function decrementOrCreate(
        string|array $fields,
        mixed $conditions,
        int|float $num = 1,
        mixed $initData = null,
        ORMOptions|int $options = new ORMOptions(),
    ): void {
        if ($num < 0) {
            throw new InvalidArgumentException('Increment value should be positive number.');
        }

        $this->orm->transaction(
            function () use ($options, $num, $fields, $initData, $conditions) {
                $this->findOneOrCreate(
                    $conditions,
                    initData: $initData,
                    options: $options->with(transaction: true)
                );

                $this->decrement($fields, $conditions, $num, $options->with(transaction: true));
            }
        );
    }

    public function fieldOffsets(
        string|array $fields,
        mixed $conditions,
        int|float $num,
        ORMOptions|int $options = new ORMOptions(),
    ): void {
        if ($fields === '' || $fields === []) {
            throw new InvalidArgumentException('Fields is invalid or empty');
        }

        if ($num === 0) {
            return;
        }

        $fields = (array) $fields;

        $this->getDb()->transaction(
            function () use ($num, $options, $fields, $conditions) {
                if ($options->ignoreEvents) {
                    $query = $this->update()
                        ->where($this->conditionsToWheres($conditions));

                    foreach ($fields as $field) {
                        $expr = ($num > 0 ? '+' : '-') . abs($num);

                        $query->set($field, raw($query->qn($field) . ' ' . $expr));
                    }

                    $query->execute();

                    return;
                }

                $items = $this->select()
                    ->where($this->conditionsToWheres($conditions))
                    ->tapIf(
                        $options->transaction,
                        fn(Query $query) => $query->forUpdate()
                    )
                    ->getIterator();

                foreach ($items as $item) {
                    foreach ($fields as $field) {
                        $item->$field += $num;
                    }

                    $this->updateOne($item, options: $options);
                }
            },
            enabled: $options->transaction
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
        $pk = $this->getAutoIncrementColumn();

        if ($pk) {
            return $pk;
        }

        return $this->getMetadata()->getMainKey();
    }

    public function getTableName(): string
    {
        return $this->getMetadata()->getTableName();
    }

    /**
     * @param  mixed  ...$args
     *
     * @return T
     *
     * @throws \ReflectionException
     */
    public function createEntity(...$args): object
    {
        $metadata = $this->getMetadata();

        if (!$entity = $metadata->getCachedEntity()) {
            $class = $metadata->getClassName();

            $entity = $this->getORM()->getAttributesResolver()->createObject($class);

            $metadata->setCachedEntity($entity);
        }

        if ($args !== []) {
            $entity = $this->hydrate($args, $entity);
        }

        return $this->energize(clone $entity);
    }

    public function isEnergized(object $entity): bool
    {
        return (bool) static::getObjectMetadata()->get($entity, 'entity.energized');
    }

    /**
     * @param  T     $entity
     * @param  bool  $force
     *
     * @return  T
     */
    public function energize(object $entity, bool $force = false): object
    {
        $meta = static::getObjectMetadata();

        if ($force) {
            $meta->set($entity, 'entity.energized', false);
        }

        if ($this->isEnergized($entity)) {
            return $entity;
        }

        $meta->set($entity, 'entity.metadata', $this->getMetadata());

        $event = $this->emits(
            new EnergizeEvent(
                entity: $entity,
            )
        );

        /** @var T $entity */
        $entity = $event->entity;

        $meta->set($entity, 'entity.energized', true);

        return $entity;
    }

    public function unenergize(object $entity): static
    {
        self::getObjectMetadata()->set($entity, 'entity.energized', false);

        return $this;
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

        if (is_a($data, $class, true)) {
            return $this->energize($data);
        }

        if (is_object($data)) {
            $data = TypeCast::toArray($data);
        }

        // Only ORM has Hydrator, we must call ORM to do this.
        /** @var T $entity */
        $entity = $this->getORM()->hydrateEntity(
            $data,
            $this->createEntity()
        );

        return $entity;
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
        return $this->tryEntity($data);
    }

    /**
     * @param  array|object|null  $data
     *
     * @return  object|T|null
     *
     * @throws \ReflectionException
     */
    public function tryEntity(object|array|null $data): ?object
    {
        if ($data === null) {
            return null;
        }

        /** @var ?T $entity */
        $entity = $this->toEntity($data);

        return $entity;
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
        // Only ORM has Hydrator, we must call ORM to do this.
        /** @var T $entity */
        $entity = $this->getORM()->hydrateEntity($data, $entity);

        return $entity;
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
            foreach ($conditions as $k => $v) {
                if (!is_numeric($k)) {
                    $conditions[$k] = $this->handleConditionColumn($k, $v);
                } else {
                    $conditions[$k] = $v;
                }
            }

            return $conditions;
        }

        $metadata = $this->getMetadata();

        $key = $metadata->getMainKey();

        if ($key) {
            return [$key => $this->handleConditionColumn($key, $conditions)];
        }

        throw new LogicException(
            sprintf(
                'Conditions cannot be scalars since %s has no keys',
                $metadata->getClassName()
            )
        );
    }

    protected function handleConditionColumn(string $key, mixed $value): mixed
    {
        $metadata = $this->getMetadata();

        $col = $metadata->getColumn($key);

        if (!$col) {
            return $value;
        }

        $prop = $col->getProperty();

        // UUID Binary
        $uuidAttr = $prop->getAttributes(UUIDBin::class, ReflectionAttribute::IS_INSTANCEOF);

        if ($uuidAttr) {
            if (is_array($value)) {
                return array_map(fn($v) => try_wrap_uuid($v), $value);
            }

            return try_wrap_uuid($value);
        }

        return $value;
    }

    protected function extractForSave(object|array $data, bool $updateNulls = true, bool $isNew = false): array
    {
        $data = $this->extract($data);
        $entity = $this->toEntity($data);

        return $this->castForSave($data, $updateNulls, $entity, $isNew);
    }

    protected function castForSave(
        array $data,
        bool $updateNulls = true,
        ?object $entity = null,
        bool $isNew = false
    ): array {
        $entity ??= $this->toEntity($data);

        $metadata = $this->getMetadata();

        $item = [];

        $db = $this->getDb();
        $dataType = $db->getPlatform()->getDataType();

        foreach ($this->getTableColumns() as $field => $column) {
            $value = $data[$field] ?? null;

            // Handler property attributes
            if ($prop = $metadata->getColumn($field)?->getProperty()) {
                $value = $this->castProperty($prop, $value, $entity, $isNew);
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

    protected function castProperty(ReflectionProperty $prop, mixed $value, object $entity, bool $isNew = false): mixed
    {
        $castManager = $this->getMetadata()->getCastManager();

        AttributesAccessor::runAttributeIfExists(
            $prop,
            CastForSaveInterface::class,
            function (CastForSaveInterface $attr) use ($isNew, $entity, $castManager, &$value) {
                $caster = $castManager->wrapCastCallback(
                    $castManager->castToCallback($attr->getCaster() ?? $attr, $attr->options ?? 0),
                    $attr->options ?? 0
                );

                $value = $caster($value, $this->getORM(), $entity, $isNew);
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
            ->getTableManager(
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
        $options = ORMOptions::wrap($args['options'] ?? null);

        if ($options->ignoreEvents) {
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

    /**
     * @template  Event of EventInterface
     *
     * @param  Event  $event
     *
     * @return  Event
     *
     * @throws \ReflectionException
     */
    public function emits(EventInterface $event): EventInterface
    {
        if ($event instanceof AbstractEntityEvent) {
            $event->metadata = $this->metadata;
        }

        $options = $event->options ?? new ORMOptions();

        if ($options->ignoreEvents) {
            return $event;
        }

        $event = $this->emit($event);

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

    public static function getObjectMetadata(): ObjectMetadata
    {
        return ObjectMetadata::getInstance('windwalker.orm');
    }
}
