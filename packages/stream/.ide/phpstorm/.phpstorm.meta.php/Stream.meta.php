<?php

namespace PHPSTORM_META {

    registerArgumentsSet(
        'stream_modes',
        \Windwalker\Stream\READ_ONLY_FROM_BEGIN,
        \Windwalker\Stream\READ_WRITE_FROM_BEGIN,
        \Windwalker\Stream\WRITE_ONLY_RESET,
        \Windwalker\Stream\READ_WRITE_RESET,
        \Windwalker\Stream\WRITE_ONLY_FROM_END,
        \Windwalker\Stream\READ_WRITE_FROM_END,
        \Windwalker\Stream\WRITE_CREATE_ONLY_FROM_BEGIN,
        \Windwalker\Stream\READ_WRITE_CREATE_ONLY_FROM_BEGIN,
        \Windwalker\Stream\WRITE_ONLY_CREATE_FROM_BEGIN,
        \Windwalker\Stream\READ_WRITE_CREATE_FROM_BEGIN,
    );

    expectedArguments(
        \Windwalker\Stream\Stream::__construct(),
        1,
        argumentsSet('stream_modes')
    );
}
