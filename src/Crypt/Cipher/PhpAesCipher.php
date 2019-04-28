<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Crypt\Cipher;

/**
 * The PhpAesCipher class.
 *
 * @see    https://gist.github.com/chrisns/3992815
 *
 * @since  3.0
 */
class PhpAesCipher implements CipherInterface
{
    const KEY_128BIT = 128;

    const KEY_192BIT = 192;

    const KEY_256BIT = 256;

    /**
     * Property keyLength.
     *
     * @var  int
     */
    protected $keyLength = self::KEY_128BIT;

    /**
     * Method to decrypt a data string.
     *
     * @param   string $data The encrypted string to decrypt.
     * @param   string $key  The private key.
     * @param   string $iv   The public key.
     *
     * @return  string  The decrypted data string.
     *
     * @since    2.0
     */
    public function decrypt($data, $key = null, $iv = null)
    {
        include_once __DIR__ . '/../lib/aes.class.php';

        return \AesCtr::decrypt(base64_decode($data), $key, $this->keyLength);
    }

    /**
     * Method to encrypt a data string.
     *
     * @param   string $data The data string to encrypt.
     * @param   string $key  The private key.
     * @param   string $iv   The public key.
     *
     * @return  string  The encrypted data string.
     *
     * @since   2.0
     * @throws  \InvalidArgumentException
     */
    public function encrypt($data, $key = null, $iv = null)
    {
        include_once __DIR__ . '/../lib/aes.class.php';

        return base64_encode(\AesCtr::encrypt($data, $key, $this->keyLength));
    }
}
