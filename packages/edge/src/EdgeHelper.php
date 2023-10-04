<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Edge;

use Windwalker\Utilities\TypeCast;
use Windwalker\Utilities\Wrapper\RawWrapper;

/**
 * The EdgeHelper class.
 */
class EdgeHelper
{
    public static function toCssClasses(mixed $array): string
    {
        $classList = TypeCast::toArray($array);

        $classes = [];

        foreach ($classList as $class => $constraint) {
            if (is_numeric($class)) {
                $classes[] = $constraint;
            } elseif ($constraint) {
                $classes[] = $class;
            }
        }

        return implode(' ', $classes);
    }

    public static function toJS(mixed $data): string
    {
        $base64 = base64_encode(json_encode($data));

        return "JSON.parse(atob('$base64'))";
    }
}
