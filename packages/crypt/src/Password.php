<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Crypt;

use Exception;

/**
 * The SimplePassword class.
 *
 * @since  2.0
 *
 * @deprecated  Use PasswordHasher instead.
 */
class Password
{
    public const SEED_ALNUM = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    /**
     * Generate a random password.
     *
     * @param  integer  $length  Length of the password to generate
     *
     * @return  string  Random Password
     *
     * @throws Exception
     * @since   2.0.9
     */
    public static function genRandomPassword(int $length = 20, string $seed = self::SEED_ALNUM): string
    {
        $base = strlen($seed);
        $password = '';

        $random = str_split(random_bytes($length));

        do {
            $shift = ord(array_pop($random));

            $password .= $seed[$shift % $base];
        } while ($random !== []);

        return $password;
    }
}
