<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Crypt\Cipher;

/**
 * The Aes256Cipher class.
 *
 * @since  3.0
 */
class Aes256Cipher extends AbstractOpensslCipher
{
    /**
     * @var    integer  The openssl cipher method.
     * @see    http://php.net/manual/en/function.openssl-get-cipher-methods.php
     * @since  3.0
     */
    protected $method = 'aes-256';

    /**
     * Property mode.
     *
     * @var  string
     */
    protected $mode = 'cbc';
}
