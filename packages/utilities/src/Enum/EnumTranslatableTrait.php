<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Enum;

use MyCLabs\Enum\Enum;
use Windwalker\Utilities\Contract\LanguageInterface;

/**
 * Trait EnumTranslatableTrait
 */
trait EnumTranslatableTrait
{
    use EnumMetaTrait;

    public static function getTransItems(LanguageInterface $lang, ...$args): array
    {
        $items = [];

        /** @var static|Enum|EnumTranslatableInterface $item */
        foreach (static::values() as $item) {
            $items[$item->getValue()] = $item->getTitle($lang, ...$args);
        }

        return $items;
    }
}
