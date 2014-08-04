<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

include_once __DIR__ . '/../../../../../autoload.php';

$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

$iv = '321';

$key = new \Windwalker\Crypt\Key('blowfish', md5('123'), $iv);

$crypt = new \Windwalker\Crypt\Crypt(new \Windwalker\Crypt\Cipher\BlowfishCipher, $key);

echo $crypt->encrypt('Windwalker');
