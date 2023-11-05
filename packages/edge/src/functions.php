<?php

declare(strict_types=1);

namespace {

    // Simple fix for Blade escape
    if (!function_exists('e')) {
        function e(mixed $string, bool $doubleEncode = true): string
        {
            return htmlspecialchars((string) $string, ENT_QUOTES, 'UTF-8', $doubleEncode);
        }
    }
}
