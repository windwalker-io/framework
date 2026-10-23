<?php

declare(strict_types=1);

namespace Windwalker;

use Brick\Math\BigDecimal;
use Brick\Math\BigInteger;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use ReflectionAttribute;
use UnexpectedValueException;
use Windwalker\ORM\Attributes\Table;
use Windwalker\Query\Wrapper\UuidBinWrapper;

if (!function_exists('\Windwalker\entity_table')) {
    /**
     * Get Table name from Entity object or class.
     *
     * @param  string|object  $entity
     *
     * @return  string
     */
    function entity_table(string|object $entity): string
    {
        if (!str_contains($entity, '\\') || !class_exists($entity)) {
            return $entity;
        }

        if (is_object($entity)) {
            $entity = $entity::class;
        }

        $tableAttr = Attributes\AttributesAccessor::getFirstAttributeInstance(
            $entity,
            Table::class,
            ReflectionAttribute::IS_INSTANCEOF
        );

        if (!$tableAttr) {
            throw new UnexpectedValueException(
                sprintf(
                    '%s has no table info.',
                    $entity
                )
            );
        }

        return $entity;
    }
}

if (!function_exists('\Windwalker\wrap_uuid')) {
    function wrap_uuid(mixed $uuid): UuidBinWrapper
    {
        return new UuidBinWrapper($uuid);
    }
}

if (!function_exists('\Windwalker\try_wrap_uuid')) {
    function try_wrap_uuid(mixed $uuid): ?UuidBinWrapper
    {
        if ($uuid === null) {
            return null;
        }

        return new UuidBinWrapper($uuid);
    }
}

if (!function_exists('\Windwalker\to_uuid')) {
    function to_uuid(mixed $uuid): UuidInterface
    {
        return try_uuid($uuid);
    }
}

if (!function_exists('\Windwalker\try_uuid')) {
    function try_uuid(mixed $uuid): ?UuidInterface
    {
        if (!interface_exists(UuidInterface::class)) {
            throw new \DomainException('Please install `ramsey/uuid` first.');
        }

        if ($uuid === null || $uuid === '0x') {
            return null;
        }

        if ($uuid instanceof UuidInterface) {
            return $uuid;
        }

        if (is_string($uuid) && strlen($uuid) === 16) {
            return Uuid::fromBytes($uuid);
        }

        return Uuid::fromString($uuid);
    }
}

if (!function_exists('\Windwalker\try_big_decimal')) {
    /**
     * NOTE: This function convert float to string first to avoid float precision issue.
     * If you want ot keep the IEE 754 float precision, please use `BigDecimal::fromFloatShortest($float)` directly.
     *
     * @param  mixed  $value
     * @param  int    $scale
     *
     * @return  BigDecimal|null
     */
    function try_big_decimal(mixed $value, int $scale = 4): ?BigDecimal
    {
        if (!class_exists(BigDecimal::class)) {
            throw new \DomainException('Please install `brick/math` first.');
        }

        if ($value === null) {
            return null;
        }

        if ($value instanceof BigDecimal) {
            return $value;
        }

        return BigDecimal::of((string) $value)->toScale($scale);
    }
}

if (!function_exists('\Windwalker\try_big_integer')) {
    /**
     * @param  mixed  $value
     *
     * @return  BigInteger|null
     */
    function try_big_integer(mixed $value): ?BigInteger
    {
        if (!class_exists(BigInteger::class)) {
            throw new \DomainException('Please install `brick/math` first.');
        }

        if ($value === null) {
            return null;
        }

        if ($value instanceof BigInteger) {
            return $value;
        }

        return BigInteger::of((string) $value);
    }
}
