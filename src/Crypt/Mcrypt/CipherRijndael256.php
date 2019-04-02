<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Crypt\Mcrypt;

/**
 * The Rijndael256 class.
 *
 * @since       2.0
 *
 * @deprecated  PHP7 already deprecated mcrypt extension
 */
class CipherRijndael256 extends AbstractMcryptCipher
{
    /**
     * @var    integer  The mcrypt cipher constant.
     * @see    http://www.php.net/manual/en/mcrypt.ciphers.php
     * @since  2.0
     */
    protected $type = MCRYPT_RIJNDAEL_256;

    /**
     * @var    integer  The mcrypt block cipher mode.
     * @see    http://www.php.net/manual/en/mcrypt.constants.php
     * @since  2.0
     */
    protected $mode = MCRYPT_MODE_CBC;
}
