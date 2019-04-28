<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Crypt;

/**
 * The SimplePassword class.
 *
 * @since  2.0
 *
 * @deprecated  Use php5 password_hash() instead.
 */
class Password implements HasherInterface
{
    const MD5 = 3;

    const BLOWFISH = 4;

    const SHA256 = 5;

    const SHA512 = 6;

    const SODIUM_ARGON2 = 7;

    const SODIUM_SCRYPT = 8;

    /**
     * Property salt.
     *
     * @var  string
     */
    protected $salt;

    /**
     * Property cost.
     *
     * @var  integer
     */
    protected $cost;

    /**
     * Property type.
     *
     * @var  int
     */
    protected $type;

    /**
     * Constructor.
     *
     * @param int    $type
     * @param int    $cost
     * @param string $salt
     *
     * @throws \DomainException
     */
    public function __construct($type = self::BLOWFISH, $cost = 10, $salt = null)
    {
        $this->setSalt($salt);
        $this->setCost($cost);
        $this->setType($type);
    }

    /**
     * create
     *
     * @param string $password
     *
     * @return  string
     */
    public function create($password)
    {
        if (!$this->salt) {
            $salt = str_replace('+', '.', base64_encode(CryptHelper::genRandomBytes(64)));
        } else {
            $salt = $this->salt;
        }

        switch ($this->type) {
            case static::MD5:
                $salt = '$1$' . $salt . '$';
                break;

            case static::SHA256:
                $cost = CryptHelper::limitInteger($this->cost, 1000);

                $salt = '$5$rounds=' . $cost . '$' . $salt . '$';
                break;

            case static::SHA512:
                $cost = CryptHelper::limitInteger($this->cost, 1000);

                $salt = '$6$rounds=' . $cost . '$' . $salt . '$';
                break;

            case static::SODIUM_ARGON2:
                return \Sodium\crypto_pwhash_str(
                    $password,
                    \Sodium\CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
                    \Sodium\CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE
                );

            case static::SODIUM_SCRYPT:
                return \Sodium\crypto_pwhash_scryptsalsa208sha256_str(
                    $password,
                    \Sodium\CRYPTO_PWHASH_SCRYPTSALSA208SHA256_OPSLIMIT_INTERACTIVE,
                    \Sodium\CRYPTO_PWHASH_SCRYPTSALSA208SHA256_MEMLIMIT_INTERACTIVE
                );

            default:
            case static::BLOWFISH:
                $salt = CryptHelper::repeatToLength($salt, 22, true);
                $cost = CryptHelper::limitInteger($this->cost, 4, 31);

                $options['cost'] = $cost;

                return password_hash($password, PASSWORD_BCRYPT, $options);
                break;
        }

        if (!function_exists('crypt')) {
            throw new \RangeException("crypt() must be loaded for Password::create method");
        }

        return crypt($password, $salt);
    }

    /**
     * Verify the password.
     *
     * @param   string $password The password plain text.
     * @param   string $hash     The hashed password.
     *
     * @return  boolean  Verify success or not.
     *
     * @see  https://github.com/ircmaxell/password_compat/blob/92951ae05e988803fdc1cd49f7e4cd29ca7b75e9/lib/password.php#L230-L247
     */
    public function verify($password, $hash)
    {
        if (static::isSodiumAlgo($this->type)) {
            return $this->verifySodium($password, $hash);
        }

        return password_verify($password, $hash);
    }

    /**
     * verifySodium
     *
     * @param string $password
     * @param string $hash
     *
     * @return  bool
     */
    protected function verifySodium($password, $hash)
    {
        if (strpos($hash, '$argon2i') === 0) {
            $result = \Sodium\crypto_pwhash_str_verify($hash, $password);

            \Sodium\memzero($password);

            return $result;
        } elseif (strpos($hash, '$7$C6') === 0) {
            $result = \Sodium\crypto_pwhash_scryptsalsa208sha256_str_verify($hash, $password);

            \Sodium\memzero($password);

            return $result;
        }

        return false;
    }

    /**
     * Generate a random password.
     *
     * This is a fork of Joomla JUserHelper::genRandomPassword()
     *
     * @param   integer $length Length of the password to generate
     *
     * @return  string  Random Password
     *
     * @see     https://github.com/joomla/joomla-cms/blob/staging/libraries/joomla/user/helper.php#L642
     * @since   2.0.9
     */
    public static function genRandomPassword($length = 8)
    {
        $salt = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $base = strlen($salt);
        $password = '';

        /*
         * Start with a cryptographic strength random string, then convert it to
         * a string with the numeric base of the salt.
         * Shift the base conversion on each character so the character
         * distribution is even, and randomize the start shift so it's not
         * predictable.
         */
        $random = CryptHelper::genRandomBytes($length + 1);
        $shift = ord($random[0]);

        for ($i = 1; $i <= $length; ++$i) {
            $password .= $salt[($shift + ord($random[$i])) % $base];

            $shift += ord($random[$i]);
        }

        return $password;
    }

    /**
     * Method to get property Salt
     *
     * @return  string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Method to set property salt
     *
     * @param   string $salt
     *
     * @return  static  Return self to support chaining.
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Method to get property Cost
     *
     * @return  int
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Method to set property cost
     *
     * @param   int $cost
     *
     * @throws \InvalidArgumentException
     * @return  static  Return self to support chaining.
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Method to get property Type
     *
     * @return  int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Method to set property type
     *
     * @param   int $type
     *
     * @return  static  Return self to support chaining.
     * @throws \DomainException
     */
    public function setType($type)
    {
        $this->type = $type;

        if (static::isSodiumAlgo($this->type)) {
            if (!extension_loaded('libsodium')) {
                throw new \DomainException('Please install php ext-libsodium >= 1.0.9 to use sodium pwhash().');
            }

            if (!defined('\Sodium\CRYPTO_PWHASH_SALTBYTES')) {
                throw new \DomainException('Libsodium must higher than 1.0.9 to use Argon2 and Scrypt algo.');
            }
        }

        return $this;
    }

    /**
     * isSodiumAlgo
     *
     * @param int $type
     *
     * @return  bool
     */
    public static function isSodiumAlgo($type)
    {
        return $type === static::SODIUM_ARGON2 || $type === static::SODIUM_SCRYPT;
    }

    /**
     * isSodiumHash
     *
     * @param string $hash
     *
     * @return  bool
     */
    public static function isSodiumHash($hash)
    {
        return (strpos($hash, '$argon2i') === 0 || strpos($hash, '$7$C6') === 0);
    }
}
