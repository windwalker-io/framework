<?php

declare(strict_types=1);

namespace Windwalker\ORM;

use Asika\ObjectMetadata\ObjectMetadata;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use Windwalker\Attributes\AttributesAccessor;
use Windwalker\Data\Collection;
use Windwalker\ORM\Attributes\JsonSerializer;
use Windwalker\ORM\Attributes\JsonSerializerInterface;
use Windwalker\ORM\Attributes\Table;
use Windwalker\ORM\Relation\RelationCollection;
use Windwalker\ORM\Relation\RelationProxies;
use Windwalker\ORM\Relation\Strategy\RelationStrategyInterface;
use Windwalker\Utilities\Accessible\AccessorBCTrait;
use Windwalker\Utilities\StrNormalize;
use Windwalker\Utilities\TypeCast;

/**
 * The AbstractEntity class.
 */
trait EntityTrait
{
    use AccessorBCTrait {
        AccessorBCTrait::__call as accessorCall;
    }

    public static function table(): ?string
    {
        return (new ReflectionClass(static::class))
            ->getAttributes(Table::class, ReflectionAttribute::IS_INSTANCEOF)[0]
            ?->newInstance()
            ?->getName();
    }

    public static function newInstance(array $data = []): static
    {
        $instance = new static();

        foreach ($data as $k => $datum) {
            $instance->$k = $datum;
        }

        return $instance;
    }

    public static function create(...$args): static
    {
        return static::newInstance($args);
    }

    protected function retrieveMeta(string $key): mixed
    {
        return EntityMapper::getObjectMetadata()->get($this, $key);
    }

    /**
     * @param  string  $propName
     *
     * @return  mixed
     *
     * @deprecated  Use `$this->props ??= fetchRelation(...)` instead.
     */
    protected function loadRelation(string $propName): mixed
    {
        return $this->$propName ??= $this->fetchRelation($propName);
    }

    protected function fetchRelation(string $propName): mixed
    {
        return RelationProxies::call($this, $propName);
    }

    /**
     * @param  string  $propName
     *
     * @return  mixed|RelationCollection
     *
     * @deprecated  Use `$this->props ??= fetchCollection(...)` instead.
     */
    protected function loadCollection(string $propName)
    {
        return $this->$propName ??= $this->fetchCollection($propName);
    }

    protected function fetchCollection(string $propName)
    {
        return RelationProxies::call($this, $propName)
            ?? new RelationCollection(
                static::class,
                null
            );
    }

    public function loadAllRelations(): void
    {
        foreach ($this->dump() as $prop => $value) {
            if ($value === null && RelationProxies::has($this, $prop)) {
                $this->$prop = $this->loadRelation($prop);
            }
        }
    }

    public function clearRelations(): void
    {
        foreach ($this->dump() as $prop => $value) {
            $ref = new ReflectionProperty($this, $prop);
            $attrs = $ref->getAttributes(RelationStrategyInterface::class, ReflectionAttribute::IS_INSTANCEOF);

            if ($attrs) {
                $this->$prop = null;
            }
        }
    }

    public function toCollection(?ORM $orm = null): Collection
    {
        if ($orm) {
            return $orm->toCollection($this);
        }

        return Collection::wrap($this->dump());
    }

    /**
     * Dump to array. but keep properties types.
     *
     * @inheritDoc
     */
    public function dump(bool $recursive = false, bool $onlyDumpable = false): array
    {
        $ref = new \ReflectionObject($this);
        $data = [];

        foreach (get_object_vars($this) as $k => $v) {
            if ($ref->hasProperty($k) && $ref->getProperty($k)->isVirtual()) {
                continue;
            }

            $data[$k] = $v;
        }

        return TypeCast::toArray($data, $recursive, $onlyDumpable);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        $item = $this->dump();

        foreach ($item as $key => $value) {
            $prop = new ReflectionProperty($this, $key);
            $attrs = AttributesAccessor::getAttributesFromAny(
                $prop,
                JsonSerializerInterface::class,
                ReflectionAttribute::IS_INSTANCEOF
            );

            /** @var ReflectionAttribute<JsonSerializerInterface> $attr */
            foreach ($attrs as $attr) {
                $attrInstance = $attr->newInstance();

                $value = $attrInstance->serialize($value);
            }

            $item[$key] = $value;
        }

        return $item;
    }

    public function __call(string $name, array $args)
    {
        return $this->accessorCall($name, $args);
    }

    public function &__get(string $name): mixed
    {
        $getter = $this->getGetter($name);

        if ($getter) {
            $v = $this->$getter();

            return $v;
        }

        return $this->$name;
    }

    public function __set(string $name, mixed $value): void
    {
        $setter = $this->getSetter($name);

        if ($setter) {
            $this->$setter($value);
            return;
        }

        $this->$name = $value;
    }

    public function __isset(string $name): bool
    {
        $getter = $this->getGetter($name);

        if ($getter) {
            return $this->$getter() !== null;
        }

        return isset($this->$name);
    }

    private function getGetter(string $name): ?string
    {
        $pascalName = StrNormalize::toPascalCase($name);
        $getter = 'get' . $pascalName;

        if (method_exists($this, $getter)) {
            return $getter;
        }

        $getter = 'is' . $pascalName;

        if (method_exists($this, $getter)) {
            return $getter;
        }

        return null;
    }

    private function getSetter(string $name): ?string
    {
        $pascalName = StrNormalize::toPascalCase($name);
        $getter = 'set' . $pascalName;

        if (method_exists($this, $getter)) {
            return $getter;
        }

        return null;
    }
}
