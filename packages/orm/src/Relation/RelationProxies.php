<?php

declare(strict_types=1);

namespace Windwalker\ORM\Relation;

use Closure;
use Windwalker\ORM\EntityMapper;

/**
 * The RelationProxy class.
 */
class RelationProxies
{
    public static function set(object $entity, string $prop, mixed $getter): void
    {
        EntityMapper::getObjectMetadata()->set($entity, static::handleProp($prop), $getter);
    }

    public static function get(object $entity, string $prop): mixed
    {
        return EntityMapper::getObjectMetadata()->get($entity, static::handleProp($prop));
    }

    public static function has(object $entity, string $prop): bool
    {
        return EntityMapper::getObjectMetadata()->has($entity, static::handleProp($prop));
    }

    public static function call(object $entity, string $prop): mixed
    {
        $result = self::get($entity, $prop);

        if (!$result) {
            return null;
        }

        if ($result instanceof Closure) {
            self::set($entity, $prop, $result = $result());
        }

        return $result;
    }

    public static function remove(object $entity, string $prop): void
    {
        EntityMapper::getObjectMetadata()->remove($entity, static::handleProp($prop));
    }

    protected static function handleProp(string $prop): string
    {
        return 'orm.relation:' . $prop;
    }
}
