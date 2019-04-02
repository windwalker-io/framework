<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Crypt\Mcrypt;

use Windwalker\Crypt\Cipher\CipherInterface;
use Windwalker\Crypt\CryptHelper;

/**
 * The McryptCipher class.
 *
 * @since       2.0
 *
 * @deprecated  PHP7 already deprecated mcrypt extension
 */
abstract class AbstractMcryptCipher implements CipherInterface
{
    /**
     * Property type.
     *
     * @var string
     */
    protected $type;

    /**
     * Property mode.
     *
     * @var string
     */
    protected $mode;

    /**
     * Property iv.
     *
     * @var string
     */
    protected $iv;

    /**
     * Constructor.
     *
     * @since   2.0
     * @throws  \RuntimeException
     */
    public function __construct()
    {
        if (!is_callable('mcrypt_encrypt')) {
            throw new \RuntimeException('The mcrypt extension is not available.');
        }
    }

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
        $data = base64_decode($data);

        if (!$iv) {
            $ivSize = $this->getIVSize();

            $iv = substr($data, 0, $ivSize);

            $data = substr($data, $ivSize);
        } else {
            $ivSize = $this->getIVSize();

            if (substr($data, 0, $ivSize) === $iv) {
                $data = substr($data, $ivSize);
            }
        }

        $key = CryptHelper::repeatToLength($key, 24, true);

        // Decrypt the data.
        $decrypted = trim(mcrypt_decrypt($this->type, $key, $data, $this->mode, $iv));

        return $decrypted;
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
        // @see https://github.com/ventoviro/windwalker-crypt/issues/2
        throw new \LogicException(
            'Sorry, due to the secure issue, McryptCipher is nolonger supported, you can only decrypt data now.'
        );

        return;

        $iv = $iv ?: $this->getIVKey();

        $key = CryptHelper::repeatToLength($key, 24, true);

        // Encrypt the data.
        $encrypted = mcrypt_encrypt($this->type, $key, $data, $this->mode, $iv);

        return base64_encode($iv . $encrypted);
    }

    /**
     * getIVKey
     *
     * @return  string
     */
    public function getIVKey()
    {
        if (!$this->iv) {
            $ivSize = $this->getIVSize();

            $this->iv = mcrypt_create_iv($ivSize, MCRYPT_RAND);
        }

        return $this->iv;
    }

    /**
     * getIVSize
     *
     * @return  integer
     */
    public function getIVSize()
    {
        return mcrypt_get_iv_size($this->type, $this->mode);
    }
}
