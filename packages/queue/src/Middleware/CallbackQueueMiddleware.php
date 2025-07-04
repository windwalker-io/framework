<?php

declare(strict_types=1);

namespace Windwalker\Queue\Middleware;

use Windwalker\Queue\Job\JobController;

class CallbackQueueMiddleware implements QueueMiddlewareInterface
{
    public function __construct(protected \Closure $callback)
    {
    }

    public function process(JobController $controller, QueueMiddlewareHandler $handler)
    {
        return ($this->callback)($controller, $handler);
    }
}
