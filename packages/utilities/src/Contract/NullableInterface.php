<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Contract;

/**
 * Interface NullableInterface
 */
interface NullableInterface
{
    /**
     * isNull
     *
     * @return  bool
     */
    public function isNull(): bool;

    /**
     * notNull
     *
     * @return  bool
     */
    public function notNull(): bool;
}
