<?php

declare(strict_types=1);

use Windwalker\Promise\Scheduler\ImmediateScheduler;
use Windwalker\Promise\Scheduler\ScheduleRunner;

include __DIR__ . '/../../../../vendor/autoload.php';

ScheduleRunner::getInstance()->setSchedulers([new ImmediateScheduler()]);

show(
    \Windwalker\Promise\Promise::resolve(
        \Windwalker\Promise\Promise::reject('BBB')
    )
        ->then(
            function ($e) {
                show('Then', $e);
            }
        )
);

exit(' @Checkpoint');

$promise = \Windwalker\Promise\Promise::resolve(
    \Windwalker\Promise\Promise::reject('BBB')
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
