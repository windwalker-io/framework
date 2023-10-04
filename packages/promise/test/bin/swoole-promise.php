<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

use Windwalker\Promise\Promise;

include __DIR__ . '/../../../../vendor/autoload.php';

\Windwalker\run(
    function () {
        $promise = Promise::resolved('YYYY');

        // Schedule 1
        $p = $promise->then(
            function ($v) {
                return $v;
            }
        );

        $p = $p->then(
            function ($v) {
                return $v;
            }
        );

        $p = $p->then(
            function ($v) {
                return 'GOO';
            }
        );

        // self::assertArrayNotHasKey('v1', $this->values);

        // No schedule, so auto create Schedule 2
        $value = $p->wait();

        show($value);
    }
);
