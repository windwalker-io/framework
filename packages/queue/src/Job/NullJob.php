<?php

declare(strict_types=1);

namespace Windwalker\Queue\Job;

/**
 * The NullJob class.
 *
 * @since  3.2
 */
class NullJob
{
    public function __invoke(): void
    {
    }
}
