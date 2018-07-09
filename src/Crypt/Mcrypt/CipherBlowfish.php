<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Crypt\Mcrypt;

/**
 * The BlowfishChipher class.
 *
 * @since       2.0
 * @deprecated  PHP7 already deprecated mcrypt extension
 */
class CipherBlowfish extends AbstractMcryptCipher
{
    /**
     * @var int The mcrypt cipher constant.
     *
     * @see    http://www.php.net/manual/en/mcrypt.ciphers.php
     * @since  2.0
     */
    protected $type = MCRYPT_BLOWFISH;

    /**
     * @var int The mcrypt block cipher mode.
     *
     * @see    http://www.php.net/manual/en/mcrypt.constants.php
     * @since  2.0
     */
    protected $mode = MCRYPT_MODE_CBC;
}
