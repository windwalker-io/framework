<?php

declare(strict_types=1);

namespace Windwalker\ORM\Cast;

use Windwalker\Query\Wrapper\UuidWrapper;

/**
 * The UuidCast class.
 */
class UuidCast implements CastInterface
{
    public function hydrate(mixed $value): mixed
    {
        return UuidWrapper::wrap($value);
    }

    public function extract(mixed $value): mixed
    {
        return UuidWrapper::wrap($value);
    }
}
