<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2022 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Enum;

use function Windwalker\arr;

/**
 * Trait EnumPhpAdapterTrait
 */
trait EnumPhpAdapterTrait
{
    public static function wrap(mixed $value): static
    {
        if ($value instanceof self) {
            return $value;
        }

        return self::from($value);
    }

    public static function tryWrap(mixed $value): ?static
    {
        if ($value instanceof self) {
            return $value;
        }

        return self::tryFrom($value);
    }

    public static function values(): array
    {
        if (is_subclass_of(static::class, \UnitEnum::class)) {
            $ref = new \ReflectionEnum(static::class);

            $cases = [];

            foreach ($ref->getCases() as $case) {
                $cases[$case->name] = $case->getValue();
            }

            return $cases;
        }

        return parent::values();
    }

    public static function cases(): array
    {
        return array_values(static::values());
    }

    public function getValue(): mixed
    {
        if ($this instanceof \UnitEnum) {
            return $this->value ?? $this->name;
        }

        return parent::getValue();
    }

    public function getKey(): mixed
    {
        if ($this instanceof \UnitEnum) {
            return $this->name;
        }

        return parent::getKey();
    }

    public function sameAs($variable = null): bool
    {
        if ($this instanceof \UnitEnum) {
            return $this === static::tryWrap($variable);
        }

        return $this->equals(static::wrap($variable));
    }

    public static function tryFrom(string|int $value): ?static
    {
        if (is_subclass_of(static::class, \UnitEnum::class)) {
            return parent::tryFrom($value);
        }

        try {
            return parent::from($value);
        } catch (\UnexpectedValueException) {
            return null;
        }
    }

    /**
     * Returns the names (keys) of all constants in the Enum class
     *
     * @psalm-pure
     * @psalm-return list<string>
     * @return array
     */
    public static function keys(): array
    {
        return \array_keys(static::toArray());
    }

    /**
     * Returns all possible values as an array
     *
     * @psalm-pure
     * @psalm-suppress ImpureStaticProperty
     *
     * @psalm-return array<string, mixed>
     * @return array Constant name in key, constant value in value
     */
    public static function toArray(): array
    {
        static $caches = [];

        $class = static::class;

        if (!isset($caches[$class])) {
            if (is_subclass_of(static::class, \UnitEnum::class)) {
                $reflection = new \ReflectionEnum($class);
                $values = arr($reflection->getCases())
                    ->map(fn (\ReflectionEnumBackedCase|\ReflectionEnumUnitCase $case) => $case->getValue())
                    ->mapWithKeys(fn (\UnitEnum|\BackedEnum $enum) => [$enum->name => $enum])
                    ->map(fn (\UnitEnum|\BackedEnum $enum) => $enum->value ?? $enum->name)
                    ->dump();

                $caches[$class] = $values;
            } else {
                $reflection = new \ReflectionClass($class);
                $caches[$class] = $reflection->getConstants();
            }
        }

        return $caches[$class];
    }

    /**
     * Specify data which should be serialized to JSON. This method returns data that can be serialized by json_encode()
     * natively.
     *
     * @return mixed
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @psalm-pure
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->getValue();
    }

    public static function __callStatic($name, $args)
    {
        if (is_subclass_of(static::class, \UnitEnum::class)) {
            $ref = new \ReflectionEnum(static::class);

            if ($ref->hasCase($name)) {
                return constant(static::class . '::' . $name);
            }
        }

        return parent::__callStatic($name, $args);
    }
}
