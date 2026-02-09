<?php

declare(strict_types=1);

namespace Windwalker\ORM;

use Asika\ObjectMetadata\ObjectMetadata;
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
use Windwalker\ORM\Attributes\JsonArray;
use Windwalker\ORM\Attributes\JsonObject;
use Windwalker\ORM\Attributes\ManyToMany;
use Windwalker\ORM\Attributes\ManyToOne;
use Windwalker\ORM\Attributes\Mapping;
use Windwalker\ORM\Attributes\NestedSet;
use Windwalker\ORM\Attributes\OneToMany;
use Windwalker\ORM\Attributes\OneToOne;
use Windwalker\ORM\Attributes\OptimisticLock;
use Windwalker\ORM\Attributes\PK;
use Windwalker\ORM\Attributes\Table;
use Windwalker\ORM\Attributes\Watch;
use Windwalker\ORM\Attributes\WatchBefore;
use Windwalker\ORM\Event\AfterCopyEvent;
use Windwalker\ORM\Event\AfterCreateBulkEvent;
use Windwalker\ORM\Event\AfterDeleteEvent;
use Windwalker\ORM\Event\AfterSaveEvent;
use Windwalker\ORM\Event\AfterUpdateWhereEvent;
use Windwalker\ORM\Event\BeforeCopyEvent;
use Windwalker\ORM\Event\BeforeCreateBulkEvent;
use Windwalker\ORM\Event\BeforeDeleteEvent;
use Windwalker\ORM\Event\BeforeSaveEvent;
use Windwalker\ORM\Event\BeforeStoreEvent;
use Windwalker\ORM\Event\BeforeUpdateWhereEvent;
use Windwalker\ORM\Event\EnergizeEvent;
use Windwalker\ORM\Hydrator\EntityHydrator;
use Windwalker\ORM\Iterator\ResultIterator;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\Metadata\EntityMetadataCollection;
use Windwalker\Query\Query;
use Windwalker\Utilities\Wrapper\RawWrapper;

/**
 * The ORM class.
 */
class ORM implements EventAwareInterface
{
    use EventAwareTrait;
    use AttributesAwareTrait;
    use EntityMapperConstantsTrait;
    use ORMProxyTrait;

    protected ?FieldHydratorInterface $hydrator = null;

    protected EntityMetadataCollection $entityMetadataCollection;

    protected BaseCaster $caster;

    /**
     * ORM constructor.
     *
     * @param  DatabaseAdapter  $db
     */
    public function __construct(protected DatabaseAdapter $db)
    {
        $this->entityMetadataCollection = new EntityMetadataCollection($this);

        $this->setAttributesResolver(new AttributesResolver());

        $this->caster ??= new BaseCaster($this->db);
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
        $ar->registerAttribute(JsonObject::class, Attribute::TARGET_PROPERTY);
        $ar->registerAttribute(JsonArray::class, Attribute::TARGET_PROPERTY);
        $ar->registerAttribute(Column::class, Attribute::TARGET_PROPERTY);
        $ar->registerAttribute(Mapping::class, Attribute::TARGET_PROPERTY);
        $ar->registerAttribute(PK::class, Attribute::TARGET_PROPERTY);
        $ar->registerAttribute(OptimisticLock::class, Attribute::TARGET_PROPERTY);

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
        $ar->registerAttribute(BeforeCreateBulkEvent::class, Attribute::TARGET_METHOD);
        $ar->registerAttribute(AfterCreateBulkEvent::class, Attribute::TARGET_METHOD);
        $ar->registerAttribute(EnergizeEvent::class, Attribute::TARGET_METHOD);
        $ar->registerAttribute(Watch::class, Attribute::TARGET_METHOD);
        $ar->registerAttribute(WatchBefore::class, Attribute::TARGET_METHOD);
    }

