<?php

declare(strict_types=1);

namespace Windwalker\Crypt\Symmetric;

use Windwalker\Crypt\HiddenString;
use Windwalker\Crypt\Key;
use Windwalker\Crypt\SafeEncoder;

use const Windwalker\Crypt\ENCODER_BASE64URLSAFE;

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
     * @param string            $str
     * @param string|Key        $key
     * @param  string|callable  $encoder
     *
     * @return  HiddenString
     */
    public function decrypt(
        string $str,
        #[\SensitiveParameter] Key|string $key,
        string|callable $encoder = ENCODER_BASE64URLSAFE
    ): HiddenString;

    /**
     * encrypt
     *
     * @param string|HiddenString  $str
     * @param string|Key           $key
     * @param  string|callable     $encoder
     *
     * @return  string
     */
    public function encrypt(
        #[\SensitiveParameter] HiddenString|string $str,
        #[\SensitiveParameter] Key|string $key,
        string|callable $encoder = ENCODER_BASE64URLSAFE
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
