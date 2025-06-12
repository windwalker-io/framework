<?php

declare(strict_types=1);

namespace Windwalker;

use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Uuid;
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
