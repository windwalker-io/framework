<?php

declare(strict_types=1);

namespace Windwalker\Crypt\Symmetric;

use AesCtr;
use Exception;
use Windwalker\Crypt\HiddenString;
use Windwalker\Crypt\Key;
use Windwalker\Crypt\SafeEncoder;

use const Windwalker\Crypt\ENCODER_BASE64URLSAFE;

/**
 * The PhpAesCipher class.
 *
 * @see    https://gist.github.com/chrisns/3992815
 *
 * @since  3.0
 *
 * @deprecated  This class is keep for B/C, use SodiumCipher instead.
 */
class PhpAesCipher implements CipherInterface
{
    public const KEY_128BIT = 128;

    public const KEY_192BIT = 192;

    public const KEY_256BIT = 256;

    /**
     * Property keyLength.
     *
     * @var  int
     */
    protected $keyLength = self::KEY_128BIT;

    /**
     * Method to decrypt a data string.
     *
     * @param string            $str
     * @param string|Key        $key  The private key.
     * @param  string|callable  $encoder
     *
     * @return HiddenString The decrypted data string.
     *
     * @since    2.0
     */
    public function decrypt(
        string $str,
        #[\SensitiveParameter] Key|string $key,
        string|callable $encoder = ENCODER_BASE64URLSAFE
    ): HiddenString {
        $key = Key::strip($key);

        include_once __DIR__ . '/../lib/aes.class.php';

        return new HiddenString(
            (string) AesCtr::decrypt(base64_decode($str), $key, $this->keyLength)
        );
    }

    /**
     * Method to encrypt a data string.
     *
     * @param string|HiddenString  $str
     * @param string|Key           $key  The private key.
     * @param  string|callable     $encoder
     *
     * @return  string  The encrypted data string.
     *
     * @since   2.0
     */
    public function encrypt(
        #[\SensitiveParameter] HiddenString|string $str,
        #[\SensitiveParameter] Key|string $key,
        string|callable $encoder = ENCODER_BASE64URLSAFE
    ): string {
        $str = HiddenString::strip($str);
        $key = Key::strip($key);

        include_once __DIR__ . '/../lib/aes.class.php';

        return base64_encode(AesCtr::encrypt($str, $key, $this->keyLength));
    }

    /**
     * Generate Key.
     *
     * @param int|null $length
     *
     * @return  Key
     * @throws Exception
     */
    public static function generateKey(?int $length = 32): Key
    {
        return new Key(random_bytes($length));
    }
}
