<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Enum;

use Windwalker\Utilities\Contract\LanguageInterface;

/**
 * Interface EnumTranslatableInterface
 */
interface EnumTranslatableInterface extends EnumMetaInterface
{
    public function trans(LanguageInterface $lang, ...$args): string;

    public static function getTransItems(LanguageInterface $lang, ...$args): array;
}
