<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Crypt\Hasher;

/**
 * The PasswordHasher class.
 */
class PasswordHasher implements PasswordHasherInterface
{
    public const SEED_ALNUM = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    public const SEED_ALNUM_SPECIAL_CHARS = self::SEED_ALNUM . '!"#$%&\'()*+,-./:;<=>?@[\]^_`{|}~';

    public function __construct(
        protected string|int|null $algo = null,
        protected array $options = []
    ) {
    }

    public function hash(#[\SensitiveParameter] string $string): string
    {
        return password_hash($string, $this->algo, $this->options);
    }

    public function equals(#[\SensitiveParameter] string $knownString, #[\SensitiveParameter] string $userString): bool
    {
        return hash_equals($knownString, $userString);
    }

    public function verify(#[\SensitiveParameter] string $password, #[\SensitiveParameter] string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function algos(): array
    {
        return password_algos();
    }

    public function getInfo(#[\SensitiveParameter] string $hash): ?array
    {
        return password_get_info($hash);
    }

    public function needsRehash(#[\SensitiveParameter] string $hash): bool
    {
        return password_needs_rehash($hash, $this->algo, $this->options);
    }

    /**
     * Generate a random password.
     *
     * @param  int  $length  Length of the password to generate
     *
     * @return  string  Random Password
     *
     * @throws \Exception
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

    /**
     * @return string
     */
    public function getAlgo(): string
    {
        return $this->algo;
    }

    /**
     * @param  string  $algo
     *
     * @return  static  Return self to support chaining.
     */
    public function setAlgo(string $algo): static
    {
        $this->algo = $algo;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param  array  $options
     *
     * @return  static  Return self to support chaining.
     */
    public function setOptions(array $options): static
    {
        $this->options = $options;

        return $this;
    }
}
