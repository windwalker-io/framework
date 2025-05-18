<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Ramsey\Uuid\UuidInterface;
use Windwalker\Cache\Exception\LogicException;
use Windwalker\ORM\Cast\CastInterface;
use Windwalker\Query\Wrapper\UuidBinWrapper;
use Windwalker\Query\Wrapper\UuidWrapper;

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
