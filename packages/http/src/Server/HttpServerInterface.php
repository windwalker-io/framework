<?php

declare(strict_types=1);

namespace Windwalker\Http\Server;

/**
 * Interface HttpServerInterface
 */
interface HttpServerInterface
{
    public function onRequest(callable $listener, ?int $priority = null): static;

    public function onResponse(callable $listener, ?int $priority = null): static;
}
