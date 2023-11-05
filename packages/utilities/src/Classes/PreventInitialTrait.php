<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Classes;

/**
 * Trait PreventInitialTrait
 *
 * @since  __DEPLOY_VERSION__
 */
trait PreventInitialTrait
{
    /**
     * Prevent implement class.
     */
    protected function __construct()
    {
        //
    }
}
