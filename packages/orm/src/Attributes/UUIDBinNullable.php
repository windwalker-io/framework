<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

/**
 * The UUID class.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class UUIDBinNullable extends UUIDBin
{
    public function __construct(string|int|\Closure $version = self::UUID7, int $options = self::NULLABLE)
    {
        parent::__construct($version, $options);
    }
}
