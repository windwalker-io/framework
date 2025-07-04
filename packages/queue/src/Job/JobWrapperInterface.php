<?php

declare(strict_types=1);

namespace Windwalker\Queue\Job;

// phpcs:disable
interface JobWrapperInterface
{
    public function process(JobController $controller): void;

    public function failed(\Throwable $e): void;
}
