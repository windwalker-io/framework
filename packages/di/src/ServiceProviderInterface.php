<?php

declare(strict_types=1);

namespace Windwalker\DI;

/**
 * Interface ServiceProviderInterface
 */
interface ServiceProviderInterface
{
    /**
     * Registers the service provider with a DI container.
     *
     * @param  Container  $container  The DI container.
     *
     * @return  void
     */
    public function register(Container $container): void;
}
