<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Attribute;
use ReflectionProperty;
use Windwalker\Attributes\AttributeHandler;
use Windwalker\ORM\Metadata\EntityMetadata;

/**
 * The CastNullable class.
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_PROPERTY)]
class CastNullable extends Cast
{
    /**
     * @inheritDoc
     */
    public function __construct(mixed $hydrate = null, mixed $extract = null, int $options = 0)
    {
        $options |= static::NULLABLE | static::EMPTY_STRING_TO_NULL;

        parent::__construct($hydrate, $extract, $options);
    }
}
