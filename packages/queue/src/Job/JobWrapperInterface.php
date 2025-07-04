<?php

declare(strict_types=1);

namespace Windwalker\Queue\Job;

// phpcs:disable
interface JobWrapperInterface
{
    public function beforeProcess(JobController $controller): void;

    public function process(JobController $controller): void;

    public function afterProcess(JobController $controller): void;

    public function failed(\Throwable $e): void;
}
