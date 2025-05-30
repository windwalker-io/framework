<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Windwalker\Event\BaseEvent;
use Windwalker\Utilities\Accessible\AccessorBCTrait;

/**
 * The HandshakeEvent class.
 */
class HandshakeEvent extends BaseEvent
{
    use AccessorBCTrait;

    public function __construct(public Request $request, public Response $response)
    {
    }
}
