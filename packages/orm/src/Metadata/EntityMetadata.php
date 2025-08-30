<?php

declare(strict_types=1);

namespace Windwalker\ORM\Metadata;

use Closure;
use InvalidArgumentException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use Windwalker\Attributes\AttributeHandler;
use Windwalker\Event\EventAwareInterface;
use Windwalker\Event\EventAwareTrait;
use Windwalker\ORM\Attributes\{CastAttributeInterface, Column, Mapping, OptimisticLockInterface, PK, Table, Watch};
use Windwalker\ORM\Cast\CastManager;
use Windwalker\ORM\EntityMapper;
use Windwalker\ORM\Event\AfterSaveEvent;
use Windwalker\ORM\Event\AfterUpdateWhereEvent;
use Windwalker\ORM\Event\BeforeSaveEvent;
use Windwalker\ORM\Event\BeforeUpdateWhereEvent;
use Windwalker\ORM\ORM;
use Windwalker\ORM\Relation\RelationManager;
use Windwalker\Utilities\Cache\InstanceCacheTrait;
use Windwalker\Utilities\Options\OptionAccessTrait;
use Windwalker\Utilities\Reflection\ReflectAccessor;
use Windwalker\Utilities\StrNormalize;

/**
 * The EntityMetadata class.
 */
class EntityMetadata implements EventAwareInterface
{
    use EventAwareTrait;
    use OptionAccessTrait;
    use InstanceCacheTrait;

    protected string $className;

    protected ?string $tableName = null;

    protected ?string $tableAlias = null;

    protected string $mapperClass = EntityMapper::class;

    protected ?Column $aiColumn = null;

    /**
     * @var PK[]
     */
    protected array $keys = [];

    /**
     * @var EntityMember[]
     */
    public protected(set) array $propertyMembers;

    /**
     * @var EntityMember[]
     */
    public protected(set) array $methodMembers;

    protected CastManager $castManager;

    protected RelationManager $relationManager;

    /**
     * @var ORM
     */
    protected ORM $orm;

    protected bool $hasSetup = false;

    /**
     * EntityMetadata constructor.
     *
     * @param  string|object  $entity
     * @param  ORM            $orm
     */
    public function __construct(string|object $entity, ORM $orm)
    {
        if (is_object($entity)) {
            $entity = $entity::class;
        }

        $this->orm = $orm;
        $this->className = $entity;
        $this->castManager = new CastManager();
        $this->relationManager = new RelationManager($this);

        $this->addEventDealer($orm);

        $this->propertyMembers = array_map(
            static fn(ReflectionProperty $prop) => new EntityMember($prop),
            ReflectAccessor::getReflectProperties(
                $this->className,
                ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE
            )
        );

        $this->methodMembers = array_map(
            static fn(ReflectionMethod $method) => new EntityMember($method),
            ReflectAccessor::getReflectMethods(
                $this->className,
                ReflectionMethod::IS_STATIC | ReflectionMethod::IS_PROTECTED | ReflectionMethod::IS_PUBLIC
            )
        );
    }

    public static function isEntity(string|object|array $object): bool
    {
        if (is_array($object)) {
            return false;
        }

        $class = new ReflectionClass($object);

        return $class->getAttributes(Table::class, ReflectionAttribute::IS_INSTANCEOF) !== [];
    }

    public function setup(): static
    {
        if ($this->hasSetup) {
            return $this;
        }

        $resolver = $this->getORM()->getAttributesResolver();

        $ref = $this->getReflector();

        $resolver->resolveObjectDecorate($ref, ['metadata' => $this]);

        if (!$this->tableName) {
            throw new InvalidArgumentException(
                sprintf(
                    '%s has no table info.',
                    $this->className
                )
            );
        }

        $resolver->resolveObjectMembers($ref, ['metadata' => $this]);

        $this->hasSetup = true;

        return $this;
    }

    public function addMember(ReflectionProperty|ReflectionMethod $ref, ?Column $column = null): static
    {
        if ($ref instanceof ReflectionProperty) {
            // Init
            $this->getProperties();

            $this->propertyMembers[$ref->getName()] = new EntityMember($ref, $column);
        } else {
            // Init
            $this->getMethods();

            $this->methodMembers[$ref->getName()] = new EntityMember($ref);
        }

        return $this;
    }

