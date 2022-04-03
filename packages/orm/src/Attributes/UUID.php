<?php

/**
 * Part of unicorn project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Windwalker\Cache\Exception\LogicException;
use Windwalker\Query\Wrapper\UuidWrapper;

/**
 * The UUID class.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class UUID extends CastForSave
{
    /**
     * CastForSave constructor.
     */
    public function __construct(
        public string $version = 'uuid6',
        public mixed $caster = null,
        public int $options = 0
    ) {
        $this->caster ??= $this->getUUIDCaster();
    }

    public function getUUIDCaster(): \Closure
    {
        return function ($value) {
            if (!class_exists(\Ramsey\Uuid\Uuid::class)) {
                throw new LogicException('Please install ramsey/uuid ^4.0 first.');
            }

            $method = $this->version;

            return new UuidWrapper($value ?? \Ramsey\Uuid\Uuid::$method());
        };
    }
}
