<?php

declare(strict_types=1);

namespace Windwalker\Queue\Middleware;

use Windwalker\Queue\Job\JobController;

interface QueueMiddlewareInterface
{
    public function process(JobController $controller, QueueMiddlewareHandler $handler): JobController;
}
