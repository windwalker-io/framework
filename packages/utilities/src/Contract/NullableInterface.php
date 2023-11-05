<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Contract;

/**
 * Interface NullableInterface
 *
 * @since  __DEPLOY_VERSION__
 */
interface NullableInterface
{
    /**
     * isNull
     *
     * @return  bool
     *
     * @since  __DEPLOY_VERSION__
     */
    public function isNull(): bool;

    /**
     * notNull
     *
     * @return  bool
     *
     * @since  __DEPLOY_VERSION__
     */
    public function notNull(): bool;
}
