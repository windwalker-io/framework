<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Wrapper;

/**
 * Interface WrapperInterface
 */
interface WrapperInterface
{
    /**
     * Get wrapped value.
     *
     * @param  mixed  $src
     *
     * @return  mixed
     */
    public function __invoke(mixed $src): mixed;
}
