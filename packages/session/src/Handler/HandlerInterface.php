<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Session\Handler;

use SessionHandlerInterface;

/**
 * Interface HandlerInterface
 */
interface HandlerInterface extends SessionHandlerInterface
{
    /**
     * isSupported
     *
     * @return  bool
     */
    public static function isSupported(): bool;
}
