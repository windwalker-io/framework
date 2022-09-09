<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use Windwalker\Data\Collection;
use Windwalker\ORM\Attributes\Table;
use Windwalker\ORM\Relation\RelationCollection;
use Windwalker\ORM\Relation\RelationProxies;
use Windwalker\ORM\Relation\Strategy\RelationStrategyInterface;
use Windwalker\Utilities\StrNormalize;
use Windwalker\Utilities\TypeCast;

/**
 * The AbstractEntity class.
 */
trait EntityTrait
{
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

    protected function loadRelation(string $propName): mixed
    {
        return $this->$propName ??= RelationProxies::call($this, $propName);
    }

    protected function loadCollection(string $propName)
    {
        return $this->$propName ??= RelationProxies::call($this, $propName)
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
        return TypeCast::toArray(get_object_vars($this), $recursive, $onlyDumpable);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        $this->loadAllRelations();

        return $this->dump();
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
