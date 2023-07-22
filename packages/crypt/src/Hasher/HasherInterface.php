<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Crypt\Hasher;

/**
 * Interface HasherInterface
 */
interface HasherInterface
{
    public function hash(#[\SensitiveParameter] string $string): string;

    public function equals(
        #[\SensitiveParameter] string $knownString,
        #[\SensitiveParameter] string $userString,
    ): bool;

    public static function algos(): array;
}