    /**
     * @param  string  $attr
     * @param  ReflectionProperty|ReflectionMethod  $ref
     *
     * @return  void
     */
    public function addAttributeMap(string|object $attr, ReflectionProperty|ReflectionMethod $ref): void
    {
        if (is_string($attr)) {
            $attr = new $attr();
        }

        if ($ref instanceof ReflectionProperty) {
            $this->propertyMembers[$ref->getName()]->addAttribute($attr);
        } else {
            // Init
            $this->methodMembers[$ref->getName()]->addAttribute($attr);
        }
    }

    /**
     * @param  string  $attributeClass
     *
     * @return  array<EntityMember>
     */
    public function getMethodMembersOfAttribute(string $attributeClass): array
    {
        return array_filter(
            $this->methodMembers,
            fn(EntityMember $member) => $member->hasAttribute($attributeClass)
        );
    }

    /**
     * @param  string  $attributeClass
     *
     * @return  array<EntityMember>
     */
    public function getPropertyMembersOfAttribute(string $attributeClass): array
    {
        return array_filter(
            $this->propertyMembers,
            fn(EntityMember $member) => $member->hasAttribute($attributeClass)
        );
    }

    public function getOptimisticLock(): ?OptimisticLockInterface
    {
        foreach ($this->propertyMembers as $member) {
            $attr = $member->getAttribute(OptimisticLockInterface::class);

            if ($attr) {
                return $attr;
            }
        }

        return null;
    }

