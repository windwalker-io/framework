<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM;

use Attribute;
use BadMethodCallException;
use ReflectionException;
use Windwalker\Attributes\AttributesAwareTrait;
use Windwalker\Attributes\AttributesResolver;
use Windwalker\Data\Collection;
use Windwalker\Database\DatabaseAdapter;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Database\Event\HydrateEvent;
use Windwalker\Database\Hydrator\FieldHydratorInterface;
use Windwalker\Event\EventAwareInterface;
use Windwalker\Event\EventAwareTrait;
use Windwalker\ORM\Attributes\AutoIncrement;
use Windwalker\ORM\Attributes\Cast;
use Windwalker\ORM\Attributes\CastNullable;
use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Attributes\EntitySetup;
use Windwalker\ORM\Attributes\ManyToMany;
use Windwalker\ORM\Attributes\ManyToOne;
use Windwalker\ORM\Attributes\Mapping;
use Windwalker\ORM\Attributes\NestedSet;
use Windwalker\ORM\Attributes\OneToMany;
use Windwalker\ORM\Attributes\OneToOne;
use Windwalker\ORM\Attributes\PK;
use Windwalker\ORM\Attributes\Table;
use Windwalker\ORM\Attributes\UUID;
use Windwalker\ORM\Attributes\Watch;
use Windwalker\ORM\Attributes\WatchBefore;
use Windwalker\ORM\Event\AfterCopyEvent;
use Windwalker\ORM\Event\AfterDeleteEvent;
use Windwalker\ORM\Event\AfterSaveEvent;
use Windwalker\ORM\Event\AfterStoreEvent;
use Windwalker\ORM\Event\AfterUpdateWhereEvent;
use Windwalker\ORM\Event\BeforeCopyEvent;
use Windwalker\ORM\Event\BeforeDeleteEvent;
use Windwalker\ORM\Event\BeforeSaveEvent;
use Windwalker\ORM\Event\BeforeStoreEvent;
use Windwalker\ORM\Event\BeforeUpdateWhereEvent;
use Windwalker\ORM\Hydrator\EntityHydrator;
use Windwalker\ORM\Iterator\ResultIterator;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\Metadata\EntityMetadataCollection;
use Windwalker\Query\Query;
use Windwalker\Utilities\Wrapper\RawWrapper;

/**
 * The ORM class.
 *
 * phpcs:disable
 *
 * @see EntityMapper
 *
 * @method  object|null   findOne(string $entityClass, mixed $conditions = [], ?string $className = null)
 * @method  object   mustFindOne(string $entityClass, mixed $conditions = [], ?string $className = null)
 * @method  ResultIterator findList(string $entityClass, mixed $conditions = [], ?string $className = null)
 * @method  string|null   findResult(string $entityClass, string|RawWrapper $column, mixed $conditions = [])
 * @method  Collection   findColumn(string $entityClass, string $column, mixed $conditions = [])
 * @method  int   countColumn(string $entityClass, string $column, mixed $conditions = [], array|string $groups = null)
 * @method  float   sumColumn(string $entityClass, string $column, mixed $conditions = [], array|string $groups = null)
 * @method  object  createOne(string $entityClass, array|object $item = [])
 * @method  iterable      createMultiple(string $entityClass, iterable $items)
 * @method  StatementInterface|null  updateOne(string $entityClass, array|object $item = [], array|string $condFields = null, int $options = 0)
 * @method  object[]      updateMultiple(string $entityClass, iterable $items, array|string $condFields = null, int $options = 0)
 * @method  StatementInterface  updateWhere(string $entityClass, array|object $data, mixed $conditions = null)
 * @method  StatementInterface[]  updateBatch(string $entityClass, array|object $data, mixed $conditions = null, int $options = 0)
 * @method  iterable|object[] saveMultiple(string $entityClass, iterable $items, string|array $condFields = null, int $options = 0)
 * @method  object        saveOne(string $entityClass, array|object $item, array|string $condFields = null, int $options = 0)
 * @method  object        findOneOrCreate(string $entityClass, mixed $conditions, mixed $initData = null, bool $mergeConditions = true)
 * @method  object        updateOneOrCreate(string $entityClass, array|object $item, mixed $initData = null, ?array $condFields = null, int $options = 0)
 * @method  StatementInterface[]  deleteWhere(string $entityClass, mixed $conditions)
 * @method  iterable|object[]     flush(string $entityClass, iterable $items, mixed $conditions = [])
 * @method  StatementInterface[]  sync(string $entityClass, iterable $items, mixed $conditions = [], ?array $compareKeys = null)
 *
 * phpcs:enable
 */
class ORM implements EventAwareInterface
{
    use EventAwareTrait;

    use AttributesAwareTrait;

    protected DatabaseAdapter $db;

    protected ?FieldHydratorInterface $hydrator = null;

