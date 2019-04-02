<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Crypt\Cipher;

use Windwalker\Crypt\CryptHelper;

if (!defined('OPENSSL_RAW_DATA')) {
    define('OPENSSL_RAW_DATA', 1);
}

/**
 * The Openssl Cipher class.
 *
 * @note   This cipher class referenced Eugene Fidelin and Peter Mortensen's code to prevent
 *       Chosen-Cipher and Timing attack.
 *
 * @see    http://stackoverflow.com/a/19445173
 *
 * @since  2.0
 */
abstract class AbstractCipher implements CipherInterface
{
    const PBKDF2_HASH_ALGORITHM = 'SHA256';

    const PBKDF2_SALT_BYTE_SIZE = 32;

    const PBKDF2_HASH_BYTE_SIZE = 32;

    /**
     * Property iv.
     *
     * @var string
     */
    protected $iv;

    /**
     * Property key.
     *
     * @var  string
     */
    protected $privateKey;

    /**
     * Property pbkdf2Salt.
     *
     * @var  string
     */
    protected $pbkdf2Salt;

    /**
     * Property secureEncryptionKey.
     *
     * @var  string
     */
    protected $secureEncryptionKey;

    /**
     * Property secureHMACKey.
     *
     * @var  string
     */
    protected $secureHMACKey;