    public function getColumnByPropertyName(string $propName): ?Column
    {
        return $this->propertyMembers[$propName]?->column;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getTableName(): string
    {
        if ($this->tableName) {
            return $this->tableName;
        }

        $tableAttr = $this->getReflector()
            ->getAttributes(Table::class, ReflectionAttribute::IS_INSTANCEOF)[0]?->newInstance();

        if (!$tableAttr) {
            throw new InvalidArgumentException(
                sprintf(
                    '%s has no table info.',
                    $this->className
                )
            );
        }

        return $this->tableName = $tableAttr->getName();
    }

    public function cast(
        string $field,
        mixed $cast,
        mixed $extract = null,
        int $options = 0
    ): static {
        $this->getCastManager()->addCast(
            $field,
            $cast,
            $extract,
            $options
        );

        return $this;
    }

    public function castByAttribute(AttributeHandler $handler, CastAttributeInterface $castAttribute): void
    {
        /** @var ReflectionProperty $prop */
        $prop = $handler->getReflector();

        $column = $this->getColumnByPropertyName($prop->getName());

        $colName = $column ? $column->getName() : $prop->getName();

        $this->cast(
            $colName,
            $castAttribute->getHydrate(),
            $castAttribute->getExtract(),
            $castAttribute->getOptions()
        );
    }

    public function getMainKey(): ?string
    {
        $pks = [];

        foreach ($this->getKeysAttrs() as $name => $pk) {
            if ($pk->isPrimary()) {
                return $pk->getColumn()->getName();
            }

            $pks[] = $name;
        }

        return $pks[0] ?? null;
    }

    /**
     * getKeys
     *
     * @return  string[]
     */
    public function getKeys(): array
    {
        return array_keys($this->getKeysAttrs());
    }

    public function getAutoIncrementColumn(): ?Column
    {
        return $this->aiColumn;
    }

    /**
     * getKeysReflectors
     *
     * @return  PK[]
     */
    protected function getKeysAttrs(): array
    {
        return $this->keys;
    }

    /**
     * @return  array<int, ReflectionMethod>
     *
     * @throws ReflectionException
     */
    public function getMethods(): array
    {
        return array_map(
            static fn(EntityMember $member) => $member->memberRef,
            $this->methodMembers
        );
    }

    public function getMethod(string $name): ?ReflectionMethod
    {
        return $this->getMethods()[$name] ?? null;
    }

    public function getMethodMember(string $name): ?ReflectionMethod
    {
        return $this->methodMembers[$name] ?? null;
    }

    /**
     * @return  array<int, ReflectionProperty>
     * @throws ReflectionException
     */
    public function getProperties(): array
    {
        return array_map(
            static fn(EntityMember $member) => $member->memberRef,
            $this->propertyMembers
        );
    }

    public function getProperty(string $name): ?ReflectionProperty
    {
        return $this->getProperties()[$name] ?? null;
    }

    public function getPropertyMember(string $name): ?EntityMember
    {
        return $this->propertyMembers[$name] ?? null;
    }

    /**
     * getColumns
     *
     * @return Column[]
     */
    public function getColumns(): array
    {
        return \Windwalker\collect($this->propertyMembers)
            ->filter(fn(EntityMember $member) => $member->column !== null)
            ->mapWithKeys(fn(EntityMember $member) => yield $member->columnName => $member->column)
            ->dump();
    }

    public function getPureColumns(): array
    {
        return $this->once(
            'pure.cols',
            function () {
                $cols = [];

                foreach ($this->getColumns() as $name => $column) {
                    if (!$column instanceof Mapping) {
                        $cols[$name] = $column;
                    }
                }

                return $cols;
            }
        );
    }

    public function getColumn(string $name, bool $fixCase = false): ?Column
    {
        $col = $this->getColumns()[$name] ?? null;

        if ($fixCase && !$col && strpbrk($name, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ') !== false) {
            $key = $this->columnNameCamelCase($name);
            $col = $this->getColumn($key);
        }

        return $col;
    }

    /**
     * ReflectionClass creation is very fast that no need to cache it.
     *
     * @return  ReflectionClass
     *
     * @throws ReflectionException
     */
    public function getReflector(): ReflectionClass
    {
        return new ReflectionClass($this->className);
    }

    /**
     * @return CastManager
     */
    public function getCastManager(): CastManager
    {
        return $this->castManager;
    }

    /**
     * @param  CastManager  $castManager
     *
     * @return  static  Return self to support chaining.
     */
    public function setCastManager(CastManager $castManager): static
    {
        $this->castManager = $castManager;

        return $this;
    }

    /**
     * @return ORM
     */
    public function getORM(): ORM
    {
        return $this->orm;
    }

    public function getEntityMapper(?string $mapperClass = null): EntityMapper
    {
        $args = [
            $this,
            $orm = $this->getORM(),
        ];

        $mapperClass ??= $this->getMapperClass();

        if ($mapperClass === EntityMapper::class) {
            $mapper = new EntityMapper(...$args);
        } else {
            $mapper = $orm->getAttributesResolver()->createObject(
                $mapperClass,
                ...$args
            );
        }

        $mapper->addEventDealer($this);

        return $mapper;
    }

    /**
     * @param  ORM  $orm
     *
     * @return  static  Return self to support chaining.
     */
    public function setORM(ORM $orm): static
    {
        $this->orm = $orm;

        return $this;
    }

    /**
     * @return RelationManager
     */
    public function getRelationManager(): RelationManager
    {
        return $this->relationManager;
    }

    /**
     * @param  RelationManager  $relationManager
     *
     * @return  static  Return self to support chaining.
     */
    public function setRelationManager(RelationManager $relationManager): static
    {
        $this->relationManager = $relationManager;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTableAlias(): ?string
    {
        return $this->tableAlias;
    }

    /**
     * @return string
     */
    public function getMapperClass(): string
    {
        return $this->mapperClass;
    }

    public function watch(string $column, callable $method, int $options = 0): Closure
    {
        $unwatches = [];

        if ($options & Watch::BEFORE_SAVE) {
            $this->on(
                BeforeSaveEvent::class,
                $unwatches[BeforeSaveEvent::class] = function (BeforeSaveEvent $event) use (
                    $column,
                    $options,
                    $method
                ) {
                    if (!($options & Watch::INCLUDE_CREATE) && $event->type === BeforeSaveEvent::TYPE_CREATE) {
                        return;
                    }

                    $val = $event->data[$column] ?? null;
                    $oldVal = $event->oldData[$column] ?? null;

                    if (is_scalar($val) && is_scalar($oldVal)) {
                        $isSame = (string) $val === (string) $oldVal;
                    } else {
                        $isSame = $val === $oldVal;
                    }

                    if (!$isSame) {
                        $watchEvent = Watch::createWatchEvent($event, $val, $oldVal);

                        $this->getORM()->getAttributesResolver()
                            ->call(
                                $method,
                                [
                                    $watchEvent::class => $watchEvent,
                                    'event' => $watchEvent,
                                ]
                            );

                        $event->extra = $watchEvent->extra;
                    }
                }
            );

            if ($options & Watch::INCLUDE_UPDATE_WHERE) {
                $this->on(
                    BeforeUpdateWhereEvent::class,
                    $unwatches[BeforeUpdateWhereEvent::class] = function (BeforeUpdateWhereEvent $event) use (
                        $column,
                        $method
                    ) {
                        $val = $event->data[$column] ?? null;

                        $watchEvent = Watch::createWatchEvent($event, $val);

                        $this->getORM()->getAttributesResolver()
                            ->call(
                                $method,
                                [
                                    $watchEvent::class => $watchEvent,
                                    'event' => $watchEvent,
                                ]
                            );
                    }
                );
            }
        } else {
            $this->on(
                AfterSaveEvent::class,
                $unwatches[AfterSaveEvent::class] = function (AfterSaveEvent $event) use ($column, $options, $method) {
                    if (!($options & Watch::INCLUDE_CREATE) && $event->type === AfterSaveEvent::TYPE_CREATE) {
                        return;
                    }

                    $val = $event->data[$column] ?? null;
                    $oldVal = $event->oldData[$column] ?? null;

                    if (is_scalar($val) && is_scalar($oldVal)) {
                        $isSame = (string) $val === (string) $oldVal;
                    } else {
                        $isSame = $val === $oldVal;
                    }

                    if (!$isSame) {
                        $watchEvent = Watch::createWatchEvent($event, $val, $oldVal);

                        $this->getORM()->getAttributesResolver()
                            ->call(
                                $method,
                                [
                                    $watchEvent::class => $watchEvent,
                                    'event' => $watchEvent,
                                ]
                            );

                        $event->extra = $watchEvent->extra;
                    }
                }
            );

            if ($options & Watch::INCLUDE_UPDATE_WHERE) {
                $this->on(
                    AfterUpdateWhereEvent::class,
                    $unwatches[AfterUpdateWhereEvent::class] = function (AfterUpdateWhereEvent $event) use (
                        $column,
                        $method
                    ) {
                        $val = $event->data[$column] ?? null;

                        $watchEvent = Watch::createWatchEvent($event, $val);

                        $this->getORM()->getAttributesResolver()
                            ->call(
                                $method,
                                [
                                    $watchEvent::class => $watchEvent,
                                    'event' => $watchEvent,
                                ]
                            );
                    }
                );
            }
        }

        return function () use ($unwatches) {
            foreach ($unwatches as $event => $listener) {
                $this->getEventDispatcher()->off($event, $listener);
            }
        };
    }

    public function watchBefore(string $column, callable $method, int $options = 0): Closure
    {
        return $this->watch($column, $method, $options | Watch::BEFORE_SAVE);
    }

    public function watchAfter(string $column, callable $method, int $options = 0): Closure
    {
        return $this->watch($column, $method, $options & ~Watch::BEFORE_SAVE);
    }

    /**
     * @return object|null
     */
    public function getCachedEntity(): ?object
    {
        return $this->cacheStorage['entity'] ?? null;
    }

    /**
     * @param  object|null  $cachedEntity
     *
     * @return  static  Return self to support chaining.
     */
    public function setCachedEntity(?object $cachedEntity): static
    {
        $this->cacheStorage['entity'] = $cachedEntity;

        return $this;
    }

    protected function columnNameCamelCase(string $colName): string
    {
        return $this->cacheStorage['colName:' . $colName] ??= StrNormalize::toSnakeCase($colName);
    }
}
