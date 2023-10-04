<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

use Windwalker\Promise\Scheduler\ImmediateScheduler;
use Windwalker\Promise\Scheduler\ScheduleRunner;

include __DIR__ . '/../../../../vendor/autoload.php';

ScheduleRunner::getInstance()->setSchedulers([new ImmediateScheduler()]);

show(
    \Windwalker\Promise\Promise::resolved(
        \Windwalker\Promise\Promise::rejected('BBB')
    )
        ->then(
            function ($e) {
                show('Then', $e);
            }
        )
);

exit(' @Checkpoint');

$promise = \Windwalker\Promise\Promise::resolved(
    \Windwalker\Promise\Promise::rejected('BBB')
)
    ->then(
        function ($e) {
            show('Then', $e);
        }
    )
    ->catch(
        function ($e) {
            show('Catch', $e);
            return $e;
        }
    );
