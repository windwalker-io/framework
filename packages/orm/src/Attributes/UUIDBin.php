<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

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
class UUIDBin extends CastForSave implements CastInterface
{
    /**
     * CastForSave constructor.
     */
    public function __construct(
        public string $version = 'uuid7',
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

            return new UuidBinWrapper($value ?: \Ramsey\Uuid\Uuid::$method());
        };
    }

    public function hydrate(mixed $value): mixed
    {
        return UuidBinWrapper::wrap($value);
    }

    public function extract(mixed $value): mixed
    {
        return UuidBinWrapper::wrap($value);
    }

    public static function wrap(mixed $value): UuidInterface
    {
        return UuidBinWrapper::wrap($value);
    }
}
