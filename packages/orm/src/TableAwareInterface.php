<?php

declare(strict_types=1);

namespace Windwalker\ORM;

/**
 * Interface TableAwareInterface
 */
interface TableAwareInterface
{
    /**
     * Get Table Name.
     *
     * @return  string
     */
    public static function table(): string;
}
