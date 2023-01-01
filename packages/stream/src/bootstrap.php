<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace {
    include_once __DIR__ . '/functions.php';
}

namespace Windwalker\Stream {
    const READ_ONLY_FROM_BEGIN = 'rb';

    const READ_WRITE_FROM_BEGIN = 'rb+';

    const WRITE_ONLY_RESET = 'wb';

    const READ_WRITE_RESET = 'wb+';

    const WRITE_ONLY_FROM_END = 'ab';

    const READ_WRITE_FROM_END = 'ab+';

    const WRITE_CREATE_ONLY_FROM_BEGIN = 'xb';

    const READ_WRITE_CREATE_ONLY_FROM_BEGIN = 'xb+';

    const WRITE_ONLY_CREATE_FROM_BEGIN = 'cb';

    const READ_WRITE_CREATE_FROM_BEGIN = 'cb+';
}
