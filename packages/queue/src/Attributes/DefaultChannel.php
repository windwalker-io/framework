<?php

declare(strict_types=1);

namespace Windwalker\Queue\Attributes;

use Windwalker\Utilities\Attributes\AttributesAccessor;

#[\Attribute(\Attribute::TARGET_CLASS)]
class DefaultChannel
{
    public function __construct(public string $channel) {}

    public static function resolve(object $job): ?string
    {
        $attr = AttributesAccessor::getFirstAttributeInstance(
            $job,
            static::class,
            \ReflectionAttribute::IS_INSTANCEOF
        );

        return $attr?->channel;
    }
}
