<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

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
