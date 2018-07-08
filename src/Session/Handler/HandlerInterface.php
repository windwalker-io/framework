<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Session\Handler;

/**
 * Interface HandlerInterface
 */
interface HandlerInterface extends \SessionHandlerInterface
{
    /**
     * isSupported
     *
     * @return  boolean
     */
    public static function isSupported();

    /**
     * register
     *
     * @return  mixed
     */
    public function register();
}
