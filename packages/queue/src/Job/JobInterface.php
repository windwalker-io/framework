<?php

declare(strict_types=1);

namespace Windwalker\Queue\Job;

/**
 * The AbstractJob class.
 *
 * @since  3.2
 */
interface JobInterface
{
    public function getName(): string;

    public function __invoke(): void;
}
