<?php

declare(strict_types=1);

namespace Windwalker\Pool\Enum;

enum ConnectionState
{
    case INACTIVE;
    case ACTIVE;
    case ABANDONED;
}
