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
 * Interface PasswordInterface
 */
interface PasswordHasherInterface extends HasherInterface
{
    public function verify(#[\SensitiveParameter] string $password, #[\SensitiveParameter] string $hash): bool;

    /**
     * @param  string  $hash
     *
     * @return  array{ algo: int, algoName: string, options: array }|null
     */
    public function getInfo(#[\SensitiveParameter] string $hash): ?array;

    public function needsRehash(#[\SensitiveParameter] string $hash): bool;
}
