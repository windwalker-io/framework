<?php

/**
 * Part of framework project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

use Windwalker\Promise\Promise;

include __DIR__ . '/../../../../vendor/autoload.php';

\Windwalker\run(
    function () {
        $promise = Promise::resolved('YYYY');

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

        $value = $p->wait();

        show($value);
    }
);
