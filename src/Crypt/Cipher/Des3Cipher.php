<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Crypt\Cipher;

/**
 * The Cipher3DES class.
 *
 * @since  2.0
 */
class Des3Cipher extends AbstractOpensslCipher
{
    /**
     * @var    integer  The openssl cipher method.
     * @see    http://php.net/manual/en/function.openssl-get-cipher-methods.php
     * @since  3.0
     */
    protected $method = 'des-ede3';

    /**
     * Property mode.
     *
     * @var  string
     */
    protected $mode = 'cbc';
}
