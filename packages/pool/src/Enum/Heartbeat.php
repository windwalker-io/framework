<?php

declare(strict_types=1);

namespace Windwalker\Pool\Enum;

enum Heartbeat
{
    case NONE;
    case LAZY;
    case INTERVALS;
}
