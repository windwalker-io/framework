<?php

declare(strict_types=1);

namespace Windwalker\ORM\Cast;

use DateTimeImmutable;
use DateTimeInterface;

/**
 * The DateTimeCast class.
 */
class DateTimeCast implements CastInterface
{
    /**
     * @inheritDoc
     */
    public function hydrate(mixed $value): ?DateTimeInterface
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return $value;
        }

        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value)) {
            return DateTimeImmutable::createFromFormat('Y-m-d', $value);
        }

        return new DateTimeImmutable($value);
    }

    /**
     * @inheritDoc
     */
    public function extract(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y-m-d H:i:s');
        }

        return (string) $value;
    }
}
