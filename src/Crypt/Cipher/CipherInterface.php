<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Crypt\Cipher;

/**
 * Interface CipherInterface
 *
 * @since  2.0
 */
interface CipherInterface
{
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
    public function decrypt($data, $key = null, $iv = null);

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
    public function encrypt($data, $key = null, $iv = null);
}
