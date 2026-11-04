<?php

declare(strict_types=1);

namespace {
    include_once __DIR__ . '/functions.php';
}

namespace Windwalker\Stream {
    if (!defined('Windwalker\Stream\READ_ONLY_FROM_BEGIN')) {
        define('Windwalker\Stream\READ_ONLY_FROM_BEGIN', 'rb');
    }

    if (!defined('Windwalker\Stream\READ_WRITE_FROM_BEGIN')) {
        define('Windwalker\Stream\READ_WRITE_FROM_BEGIN', 'rb+');
    }

    if (!defined('Windwalker\Stream\WRITE_ONLY_RESET')) {
        define('Windwalker\Stream\WRITE_ONLY_RESET', 'wb');
    }

    if (!defined('Windwalker\Stream\READ_WRITE_RESET')) {
        define('Windwalker\Stream\READ_WRITE_RESET', 'wb+');
    }

    if (!defined('Windwalker\Stream\WRITE_ONLY_FROM_END')) {
        define('Windwalker\Stream\WRITE_ONLY_FROM_END', 'ab');
    }

    if (!defined('Windwalker\Stream\READ_WRITE_FROM_END')) {
        define('Windwalker\Stream\READ_WRITE_FROM_END', 'ab+');
    }

    if (!defined('Windwalker\Stream\WRITE_CREATE_ONLY_FROM_BEGIN')) {
        define('Windwalker\Stream\WRITE_CREATE_ONLY_FROM_BEGIN', 'xb');
    }

    if (!defined('Windwalker\Stream\READ_WRITE_CREATE_ONLY_FROM_BEGIN')) {
        define('Windwalker\Stream\READ_WRITE_CREATE_ONLY_FROM_BEGIN', 'xb+');
    }

    if (!defined('Windwalker\Stream\WRITE_ONLY_CREATE_FROM_BEGIN')) {
        define('Windwalker\Stream\WRITE_ONLY_CREATE_FROM_BEGIN', 'cb');
    }

    if (!defined('Windwalker\Stream\READ_WRITE_CREATE_FROM_BEGIN')) {
        define('Windwalker\Stream\READ_WRITE_CREATE_FROM_BEGIN', 'cb+');
    }
}
