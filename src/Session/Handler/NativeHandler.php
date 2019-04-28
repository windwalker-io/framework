<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Session\Handler;

/**
 * Class PhpHandler
 *
 * @since 2.0
 */
class NativeHandler extends \SessionHandler implements HandlerInterface
{
    /**
     * isSupported
     *
     * @return  boolean
     */
    public static function isSupported()
    {
        return true;
    }

    /**
     * register
     *
     * @return  mixed
     */
    public function register()
    {
        return true;
    }
}
