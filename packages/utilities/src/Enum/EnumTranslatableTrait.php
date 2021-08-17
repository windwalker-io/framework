<?php

/**
 * Part of unicorn project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Enum;

use MyCLabs\Enum\Enum;
use Windwalker\Utilities\Contract\LanguageInterface;

/**
 * Trait EnumTranslableTrait
 */
trait EnumTranslatableTrait
{
    public static function getTransItems(LanguageInterface $lang, ...$args): array
    {
        $items = [];

        /** @var Enum|EnumTranslatableInterface $item */
        foreach (static::values() as $item) {
            $items[$item->getValue()] = $item->trans($lang, ...$args);
        }

        return $items;
    }
}
