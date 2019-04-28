<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Crypt;

use Windwalker\Crypt\Cipher\CipherInterface;

/**
 * The Crypt class.
 *
 * @since  2.0
 */
class Crypt implements CryptInterface
{
    /**
     * Property cipher.
     *
     * @var CipherInterface
     */
    protected $cipher;

    /**
     * Property public.
     *
     * @var  string
     */
    protected $iv;

    /**
     * Property private.
     *
     * @var  null|string
     */
    protected $key;

    /**
     * Class init.
     *
     * @param CipherInterface $cipher
     * @param string          $key
     * @param string          $iv
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(CipherInterface $cipher, $key = null, $iv = null)
    {
        $this->cipher = $cipher;
        $this->iv = $iv;
        $this->key = $key;

        if (!is_string($this->key) && $this->key !== null) {
            throw new \InvalidArgumentException('Public key should be string');
        }
    }

    /**
     * encrypt
     *
     * @param string $string
     * @param string $key
     * @param string $iv
     *
     * @return  string
     *
     * @throws \InvalidArgumentException
     */
    public function encrypt($string, $key = null, $iv = null)
    {
        $key = $key ?: $this->getKey();
        $iv = $iv ?: $this->getIv();

        return $this->cipher->encrypt($string, $key, $iv);
    }

    /**
     * decrypt
     *
     * @param string $string
     * @param string $key
     * @param string $iv
     *
     * @return  string
     */
    public function decrypt($string, $key = null, $iv = null)
    {
        $key = $key ?: $this->getKey();
        $iv = $iv ?: $this->getIv();

        return $this->cipher->decrypt($string, $key, $iv);
    }

    /**
     * match
     *
     * @param string $string
     * @param string $encrypted
     * @param string $key
     * @param string $iv
     *
     * @return  boolean
     */
    public function verify($string, $encrypted, $key = null, $iv = null)
    {
        return ($string === $this->decrypt($encrypted, $key, $iv));
    }

    /**
     * Method to get property Public
     *
     * @return  string
     */
    public function getIV()
    {
        return $this->iv;
    }

    /**
     * Method to set property public
     *
     * @param   string $iv
     *
     * @return  static  Return self to support chaining.
     */
    public function setIV($iv)
    {
        $this->iv = $iv;

        return $this;
    }

    /**
     * Method to get property Private
     *
     * @return  null|string
     */
    public function getKey()
    {
        if (!$this->key) {
            $this->key = md5('To be, or not to be, that is the question.');
        }

        return $this->key;
    }

    /**
     * Method to set property private
     *
     * @param   null|string $key
     *
     * @throws \InvalidArgumentException
     * @return  static  Return self to support chaining.
     */
    public function setKey($key)
    {
        $this->key = $key;

        if (!is_string($this->key) && $this->key !== null) {
            throw new \InvalidArgumentException('Key should be string');
        }

        return $this;
    }

    /**
     * Method to get property Cipher
     *
     * @return  CipherInterface
     */
    public function getCipher()
    {
        return $this->cipher;
    }

    /**
     * Method to set property cipher
     *
     * @param   CipherInterface $cipher
     *
     * @return  static  Return self to support chaining.
     */
    public function setCipher($cipher)
    {
        $this->cipher = $cipher;

        return $this;
    }
}
