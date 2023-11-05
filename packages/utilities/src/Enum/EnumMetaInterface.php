<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Enum;

use Windwalker\Utilities\Contract\LanguageInterface;

/**
 * Interface EnumMetaInterface
 */
interface EnumMetaInterface extends EnumAdapterInterface
{
    public function getTitle(?LanguageInterface $lang = null, ...$args): string;

    public static function getTitles(): array;

    public function getIcon(): string;

    public static function getIcons(): array;

    public function getColor(): string;

    public static function getColors(): array;

    public function getMeta(): array;

    public static function getMetas(): array;

    public static function maxLength(): int;
}
