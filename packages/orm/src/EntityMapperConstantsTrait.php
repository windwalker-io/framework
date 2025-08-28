<?php

declare(strict_types=1);

namespace Windwalker\ORM;

trait EntityMapperConstantsTrait
{
    /**
     * @deprecated  Use {@see ORMOptions} instead.
     */
    public const int UPDATE_NULLS = 1 << 0;

    /**
     * @deprecated  Use {@see ORMOptions} instead.
     */
    public const int IGNORE_EVENTS = 1 << 1;

    /**
     * @deprecated  Use {@see ORMOptions} instead.
     */
    public const int IGNORE_OLD_DATA = 1 << 2;

    /**
     * @deprecated  Use {@see ORMOptions} instead.
     */
    public const int TRANSACTION = 1 << 3;

    /**
     * @deprecated  Use {@see ORMOptions} instead.
     */
    public const int FOR_UPDATE = 1 << 4;
}
