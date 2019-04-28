<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Crypt\Cipher;

/**
 * The BlowfishChipher class.
 *
 * @since  2.0
 */
class BlowfishCipher extends AbstractOpensslCipher
{
    /**
     * @var    integer  The openssl cipher method.
     * @see    http://php.net/manual/en/function.openssl-get-cipher-methods.php
     * @since  3.0
     */
    protected $method = 'bf';
}
