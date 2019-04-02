<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

use Windwalker\Event\Dispatcher;
use Windwalker\Queue\Queue;
use Windwalker\Queue\Worker;
use Windwalker\Structure\Structure;

$worker = new Worker(new Queue(), new Dispatcher());

$options = [
    'timeout' => 30, // Number of seconds that a job can run.
    'delay' => 0, // Delay time for failed job to wait next run.
    'force' => false, // Force run worker if in pause mode.
    'memory' => 128, // The memory limit in megabytes.
    'sleep' => 1, // Number of seconds to sleep after job run complete.
    'tries' => 5, // Number of times to attempt a job if it failed.
];

$worker->loop(['default', 'flower'], new Structure($options));
