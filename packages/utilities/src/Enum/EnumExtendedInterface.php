<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Enum;

interface EnumExtendedInterface extends \JsonSerializable
{
    public static function wrap(mixed $value): static;

    public static function tryWrap(mixed $value): ?static;

    /**
     * Return assoc array of cases with case name as key.
     *
     * @return  array<string|int, static>
     */
    public static function values(): array;

    public static function rawValues(): array;

    /**
     * Returns the names (keys) of all constants in the Enum class
     *
     * @psalm-pure
     * @psalm-return list<string>
     * @return array
     */
    public static function names(): array;
}
