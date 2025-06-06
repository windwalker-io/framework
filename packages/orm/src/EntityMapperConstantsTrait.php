<?php

declare(strict_types=1);

namespace Windwalker\ORM;

trait EntityMapperConstantsTrait
{
    public const int UPDATE_NULLS = 1 << 0;

    public const int IGNORE_EVENTS = 1 << 1;

    public const int IGNORE_OLD_DATA = 1 << 2;

    public const int TRANSACTION = 1 << 3;

    public const int FOR_UPDATE = 1 << 4;
}
