<?php

declare(strict_types=1);

namespace Windwalker\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Windwalker\Core\Factory\CacheFactory;
use Windwalker\Core\Manager\CacheManager;
use Windwalker\Core\Package\AbstractPackage;
use Windwalker\Core\Package\PackageInstaller;
use Windwalker\DI\Container;
use Windwalker\DI\ServiceProviderInterface;

/**
 * The CachePackage class.
 */
class CachePackage extends AbstractPackage implements ServiceProviderInterface
{
    public function install(PackageInstaller $installer): void
    {
        $installer->installConfig(__DIR__ . '/../etc/*.php', 'config');
    }

    public function register(Container $container): void
    {
        $container->prepareSharedObject(CacheManager::class);
        $container->prepareSharedObject(CacheFactory::class);

        $container->bindShared(
            CacheInterface::class,
            fn(Container $container, ?string $tag = null) => $container->get(CacheFactory::class)->get($tag)
        );

        $container->bindShared(
            CacheItemPoolInterface::class,
            fn(Container $container, ?string $tag = null) => $container->get(CacheFactory::class)->get($tag)
        );

        $container->bindShared(
            CachePool::class,
            fn(Container $container, ?string $tag = null) => $container->get(CacheFactory::class)->get($tag)
        );
    }
}
