<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Crypt\Cipher;

/**
 * The Simple class.
 *
 * @since       2.0
 *
 * @deprecated  This cipher is not safe.
 *
 * @link        https://github.com/ventoviro/windwalker/issues/260
 * @link        http://www.openwall.com/lists/oss-security/2015/11/08/1
 */
class SimpleCipher implements CipherInterface
{
    const DEFAULT_RANDOM_BYTE_LENGTH = 256;

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
            $iv = substr($data, 0, static::DEFAULT_RANDOM_BYTE_LENGTH);

            $data = substr($data, static::DEFAULT_RANDOM_BYTE_LENGTH);
        } else {
            if (substr($data, 0, strlen($iv)) === $iv) {
                $data = substr($data, strlen($iv));
            }
        }

        $key = sha1($iv . $key);

        return $this->doDecrypt($data, $key);
    }

    /**
     * doDecrypt
     *
     * @param   string $data
     * @param   string $key
     *
     * @return  string
     */
    protected function doDecrypt($data, $key)
    {
        $decrypted = '';

        // Convert the HEX input into an array of integers and get the number of characters.
        $chars = $this->hexToIntArray($data);

        // Make sure private key is as long as chars length
        $key = str_repeat($key, (int) ceil(count($chars) / strlen($key)));

        // Get the XOR values between the ASCII values of the input and key characters for all input offsets.
        foreach ($chars as $i => $char) {
            $decrypted .= chr($char ^ ord($key[$i]));
        }

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
        $iv = $iv ?: $this->getRandomKey(static::DEFAULT_RANDOM_BYTE_LENGTH);

        $key = sha1($iv . $key);

        return base64_encode($iv . $this->doEncrypt($data, $key));
    }

    /**
     * doEncrypt
     *
     * @param   string $data The data string to encrypt.
     * @param   string $key  The key to encrypt data.
     *
     * @return  string
     */
    protected function doEncrypt($data, $key)
    {
        $encrypted = '';

        // Split up the input into a character array and get the number of characters.
        $chars = preg_split('//', $data, -1, PREG_SPLIT_NO_EMPTY);

        // Make sure private key is as long as chars length
        $key = str_repeat($key, (int) ceil(count($chars) / strlen($key)));

        // Get the XOR values between the ASCII values of the input and key characters for all input offsets.
        foreach ($chars as $i => $char) {
            $encrypted .= $this->intToHex(ord($key[$i]) ^ ord($char));
        }

        return $encrypted;
    }

    /**
     * Method to generate a random key of a given length.
     *
     * @param   integer $length The length of the key to generate.
     *
     * @return  string
     *
     * @since   2.0
     */
    private function getRandomKey($length = self::DEFAULT_RANDOM_BYTE_LENGTH)
    {
        $key = '';
        $salt = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $saltLength = strlen($salt);

        // Build the random key.
        for ($i = 0; $i < $length; $i++) {
            $key .= $salt[mt_rand(0, $saltLength - 1)];
        }

        return $key;
    }

    /**
     * Convert hex to an integer
     *
     * @param   string  $s The hex string to convert.
     * @param   integer $i The offset?
     *
     * @return  integer
     *
     * @since   2.0
     */
    private function hexToInt($s, $i)
    {
        $j = (int) $i * 2;
        $k = 0;
        $s1 = (string) $s;

        // Get the character at position $j.
        $c = substr($s1, $j, 1);

        // Get the character at position $j + 1.
        $c1 = substr($s1, $j + 1, 1);

        switch ($c) {
            case 'A':
                $k += 160;
                break;
            case 'B':
                $k += 176;
                break;
            case 'C':
                $k += 192;
                break;
            case 'D':
                $k += 208;
                break;
            case 'E':
                $k += 224;
                break;
            case 'F':
                $k += 240;
                break;
            case ' ':
                $k += 0;
                break;
            default:
                $k = $k + (16 * (int) $c);
                break;
        }

        switch ($c1) {
            case 'A':
                $k += 10;
                break;
            case 'B':
                $k += 11;
                break;
            case 'C':
                $k += 12;
                break;
            case 'D':
                $k += 13;
                break;
            case 'E':
                $k += 14;
                break;
            case 'F':
                $k += 15;
                break;
            case ' ':
                $k += 0;
                break;
            default:
                $k += (int) $c1;
                break;
        }

        return $k;
    }

    /**
     * Convert hex to an array of integers
     *
     * @param   string $hex The hex string to convert to an integer array.
     *
     * @return  array  An array of integers.
     *
     * @since   2.0
     */
    private function hexToIntArray($hex)
    {
        $array = [];

        $j = (int) strlen($hex) / 2;

        for ($i = 0; $i < $j; $i++) {
            $array[$i] = (int) $this->hexToInt($hex, $i);
        }

        return $array;
    }

    /**
     * Convert an integer to a hexadecimal string.
     *
     * @param   integer $i An integer value to convert to a hex string.
     *
     * @return  string
     *
     * @since   2.0
     */
    private function intToHex($i)
    {
        // Sanitize the input.
        $i = (int) $i;

        // Get the first character of the hexadecimal string if there is one.
        $j = (int) ($i / 16);

        if ($j === 0) {
            $s = ' ';
        } else {
            $s = strtoupper(dechex($j));
        }

        // Get the second character of the hexadecimal string.
        $k = $i - $j * 16;
        $s = $s . strtoupper(dechex($k));

        return $s;
    }
}
