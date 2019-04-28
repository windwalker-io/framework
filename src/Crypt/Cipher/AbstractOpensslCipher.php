<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Crypt\Cipher;

/**
 * The AbstractOpensslCipher class.
 *
 * @since  3.2
 */
class AbstractOpensslCipher extends AbstractCipher
{
    /**
     * Property type.
     *
     * @var string
     */
    protected $method;

    /**
     * Property mode.
     *
     * @var int
     */
    protected $mode = 'cbc';

    /**
     * Constructor.
     *
     * @param string $key
     *
     * @param array  $options
     *
     * @since  2.0
     *
     */
    public function __construct($key = null, array $options = [])
    {
        if (!is_callable('openssl_encrypt')) {
            throw new \RuntimeException('The openssl extension is not available.');
        }

        parent::__construct($key ?: $this->randomPseudoBytes(24), $options);
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
        return openssl_encrypt($data, $this->getMethod(), $key, OPENSSL_RAW_DATA, $iv);
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
        return openssl_decrypt($data, $this->getMethod(), $key, OPENSSL_RAW_DATA, $iv);
    }

    /**
     * getIVSize
     *
     * @return  integer
     */
    public function getIVSize()
    {
        return openssl_cipher_iv_length($this->getMethod());
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
        $size = $size ?: static::PBKDF2_SALT_BYTE_SIZE;

        $bytes = openssl_random_pseudo_bytes($size, $isSourceStrong);

        if (false === $isSourceStrong || false === $bytes) {
            throw new \RuntimeException('IV generation failed');
        }

        return $bytes;
    }

    /**
     * Method to get property Mode
     *
     * @return  int
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Method to set property mode
     *
     * @param   int $mode
     *
     * @return  static  Return self to support chaining.
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * Method to get property Type
     *
     * @return  string
     */
    public function getMethod()
    {
        if (!$this->mode) {
            return $this->method;
        }

        return $this->method . '-' . $this->mode;
    }
}
