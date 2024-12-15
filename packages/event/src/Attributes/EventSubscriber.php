<?php

declare(strict_types=1);

namespace Windwalker\Event\Attributes;

use Attribute;
use Windwalker\Attributes\AttributeHandler;
use Windwalker\Attributes\AttributeInterface;

/**
 * The EventSubscriber class.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class EventSubscriber
{
    public static function isSubscriber(object|string $objectOrClass): bool
    {
        if ($objectOrClass instanceof \ReflectionClass) {
            $ref = $objectOrClass;
        } else {
            $ref = new \ReflectionClass($objectOrClass);
        }

        $subscriberAttributes = $ref->getAttributes(self::class, \ReflectionAttribute::IS_INSTANCEOF);

        return count($subscriberAttributes) > 0;
    }
}
