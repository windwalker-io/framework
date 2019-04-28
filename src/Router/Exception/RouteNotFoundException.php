<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Router\Exception;

use Exception;

/**
 * The RouteNotFoundException class.
 *
 * @since  2.0
 */
class RouteNotFoundException extends \RuntimeException
{
    /**
     * RouteNotFoundException constructor.
     *
     * @param string    $message
     * @param int       $code
     * @param Exception $previous
     */
    public function __construct($message = '', $code = 404, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
