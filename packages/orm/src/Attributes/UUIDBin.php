<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Ramsey\Uuid\Uuid as RamseyUuid;
use Ramsey\Uuid\UuidInterface;
use Windwalker\Cache\Exception\LogicException;
use Windwalker\ORM\Cast\CastInterface;
use Windwalker\ORM\Traits\UUIDTrait;
use Windwalker\Query\Wrapper\UuidBinWrapper;

/**
 * The UUID class.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class UUIDBin implements CastInterface, CastForSaveInterface
{
    use UUIDTrait;

    public const NULLABLE = 1 << 0;

    /**
     * CastForSave constructor.
     */
    public function __construct(
        public string|int $version = self::UUID7,
        public int $options = 0
    ) {
    }

    public function getCaster(): \Closure
    {
        return function ($value) {
            static::checkLibrary();

            if (!$value && ($this->options & static::NULLABLE)) {
                return null;
            }

            return new UuidBinWrapper($value ?: static::getDefault($this->version));
        };
    }

    public function hydrate(mixed $value): mixed
    {
        return UuidBinWrapper::tryWrap($value);
    }

    public function extract(mixed $value): mixed
    {
        return UuidBinWrapper::tryWrap($value);
    }

    public static function wrap(mixed $value): UuidInterface
    {
        return UuidBinWrapper::wrap($value);
    }

    public static function tryWrap(mixed $value): ?UuidInterface
    {
        return UuidBinWrapper::tryWrap($value);
    }
}
