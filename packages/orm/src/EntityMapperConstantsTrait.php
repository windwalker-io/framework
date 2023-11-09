<?php

declare(strict_types=1);

namespace Windwalker\ORM;

trait EntityMapperConstantsTrait
{
    public const UPDATE_NULLS = 1 << 0;

    public const IGNORE_EVENTS = 1 << 1;

    public const IGNORE_OLD_DATA = 1 << 2;

    public const TRANSACTION = 1 << 3;
}
