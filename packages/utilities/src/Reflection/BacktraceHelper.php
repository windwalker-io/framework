<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Reflection;

/**
 * The BacktraceHelper class.
 */
class BacktraceHelper
{
    public static function findCalled(string $class): ?array
    {
        $inClass = false;

        foreach ($traces = debug_backtrace() as $i => $trace) {
            if ($inClass === false && ($trace['object'] ?? null) instanceof  $class) {
                $inClass = true;
                continue;
            }

            if ($inClass === true && !($trace['object'] ?? null) instanceof  $class) {
                return [
                    'file' => $traces[$i - 2]['file'],
                    'line' => $traces[$i - 2]['line'],
                    'class' => $traces[$i - 1]['class'],
                    'function' => $traces[$i - 1]['function'],
                ];
            }
        }

        return null;
    }

    public static function findCaller(string $class): ?array
    {
        $inClass = false;

        foreach ($traces = debug_backtrace() as $i => $trace) {
            if ($inClass === false && ($trace['object'] ?? null) instanceof  $class) {
                $inClass = true;
                continue;
            }

            if ($inClass === true && !($trace['object'] ?? null) instanceof  $class) {
                return [
                    'file' => $traces[$i - 1]['file'],
                    'line' => $traces[$i - 1]['line'],
                    'class' => $traces[$i]['class'],
                    'function' => $traces[$i]['function'],
                ];
            }
        }

        return null;
    }
}
