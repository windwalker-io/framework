<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

include_once __DIR__ . '/../../../vendor/autoload.php';

$crypt = new \Windwalker\Crypt\Crypt(new \Windwalker\Crypt\Mcrypt\CipherBlowfish);

echo $pass = $crypt->encrypt('Windwalker');
echo "\n\n";

$crypt = new \Windwalker\Crypt\Crypt(new \Windwalker\Crypt\Mcrypt\CipherBlowfish);

echo $crypt->decrypt($pass);
