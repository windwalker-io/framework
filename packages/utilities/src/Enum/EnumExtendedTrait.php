<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Enum;

use function Windwalker\arr;

trait EnumExtendedTrait
{
    public static function preprocessValue(mixed $value): mixed
    {
        return $value;
    }

    public static function wrap(mixed $value): static
    {
        $value = static::preprocessValue($value);

        if ($value instanceof self) {
            return $value;
        }

        return static::from($value);
    }

    public static function tryWrap(mixed $value): ?static
    {
        $value = static::preprocessValue($value);

        if ($value instanceof self) {
            return $value;
        }

        if ($value === null) {
            return null;
        }

        return static::tryFrom($value);
    }

    /**
     * @return  array<string, static>
     */
    public static function values(): array
    {
        $cases = [];

        foreach (self::cases() as $case) {
            $cases[$case->name] = $case;
        }

        return $cases;
    }

    public static function rawValues(): array
    {
        return array_map(static fn (self $case) => $case->value, self::values());
    }

    /**
     * Returns the names (keys) of all constants in the Enum class
     *
     * @psalm-pure
     * @psalm-return list<string>
     * @return array
     */
    public static function names(): array
    {
        return \array_keys(static::values());
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
