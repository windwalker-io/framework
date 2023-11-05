<?php

declare(strict_types=1);

namespace Windwalker\DI;

/**
 * Interface BootableProviderInterface
 *
 * @since  3.5
 */
interface BootableProviderInterface
{
    /**
     * Boot services before app run.
     *
     * Note that if you don't want your services share to child process, you should not boot it here,
     * otherwise you must call Container::clearCache($serviceId) to clear it.
     *
     * @param  Container  $container
     *
     * @return  void
     */
    public function boot(Container $container): void;
}
