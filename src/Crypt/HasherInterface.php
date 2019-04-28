<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 Asikart.com.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Crypt;

/**
 * Interface HashInterface
 *
 * @since  3.2.2
 */
interface HasherInterface
{
    /**
     * create
     *
     * @param string $text
     *
     * @return  string
     */
    public function create($text);

    /**
     * Verify the password.
     *
     * @param   string $text The plain text.
     * @param   string $hash The hashed text.
     *
     * @return  boolean  Verify success or not.
     */
    public function verify($text, $hash);
}
