<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Classes;

/**
 * The ImmutableHelperTrait class.
 *
 * @since  __DEPLOY_VERSION__
 */
trait ImmutableHelperTrait
{
    protected function cloneInstance(callable $callback = null): static
    {
        $new = clone $this;

        if ($callback === null) {
            return $new;
        }

        $callback($new);

        return $new;
    }
}
