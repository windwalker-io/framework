<?php

/**
 * Part of framework project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Query\Grammar;

/**
 * Trait JsonPathTrait
 */
trait JsonGrammarTrait
{
    public static function compileJsonPath(array $segments): string
    {
        $path = '$';

        foreach ($segments as $segment) {
            if (is_numeric($segment)) {
                $path .= "[$segment]";
            } else {
                $path .= '.' . $segment;
            }
        }

        return $path;
    }
}
