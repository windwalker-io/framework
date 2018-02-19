<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

include_once __DIR__ . '/../../../vendor/autoload.php';

var_dump([
    \Sodium\library_version_major(),
    \Sodium\library_version_minor(),
    \Sodium\version_string(),
]);

/**
 * Encrypt a message
 *
 * @param string $message - message to encrypt
 * @param string $key     - encryption key
 *
 * @return string
 */
function safeEncrypt($message, $key)
{
    $nonce = \Sodium\randombytes_buf(
        \Sodium\CRYPTO_SECRETBOX_NONCEBYTES
    );

    $cipher =
        $nonce .
        \Sodium\crypto_secretbox(
            $message,
            $nonce,
            $key
        );
    \Sodium\memzero($message);
    \Sodium\memzero($key);
    return $cipher;
}

/**
 * Decrypt a message
 *
 * @param string $encrypted - message encrypted with safeEncrypt()
 * @param string $key       - encryption key
 *
 * @return string
 */
function safeDecrypt($encrypted, $key)
{
    $decoded    = base64_decode($encrypted);
    $nonce      = mb_substr($decoded, 0, \Sodium\CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
    $ciphertext = mb_substr($decoded, \Sodium\CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');

    $plain = \Sodium\crypto_secretbox_open(
        $ciphertext,
        $nonce,
        $key
    );
    \Sodium\memzero($ciphertext);
    \Sodium\memzero($key);
    return $plain;
}

$key     = \Sodium\randombytes_buf(
    \Sodium\CRYPTO_SECRETBOX_KEYBYTES
);
$message = 'We are all living in a yellow submarine';

echo $ciphertext = safeEncrypt($message, $key);
$plaintext = safeDecrypt($ciphertext, $key);