    /**
     * @template K
     *
     * @param  class-string<K>  $entityClass
     * @param  ?string          $mapperClass
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

        return $this->createQuery()->from($tables, $alias);
    }

    public function select(...$columns): SelectorQuery
    {
        return $this->createQuery()->select(...$columns);
    }

    public function selectRaw(mixed $column, ...$args): SelectorQuery
    {
        return $this->createQuery()->selectRaw($column, ...$args);
    }

    public function createBaseQuery(): Query
    {
        return $this->getDb()->createQuery();
    }

    public function createQuery(): SelectorQuery
    {
        return new SelectorQuery($this);
    }

    public function insert(string $table, bool $incrementField = false): Query
    {
        if (is_string($table) && class_exists($table)) {
            return $this->mapper($table)->insert($incrementField);
        }

        return $this->createQuery()->insert($table, $incrementField);
    }

    public function update(string $table, ?string $alias = null): Query
    {
        if (is_string($table) && class_exists($table)) {
            return $this->mapper($table)->update($alias);
        }

        return $this->createQuery()->update($table, $alias);
    }

    public function delete(string $table, ?string $alias = null): Query
    {
        if (is_string($table) && class_exists($table)) {
            return $this->mapper($table)->delete($alias);
        }

        return $this->createQuery()->delete($table, $alias);
    }

    public function prepareRelations(object $entity): object
    {
        return $this->mapper($entity::class)->prepareRelations($entity);
    }

    /**
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
        /** @var T $entity */
        $entity = $this->mapper($entityClass)->createEntity(...$data);

        return $entity;
    }

    /**
     * @template T
     *
     * @param  class-string<T>  $entityClass
     *
     * @return  T
     *
     * @throws ReflectionException
     */
    public function createEntityArgs(string $entityClass, mixed ...$args): object
    {
        return $this->createEntity($entityClass, $args);
    }

    /**
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
            new HydrateEvent(
                item: $item,
                class: $class,
            )
        );

        /** @var T $entity */
        $entity = $this->getEntityHydrator()->hydrate($event->item, $entity);

        if (static::isEntity($entity)) {
            $entity = $this->energize($entity);
        }

        return $entity;
    }

    public function isEnergized(object $entity): bool
    {
        return $this->mapper($entity::class)->isEnergized($entity);
    }

    /**
     * @template T of object
     *
     * @param  T     $entity
     * @param  bool  $force
     *
     * @return  T
     *
     * @throws ReflectionException
     */
    public function energize(object $entity, bool $force = false): object
    {
        /** @var T $entity */
        $entity = $this->mapper($entity::class)->energize($entity, $force);

        return $entity;
    }

    public function unenergize(object $entity): static
    {
        $entity = $this->mapper($entity::class)->unenergize($entity);

        return $this;
    }

    /**
     * @template E
     *
     * @param  class-string<E>  $entityClass
     * @param  array|object     $data
     *
     * @return  E
     *
     * @throws ReflectionException
     */
    public function toEntity(string $entityClass, array|object $data): object
    {
        /** @var E $entity */
        $entity = $this->mapper($entityClass)->toEntity($data);

        return $entity;
    }

    /**
     * @template E
     *
     * @param  class-string<E>    $entityClass
     * @param  array|object|null  $data
     *
     * @return  E|null
     *
     * @throws ReflectionException
     *
     * @deprecated  Use tryEntity() instead.
     */
    public function toEntityOrNull(string $entityClass, array|object|null $data): ?object
    {
        /** @var E $entity */
        $entity = $this->mapper($entityClass)->tryEntity($data);

        return $entity;
    }

    /**
     * @template E
     *
     * @param  class-string<E>    $entityClass
     * @param  array|object|null  $data
     *
     * @return  E|null
     *
     * @throws ReflectionException
     */
    public function tryEntity(string $entityClass, array|object|null $data): ?object
    {
        /** @var E $entity */
        $entity = $this->mapper($entityClass)->tryEntity($data);

        return $entity;
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
     * @param  callable  $callback
     * @param  bool      $autoCommit
     * @param  bool      $enabled
     *
     * @return  mixed
     *
     * @throws \Throwable
     */
    public function transaction(callable $callback, bool $autoCommit = true, bool $enabled = true): mixed
    {
        return $this->db->transaction($callback, $autoCommit, $enabled);
    }

    public function isInTransaction(): bool
    {
        return $this->db->isInTransaction();
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

            $maps = [
                'createone',
                'createmultiple',
                'updateone',
                'updatemultiple',
                'saveone',
                'savemultiple',
                'savemultiple',
            ];

            if (
                is_object($entity)
                && in_array(strtolower($name), $maps, true)
            ) {
                $entityClass = $entity::class;

                return $this->mapper($entityClass)->$name($entity, ...$args);
            }

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

    public function getCaster(): BaseCaster
    {
        return $this->caster;
    }

    /**
     * @param  BaseCaster  $caster
     *
     * @return  static  Return self to support chaining.
     */
    public function setCaster(BaseCaster $caster): static
    {
        $this->caster = $caster;

        return $this;
    }

    public static function getObjectMetadata(): ObjectMetadata
    {
        return EntityMapper::getObjectMetadata();
    }
}
