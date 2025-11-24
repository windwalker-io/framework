<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Ramsey\Uuid\UuidInterface;
use Windwalker\Cache\Exception\LogicException;
use Windwalker\ORM\Cast\CastInterface;
use Windwalker\ORM\Traits\UUIDTrait;
use Windwalker\Query\Wrapper\UuidWrapper;

/**
 * The UUID class.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class UUID implements CastInterface, CastForSaveInterface
{
    use UUIDTrait;

    public function getCaster(): \Closure
    {
        return function ($value) {
            static::checkLibrary();

            return new UuidWrapper($value ?: static::getDefault($this->version));
        };
    }

    public function hydrate(mixed $value): mixed
    {
        return UuidWrapper::wrap($value);
    }

    public function extract(mixed $value): mixed
    {
        return UuidWrapper::wrap($value);
    }

    public static function wrap(mixed $value): UuidInterface
    {
        return UuidWrapper::wrap($value);
    }

    public static function tryWrap(mixed $value): ?UuidInterface
    {
        return UuidWrapper::tryWrap($value);
    }
}
