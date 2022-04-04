<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2022 __ORGANIZATION__.
 * @license    __LICENSE__
 */

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
