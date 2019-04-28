<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Crypt\Cipher;

use Windwalker\Crypt\CryptHelper;

/**
 * The SodiumCipher class.
 *
 * @since  3.2
 */
class SodiumCipher extends AbstractCipher
{
    /**
     * Property key.
     *
     * @var  string
     */
    protected $key;

    /**
     * Property iv.
     *
     * @var  string
     */
    protected $nonce;

    /**
     * Property ignoreMemzero.
     *
     * @var  bool
     */
    protected $ignoreMemzero = false;

    /**
     * Constructor.
     *
     * @param string $key
     *
     * @param array  $options
     *
     * @since  3.2
     *
     */
    public function __construct($key = null, array $options = [])
    {
        if (!function_exists('sodium_crypto_secretbox_open')) {
            throw new \DomainException('Please install ext-libsodium or paragonie/sodium_compat first.');
        }

        parent::__construct($key ?: static::genRandomBytes(static::getKeySize()), $options);
    }

    /**
     * Method to decrypt a data string.
     *
     * @param   string $data  The encrypted string to decrypt.
     * @param   string $key   The private key.
     * @param   string $nonce The public key.
     *
     * @return  string  The decrypted data string.
     * @throws \RuntimeException
     *
     * @since    3.2
     */
    public function decrypt($data, $key = null, $nonce = null)
    {
        $plain = parent::decrypt($data, $key, $nonce);

        $key = (string) $key;
        $nonce = (string) $nonce;

        $this->memzero($data);
        $this->memzero($key);
        $this->memzero($nonce);

        return $plain;
    }

    /**
     * Method to encrypt a data string.
     *
     * @param   string $data  The data string to encrypt.
     * @param   string $key   The private key.
     * @param   string $nonce The public key.
     *
     * @return  string  The encrypted data string.
     *
     * @since   3.2
     * @throws  \InvalidArgumentException
     */
    public function encrypt($data, $key = null, $nonce = null)
    {
        $encrypted = parent::encrypt($data, $key, $nonce);

        $key = (string) $key;
        $nonce = (string) $nonce;

        $this->memzero($data);
        $this->memzero($key);
        $this->memzero($nonce);

        return $encrypted;
    }

    /**
     * getIVKey
     *
     * @return  string
     *
     * @throws \RuntimeException
     */
    public function getNonce()
    {
        if (!$this->nonce) {
            $this->nonce = static::genRandomBytes(static::getNonceSize());
        }

        return $this->nonce;
    }

    /**
     * getNonceSize
     *
     * @return  int
     */
    public static function getNonceSize()
    {
        return SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;
    }

    /**
     * getKeySize
     *
     * @return  int
     */
    public static function getKeySize()
    {
        return SODIUM_CRYPTO_SECRETBOX_KEYBYTES;
    }

    /**
     * genRandomBytes
     *
     * @param int $size
     *
     * @return  string
     */
    public static function genRandomBytes($size = SODIUM_CRYPTO_SECRETBOX_KEYBYTES)
    {
        if (function_exists('sodium_randombytes_buf')) {
            return sodium_randombytes_buf($size);
        }

        return CryptHelper::genRandomBytes($size);
    }

    /**
     * Method to set property ignoreMemzero
     *
     * @param   bool $bool
     *
     * @return  $this|bool
     */
    public function ignoreMemzero($bool = null)
    {
        if ($bool === null) {
            return $this->ignoreMemzero;
        }

        $this->ignoreMemzero = (bool) $bool;

        return $this;
    }

    /**
     * doEncrypt
     *
     * @param   string $data The data string to encrypt.
     * @param   string $key  The private key.
     * @param   string $iv   The public key.
     *
     * @return  string
     */
    protected function doEncrypt($data, $key, $iv)
    {
        $encrypted = sodium_crypto_secretbox($data, $iv, $key);

        $this->memzero($data);
        $this->memzero($key);
        $this->memzero($iv);

        return $encrypted;
    }

    /**
     * doDecrypt
     *
     * @param   string $data The encrypted string to decrypt.
     * @param   string $key  The private key.
     * @param   string $iv   The public key.
     *
     * @return  string
     */
    protected function doDecrypt($data, $key, $iv)
    {
        $plain = sodium_crypto_secretbox_open($data, $iv, $key);

        $this->memzero($data);
        $this->memzero($key);
        $this->memzero($iv);

        return $plain;
    }

    /**
     * randomPseudoBytes
     *
     * @param int $size
     *
     * @return  string
     *
     * @throws \RuntimeException
     */
    protected function randomPseudoBytes($size = null)
    {
        $size = $size ?? SODIUM_CRYPTO_SECRETBOX_KEYBYTES;

        return static::genRandomBytes($size);
    }

    /**
     * getIVSize
     *
     * @return  integer
     */
    public function getIVSize()
    {
        return static::getNonceSize();
    }

    /**
     * canMemzero
     *
     * @return  bool
     */
    public function canMemzero()
    {
        return PHP_VERSION_ID >= 70000 && (extension_loaded('libsodium') || extension_loaded('sodium'));
    }

    /**
     * memzero
     *
     * @param mixed $data
     *
     * @return  void
     * @throws \LogicException
     * @throws \SodiumException
     */
    public function memzero(&$data)
    {
        if (!$this->canMemzero() && !$this->ignoreMemzero) {
            throw new \LogicException(
                'sodium_memzero() only supports after php 7.2 or ext-libsodium installed. ' .
                'You can disable memory wiping by SodiumCipher::ignoreMemzero(true) but it is not recommended.'
            );
        }

        if (!$this->ignoreMemzero) {
            sodium_memzero($data);
        }
    }
}
