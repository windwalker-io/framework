<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
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
