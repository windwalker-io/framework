<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Classes;

final class ObjectMetadata
{
    protected static WeakObjectStorage $storage;

    public static function get(object $object, string $key): mixed
    {
        return self::getMetadata($object)[$key] ?? null;
    }

    public static function set(object $object, string $key, mixed $value): void
    {
        $metadata = self::getMetadata($object);

        $metadata[$key] = $value;

        self::setMetadata($object, $metadata);
    }

    public static function remove(object $object, string $key): void
    {
        $metadata = self::getMetadata($object);

        unset($metadata[$key]);

        self::setMetadata($object, $metadata);
    }

    public static function has(object $object, string $key): bool
    {
        $metadata = self::getMetadata($object);

        return isset($metadata[$key]);
    }

    public static function getMetadata(object $object): array
    {
        return self::getStorage()->get($object) ?? [];
    }

    public static function setMetadata(object $object, array $data): void
    {
        self::getStorage()->set($object, $data);
    }

    public static function removeMetadata(object $object): void
    {
        self::getStorage()->remove($object);
    }

    public static function hasMetadata(object $object): bool
    {
        return self::getStorage()->has($object);
    }

    public static function getStorage(): WeakObjectStorage
    {
        return self::$storage ??= new WeakObjectStorage();
    }
}