    protected EntityMetadataCollection $entityMetadataCollection;

    /**
     * ORM constructor.
     *
     * @param  DatabaseAdapter  $db
     */
    public function __construct(DatabaseAdapter $db)
    {
        $this->db = $db;

        $this->entityMetadataCollection = new EntityMetadataCollection($this);

        $this->setAttributesResolver(new AttributesResolver());
    }

    /**
     * setAttributesResolver
     *
     * @param  AttributesResolver  $attributesResolver
     *
     * @return  static
     */
    public function setAttributesResolver(AttributesResolver $attributesResolver): static
    {
        $this->initAttributeResolver($attributesResolver);

        $this->attributeResolver = $attributesResolver;

        return $this;
    }

    protected function initAttributeResolver(AttributesResolver $ar): void
    {
        $ar->setOption('orm', $this);

        $ar->registerAttribute(AutoIncrement::class, Attribute::TARGET_PROPERTY);
        $ar->registerAttribute(Cast::class, Attribute::TARGET_PROPERTY);
        $ar->registerAttribute(CastNullable::class, Attribute::TARGET_PROPERTY);
        $ar->registerAttribute(Column::class, Attribute::TARGET_PROPERTY);
        $ar->registerAttribute(Mapping::class, Attribute::TARGET_PROPERTY);
        $ar->registerAttribute(PK::class, Attribute::TARGET_PROPERTY);

        $ar->registerAttribute(OneToOne::class, Attribute::TARGET_PROPERTY);
        $ar->registerAttribute(OneToMany::class, Attribute::TARGET_PROPERTY);
        $ar->registerAttribute(ManyToOne::class, Attribute::TARGET_PROPERTY);
        $ar->registerAttribute(ManyToMany::class, Attribute::TARGET_PROPERTY);

        $ar->registerAttribute(Table::class, Attribute::TARGET_CLASS);
        $ar->registerAttribute(NestedSet::class, Attribute::TARGET_CLASS);

        $ar->registerAttribute(EntitySetup::class, Attribute::TARGET_METHOD);
        $ar->registerAttribute(BeforeSaveEvent::class, Attribute::TARGET_METHOD);
        $ar->registerAttribute(AfterSaveEvent::class, Attribute::TARGET_METHOD);
        $ar->registerAttribute(BeforeUpdateWhereEvent::class, Attribute::TARGET_METHOD);
        $ar->registerAttribute(AfterUpdateWhereEvent::class, Attribute::TARGET_METHOD);
        $ar->registerAttribute(BeforeDeleteEvent::class, Attribute::TARGET_METHOD);
        $ar->registerAttribute(AfterDeleteEvent::class, Attribute::TARGET_METHOD);
        $ar->registerAttribute(BeforeCopyEvent::class, Attribute::TARGET_METHOD);
        $ar->registerAttribute(AfterCopyEvent::class, Attribute::TARGET_METHOD);
        $ar->registerAttribute(BeforeStoreEvent::class, Attribute::TARGET_METHOD);
        $ar->registerAttribute(Watch::class, Attribute::TARGET_METHOD);
        $ar->registerAttribute(WatchBefore::class, Attribute::TARGET_METHOD);
    }

    /**
     * @template K
     *
     * @param  class-string<K> $entityClass
     * @param  ?string         $mapperClass
     *
     * @return  EntityMapper<K>
     * @throws ReflectionException
     */
    public function mapper(string $entityClass, ?string $mapperClass = null): EntityMapper
    {
        return $this->getEntityMetadata($entityClass)->getEntityMapper($mapperClass);
    }

    /**
     * @template K
     *
     * @param  string   $entityClass
     * @param  ?string  $mapperClass
     *
     * @return  EntityMapper<K>
     * @throws ReflectionException
     */
    public function __invoke(string $entityClass, ?string $mapperClass = null): EntityMapper
    {
        return $this->mapper($entityClass, $mapperClass);
    }

    public function from(mixed $tables, ?string $alias = null): SelectorQuery
    {
        if (is_string($tables) && class_exists($tables)) {
            return $this->mapper($tables)->from($tables, $alias);
        }

        return $this->createSelectorQuery()->from($tables, $alias);
    }

    public function select(...$columns): SelectorQuery
    {
        return $this->createSelectorQuery()->select(...$columns);
    }

    protected function createSelectorQuery(): SelectorQuery
    {
        return new SelectorQuery($this);
    }

    public function insert(string $table, bool $incrementField = false): Query
    {
        if (is_string($table) && class_exists($table)) {
            return $this->mapper($table)->insert($incrementField);
        }

        return $this->createSelectorQuery()->insert($table, $incrementField);
    }

    public function update(string $table, ?string $alias = null): Query
    {
        if (is_string($table) && class_exists($table)) {
            return $this->mapper($table)->update($alias);
        }

        return $this->createSelectorQuery()->update($table, $alias);
    }

