<?php

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
