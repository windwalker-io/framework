<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\ORM\Cast;

use DateTimeImmutable;
use Windwalker\Utilities\TypeCast;

/**
 * The TimestampCast class.
 */
class TimestampCast implements CastInterface
{
    /**
     * @inheritDoc
     */
    public function hydrate(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (!is_numeric($value)) {
            $value = (new DateTimeImmutable($value))->getTimestamp();
        }

        return TypeCast::tryNumeric($value);
    }

    /**
     * @inheritDoc
     */
    public function extract(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('U', $value);

        return $date->format('Y-m-d H:i:s');
    }
}
