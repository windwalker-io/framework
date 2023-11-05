<?php

declare(strict_types=1);

namespace Windwalker\ORM\Cast;

use Windwalker\Query\Wrapper\UuidBinWrapper;

/**
 * The UuidCast class.
 */
class UuidBinCast implements CastInterface
{
    public function hydrate(mixed $value): mixed
    {
        return UuidBinWrapper::wrap($value);
    }

    public function extract(mixed $value): mixed
    {
        return UuidBinWrapper::wrap($value);
    }
}
