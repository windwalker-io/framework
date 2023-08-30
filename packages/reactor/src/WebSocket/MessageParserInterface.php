<?php

/**
 * Part of cati project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
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