    /**
     * Property options.
     *
     * @var  array
     */
    protected $options;

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
        $this->privateKey = $key;
        $this->options = array_merge(
            [
                'pbkdf2_iteration' => 12000,
            ],
            $options
        );
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
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     *
     * @since   2.0
     */
    public function encrypt($data, $key = null, $iv = null)
    {
        $key = $key === null ? $this->privateKey : $key;

        $this->derivateSecureKeys($key);

        $iv = $iv ?: $this->getIVKey();

        $key = CryptHelper::repeatToLength($key, 24, true);

        // Encrypt the data.
        $encrypted = $this->doEncrypt($data, $key, $iv);

        $hmac = $this->hmac($this->pbkdf2Salt . $iv . $encrypted);

        return implode(
            ':',
            [
                base64_encode($hmac),
                base64_encode($this->pbkdf2Salt),
                base64_encode($iv),
                base64_encode($encrypted),
            ]
        );
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
    abstract protected function doEncrypt($data, $key, $iv);

    /**
     * Method to decrypt a data string.
     *
     * @param   string $data The encrypted string to decrypt.
     * @param   string $key  The private key.
     * @param   string $iv   The public key.
     *
     * @return  string  The decrypted data string.
     *
     * @throws \RuntimeException
     *
     * @since    2.0
     */
    public function decrypt($data, $key = null, $iv = null)
    {
        $key = $key === null ? $this->privateKey : $key;

        if (strpos($data, ':') !== false) {
            list($hmac, $pbkdf2Salt, $ivFromData, $encrypted) = explode(':', $data);

            $hmac = base64_decode($hmac);
            $pbkdf2Salt = base64_decode($pbkdf2Salt);
            $ivFromData = base64_decode($ivFromData);
            $encrypted = base64_decode($encrypted);

            $iv = $iv ?: $ivFromData;

            $this->derivateSecureKeys($key, $pbkdf2Salt);

            $calculatedHmac = $this->hmac($pbkdf2Salt . $iv . $encrypted);

            if (!$this->equalHashes($calculatedHmac, $hmac)) {
                throw new \RuntimeException('HMAC ERROR: Invalid HMAC.');
            }
        } else {
            // For 3.1 and older legacy
            $data = base64_decode($data);

            if (!$iv) {
                $ivSize = $this->getIVSize();

                $iv = substr($data, 0, $ivSize);

                $encrypted = substr($data, $ivSize);
            } else {
                $ivSize = $this->getIVSize();

                if (substr($data, 0, $ivSize) === $iv) {
                    $encrypted = substr($data, $ivSize);
                } else {
                    $encrypted = $data;
                }
            }
        }

        $key = CryptHelper::repeatToLength($key, 24, true);

        // Decrypt the data.
        return trim($this->doDecrypt($encrypted, $key, $iv));
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
    abstract protected function doDecrypt($data, $key, $iv);

    /**
     * Compares two strings.
     *
     * This method implements a constant-time algorithm to compare strings.
     * Regardless of the used implementation, it will leak length information.
     *
     * @param string $knownHash The string of known length to compare against
     * @param string $userHash  The string that the user can control
     *
     * @return bool true if the two strings are the same, false otherwise
     *
     * @see https://github.com/symfony/security-core/blob/master/Util/StringUtils.php
     */
    protected function equalHashes($knownHash, $userHash)
    {
        if (function_exists('hash_equals')) {
            return hash_equals($knownHash, $userHash);
        }

        $knownLen = strlen($knownHash);
        $userLen = strlen($userHash);

        if ($userLen !== $knownLen) {
            return false;
        }

        $result = 0;

        for ($i = 0; $i < $knownLen; $i++) {
            $result |= (ord($knownHash[$i]) ^ ord($userHash[$i]));
        }

        // They are only identical strings if $result is exactly 0...
        return 0 === $result;
    }

    /**
     * getIVKey
     *
     * @return  string
     *
     * @throws \RuntimeException
     */
    public function getIVKey()
    {
        if (!$this->iv) {
            $ivSize = $this->getIVSize();

            $this->iv = $this->randomPseudoBytes($ivSize);
        }

        return $this->iv;
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
    abstract protected function randomPseudoBytes($size = null);

    /**
     * getIVSize
     *
     * @return  integer
     */
    abstract public function getIVSize();

    /**
     * Method to get property PrivateKey
     *
     * @return  string
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * Method to set property privateKey
     *
     * @param   string $privateKey
     *
     * @return  static  Return self to support chaining.
     */
    public function setPrivateKey($privateKey)
    {
        $this->privateKey = $privateKey;

        return $this;
    }

    /**
     * Creates secure PBKDF2 derivatives out of the password.
     *
     * @param string $key
     * @param string $pbkdf2Salt
     *
     * @throws \RuntimeException
     */
    protected function derivateSecureKeys($key, $pbkdf2Salt = null)
    {
        if (!$pbkdf2Salt) {
            if (!$this->pbkdf2Salt) {
                $this->pbkdf2Salt = $this->randomPseudoBytes(static::PBKDF2_SALT_BYTE_SIZE);
            }

            $pbkdf2Salt = $this->pbkdf2Salt;
        }

        $iteration = $this->options['pbkdf2_iteration'] ?: 12000;

        list($this->secureEncryptionKey, $this->secureHMACKey) = str_split(
            $this->pbkdf2(
                static::PBKDF2_HASH_ALGORITHM,
                $key,
                $pbkdf2Salt,
                $iteration,
                static::PBKDF2_HASH_BYTE_SIZE * 2,
                true
            ),
            self::PBKDF2_HASH_BYTE_SIZE
        );
    }

    /**
     * Calculates HMAC for the message.
     *
     * @param string $message
     *
     * @return string
     */
    protected function hmac($message)
    {
        return hash_hmac(self::PBKDF2_HASH_ALGORITHM, $message, $this->secureHMACKey, true);
    }

    /**
     * PBKDF2 key derivation function as defined by RSA's PKCS #5: https://www.ietf.org/rfc/rfc2898.txt
     *
     * Test vectors can be found here: https://www.ietf.org/rfc/rfc6070.txt
     * This implementation of PBKDF2 was originally created by https://defuse.ca
     * With improvements by http://www.variations-of-shadow.com
     *
     * @param string $algorithm  The hash algorithm to use. Recommended: SHA256
     * @param string $password   The password
     * @param string $salt       A salt that is unique to the password
     * @param int    $count      Iteration count. Higher is better, but slower. Recommended: At least 1000
     * @param int    $key_length The length of the derived key in bytes
     * @param bool   $raw_output If true, the key is returned in raw binary format. Hex encoded otherwise
     *
     * @return string A $key_length-byte key derived from the password and salt
     *
     * @see https://defuse.ca/php-pbkdf2.htm
     */
    protected function pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output = false)
    {
        $algorithm = strtolower($algorithm);

        if (!in_array($algorithm, hash_algos(), true)) {
            trigger_error('PBKDF2 ERROR: Invalid hash algorithm.', E_USER_ERROR);
        }

        if ($count <= 0 || $key_length <= 0) {
            trigger_error('PBKDF2 ERROR: Invalid parameters.', E_USER_ERROR);
        }

        return hash_pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output);
    }
}
