<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Reactor\WebSocket;

/**
 * Interface MessageParserInterface
 */
interface MessageParserInterface
{
    public function parse(WebSocketFrame $frame);

    public function parseRequest();
}
