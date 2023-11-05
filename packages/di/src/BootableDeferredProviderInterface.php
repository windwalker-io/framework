<?php

declare(strict_types=1);

namespace Windwalker\DI;

/**
 * Interface BootableDeferredProviderInterface
 *
 * @since  3.5
 */
interface BootableDeferredProviderInterface
{
    /**
     * boot
     *
     * @param  Container  $container
     *
     * @return  void
     */
    public function bootDeferred(Container $container): void;
}
