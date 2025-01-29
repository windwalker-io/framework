<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Enum;

use MyCLabs\Enum\Enum;

use function Windwalker\arr;

/**
 * Trait EnumPhpAdapterTrait
 */
trait EnumPhpAdapterTrait
{
    public static function preprocessValue(mixed $value): mixed
    {
        return $value;
    }

    public static function wrap(mixed $value): static
    {
        $value = static::preprocessValue($value);

        if ($value instanceof self) {
            return $value;
        }

        return static::from($value);
    }

    public static function tryWrap(mixed $value): ?static
    {
        $value = static::preprocessValue($value);

        if ($value instanceof self) {
            return $value;
        }

        if ($value === null) {
            return null;
        }

        return static::tryFrom($value);
    }

    /**
     * @return  array<string, static>
     *
     * @throws \ReflectionException
     */
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

        return array_map(fn ($value) => static::wrap($value), static::toArray());
    }

    /**
     * @return  array<static>
     *
     * @throws \ReflectionException
     */
    public static function cases(): array
    {
        return array_values(static::values());
    }

    public static function rawValues(): array
    {
        return array_map(fn (self $case) => $case->value, self::values());
    }

    public function getValue(): mixed
    {
        if ($this instanceof \UnitEnum) {
            return $this->value ?? $this->name;
        }

        // Legacy Enum
        return parent::getValue();
    }

    public function getKey(): mixed
    {
        if ($this instanceof \UnitEnum) {
            // Native Enum
            return $this->name;
        }

        // Legacy Enum
        return parent::getKey();
    }

    public function sameAs($variable = null): bool
    {
        if ($this instanceof \UnitEnum) {
            // Native Enum
            return $this === static::tryWrap($variable);
        }

        // Legacy Enum
        return $this->equals(static::wrap($variable));
    }

    public static function tryFrom(string|int $value): ?static
    {
        if (is_subclass_of(static::class, \UnitEnum::class)) {
            // Native Enum
            return static::tryFrom($value);
        }

        try {
            // Legacy Enum
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
     * @throws \ReflectionException
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

    public function __call(string $name, array $arguments)
    {
        if ($this instanceof Enum && $name === 'value') {
            return $this->getValue();
        }

        // Since static::FOO in object scope will seems as a parent instance call in PHP,
        // so here may catch static method call. We must check contstants exists here.
        if (defined(static::class . '::' . $name)) {
            return static::wrap(constant(static::class . '::' . $name));
        }

        return $this->$name;
    }

    public static function __callStatic($name, $args)
    {
        if (is_subclass_of(static::class, \UnitEnum::class)) {
            $ref = new \ReflectionEnum(static::class);

            if ($ref->hasCase($name)) {
                return constant(static::class . '::' . $name);
            }
        } else {
            // For legacy enum class
            return parent::__callStatic($name, $args);
        }
    }
}
