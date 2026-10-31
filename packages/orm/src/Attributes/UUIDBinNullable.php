<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

/**
 * The UUID class.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class UUIDBinNullable extends UUIDBin
{
    public function __construct(string|int $version = self::UUID7, mixed $caster = null, int $options = self::NULLABLE)
    {
        parent::__construct($version, $caster, $options);
    }
}
