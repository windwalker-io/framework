<?php

declare(strict_types=1);

namespace Windwalker\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
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

        $container->bindShared(
            CacheInterface::class,
            function (Container $container, ?string $tag = null) {
                return $container->get(CacheManager::class)->get($tag);
            }
        );

        $container->bindShared(
            CacheItemPoolInterface::class,
            function (Container $container, ?string $tag = null) {
                return $container->get(CacheManager::class)->get($tag);
            }
        );

        $container->bindShared(
            CachePool::class,
            function (Container $container, ?string $tag = null) {
                return $container->get(CacheManager::class)->get($tag);
            }
        );
    }
}
