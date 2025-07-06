<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Enum;

use Windwalker\Utilities\Contract\LanguageInterface;

trait EnumRichTrait
{
    use EnumExtendedTrait;
    use EnumMetaTrait;

    public static function getTransItems(LanguageInterface $lang, ...$args): array
    {
        $items = [];

        foreach (self::cases() as $item) {
            $items[$item->value] = $item->getTitle($lang, ...$args);
        }

        return $items;
    }
}
