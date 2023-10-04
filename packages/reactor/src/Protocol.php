<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Reactor;

/**
 * The Protocol class.
 */
abstract class Protocol
{
    public const TCP = 1 << 0;
    public const HTTP = 1 << 1;
    public const HTTP2 = 1 << 2;
    public const WEBSOCKET = 1 << 3;
    public const MQTT = 1 << 4;
}
