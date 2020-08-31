<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

use Opis\Closure\SerializableClosure;

include_once __DIR__ . '/functions.php';

if (
    ini_get('ffi.enable')
    && class_exists(SerializableClosure::class)
    && class_exists(\FFI::class)
) {
    // SerializableClosure::init();
}
