<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Crypt\Symmetric;

use Windwalker\Crypt\HiddenString;
use Windwalker\Crypt\Key;
use Windwalker\Crypt\SafeEncoder;

/**
 * Interface CipherInterface
 *
 * @since  2.0
 */
interface CipherInterface
{
    /**
     * Decrypt string.
     *
     * @param string $str
     * @param string|Key $key
     * @param string $encoder
     *
     * @return  HiddenString
     */
    public function decrypt(
        string $str,
        #[\SensitiveParameter] Key|string $key,
        string $encoder = SafeEncoder::BASE64URLSAFE
    ): HiddenString;

    /**
     * encrypt
     *
     * @param string|HiddenString $str
     * @param string|Key          $key
     * @param string              $encoder
     *
     * @return  string
     */
    public function encrypt(
        #[\SensitiveParameter] HiddenString|string $str,
        #[\SensitiveParameter] Key|string $key,
        string $encoder = SafeEncoder::BASE64URLSAFE
    ): string;

    /**
     * Generate Key.
     *
     * @param int|null $length
     *
     * @return  Key
     */
    public static function generateKey(?int $length = null): Key;
}