    public function delete(string $table, ?string $alias = null): Query
    {
        if (is_string($table) && class_exists($table)) {
            return $this->mapper($table)->delete($alias);
        }

        return $this->createSelectorQuery()->delete($table, $alias);
    }

    public function prepareRelations(object $entity): object
    {
        return $this->mapper($entity::class)->prepareRelations($entity);
    }

    /**
     * createEntity
     *
     * @template T
     *
     * @param  class-string<T>  $entityClass
     *
     * @return  T
     *
     * @throws ReflectionException
     */
    public function createEntity(string $entityClass, array $data = []): object
    {
        $entity = $this->mapper($entityClass)->createEntity();

        if ($data !== []) {
            $entity = $this->hydrateEntity($data, $entity);
        }

        return $entity;
    }

    /**
     * hydrateEntity
     *
     * @template T
     *
     * @param  array     $data
     * @param  object|T  $entity
     *
     * @return  object|T
     * @throws ReflectionException
     */
    public function hydrateEntity(array $data, object $entity): object
    {
        $class = $entity::class;
        $item = $data;

        $event = $this->emit(
            HydrateEvent::class,
            compact('class', 'item')
        );

        return $this->getEntityHydrator()->hydrate($event->getItem(), $entity);
    }

    /**
     * @template T
     *
     * @param  string|T      $entityClass
     * @param  array|object  $data
     *
     * @return  object|T
     *
     * @throws ReflectionException
     */
    public function toEntity(string $entityClass, array|object $data): object
    {
        return $this->mapper($entityClass)->toEntity($data);
    }

    public function extractEntity(array|object|null $entity): array
    {
        if ($entity === null) {
            return [];
        }

        if (is_array($entity)) {
            return $entity;
        }

        if ($entity instanceof Collection) {
            return $entity->dump();
        }

        return $this->getEntityHydrator()->extract($entity);
    }

    public function extractField(array|object $entity, string $field): mixed
    {
        if (is_array($entity)) {
            return $entity[$field];
        }

        return $this->getEntityHydrator()->extractField($entity, $field);
    }

    public function toCollection(object|array $entity): Collection
    {
        if ($entity instanceof Collection) {
            return $entity;
        }

        if (is_array($entity) || !EntityMetadata::isEntity($entity)) {
            return Collection::wrap($entity);
        }

        return $this->mapper($entity::class)->toCollection($entity);
    }

    public function getEntityClass(string|object $entity): string
    {
        if (is_object($entity)) {
            $entity = $entity::class;
        }

        return $entity;
    }

    public function getEntityMetadata(string|object $entity): EntityMetadata
    {
        return $this->getEntityMetadataCollection()->get($entity);
    }

    public static function isEntity(object|string $entity): bool
    {
        return EntityMetadata::isEntity($entity);
    }

    /**
     * @return DatabaseAdapter
     */
    public function getDb(): DatabaseAdapter
    {
        return $this->db;
    }

    /**
     * @param  DatabaseAdapter  $db
     *
     * @return  static  Return self to support chaining.
     */
    public function setDb(DatabaseAdapter $db): static
    {
        $this->db = $db;

        return $this;
    }

    /**
     * @return EntityMetadataCollection
     */
    public function getEntityMetadataCollection(): EntityMetadataCollection
    {
        return $this->entityMetadataCollection;
    }

    /**
     * @param  EntityMetadataCollection  $entityMetadataCollection
     *
     * @return  static  Return self to support chaining.
     */
    public function setEntityMetadataCollection(EntityMetadataCollection $entityMetadataCollection): static
    {
        $this->entityMetadataCollection = $entityMetadataCollection;

        return $this;
    }

    /**
     * @return FieldHydratorInterface
     * @throws ReflectionException
     */
    public function getEntityHydrator(): FieldHydratorInterface
    {
        return $this->hydrator ??= $this->getAttributesResolver()
            ->createObject(
                EntityHydrator::class,
                hydrator: $this->getDb()->getHydrator(),
                orm: $this
            );
    }

    /**
     * @param  FieldHydratorInterface|null  $hydrator
     *
     * @return  static  Return self to support chaining.
     */
    public function setEntityHydrator(?FieldHydratorInterface $hydrator): static
    {
        $this->hydrator = $hydrator;

        return $this;
    }

    public function countWith(Query|string $query): int
    {
        return $this->db->countWith($query);
    }

    public function __call(string $name, array $args = []): mixed
    {
        if (method_exists(EntityMapper::class, $name)) {
            $entity = array_shift($args);

            return $this->mapper($entity)->$name(...$args);
        }

        throw new BadMethodCallException(
            sprintf(
                'Call to undefined method %s::%s()',
                static::class,
                $name
            )
        );
    }
}
