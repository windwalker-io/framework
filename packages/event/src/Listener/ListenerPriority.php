<?php

declare(strict_types=1);

namespace Windwalker\Event\Listener;

/**
 * The ListenerPriority class.
 *
 * @since  2.0
 */
class ListenerPriority
{
    public const MIN = -300;

    public const LOW = -200;

    public const BELOW_NORMAL = -100;

    public const NORMAL = 0;

    public const ABOVE_NORMAL = 100;

    public const HIGH = 200;

    public const MAX = 300;
}
