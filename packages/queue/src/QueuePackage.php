<?php

declare(strict_types=1);

namespace Windwalker\Queue;

use Windwalker\Core\Package\AbstractPackage;
use Windwalker\Core\Package\PackageInstaller;
use Windwalker\Core\Queue\QueueFailerManager;
use Windwalker\Core\Queue\QueueManager;
use Windwalker\DI\Container;
use Windwalker\DI\ServiceProviderInterface;
use Windwalker\Queue\Failer\QueueFailerInterface;

/**
 * The QueuePackage class.
 */
class QueuePackage extends AbstractPackage implements ServiceProviderInterface
{
    public function install(PackageInstaller $installer): void
    {
        $installer->installConfig(__DIR__ . '/../etc/*.php', 'config');
    }

    public function register(Container $container): void
    {
        $container->prepareSharedObject(Worker::class);
        $container->prepareSharedObject(QueueManager::class);
        $container->prepareSharedObject(QueueFailerManager::class);
        $container->bindShared(
            Queue::class,
            function (Container $container) {
                return $container->get(QueueManager::class)->get();
            }
        );
        $container->bindShared(
            QueueFailerInterface::class,
            function (Container $container) {
                return $container->get(QueueFailerManager::class)->get();
            }
        );
    }
}
