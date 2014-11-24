<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

include_once __DIR__ . '/../../../vendor/autoload.php';

$crypt = new \Windwalker\Crypt\Crypt(new \Windwalker\Crypt\Cipher\CipherBlowfish);

echo $pass = $crypt->encrypt('Windwalker');
echo "\n\n";

$crypt = new \Windwalker\Crypt\Crypt(new \Windwalker\Crypt\Cipher\CipherBlowfish);

echo $crypt->decrypt($pass);
