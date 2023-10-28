<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Uri {
    if (!function_exists('\Windwalker\Uri\uri_prepare')) {
        function uri_prepare(string $template, array $vars = []): UriTemplate
        {
            return new UriTemplate($template, $vars);
        }
    }

    if (!function_exists('\Windwalker\Uri\uri_expand')) {
        function uri_expand(string $template, array $vars = []): string
        {
            return (string) new UriTemplate($template, $vars);
        }
    }

    if (!function_exists('\Windwalker\Uri\uri_extract')) {
        function uri_extract(string $template, string $uri, bool $strict = false): array
        {
            return (new UriTemplate($template))->extract($uri, $strict);
        }
    }
}
