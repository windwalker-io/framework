<?php

/**
 * Part of framework project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Database;

use Windwalker\Core\Database\DatabaseExportService;
use Windwalker\Core\Manager\DatabaseManager;
use Windwalker\Core\Migration\MigrationService;
use Windwalker\Core\Package\AbstractPackage;
use Windwalker\Core\Package\PackageInstaller;
use Windwalker\Core\Seed\FakerService;
use Windwalker\DI\Container;
use Windwalker\DI\ServiceProviderInterface;
use Windwalker\ORM\ORM;

/**
 * The DatabasePackage class.
 */
class DatabasePackage extends AbstractPackage implements ServiceProviderInterface
{
    public function install(PackageInstaller $installer): void
    {
        $installer->installConfig(__DIR__ . '/../etc/*.php', 'config');
    }

    /**
     * Registers the service provider with a DI container.
     *
     * @param  Container  $container  The DI container.
     *
     * @return  void
     * @throws \Windwalker\DI\Exception\DefinitionException
     */
    public function register(Container $container): void
    {
        $container->prepareSharedObject(DatabaseManager::class);
        $container->prepareSharedObject(DatabaseFactory::class);
        $container->bind(DatabaseAdapter::class, fn(DatabaseManager $manager) => $manager->get());
        $container->bind(ORM::class, fn(DatabaseManager $manager) => $manager->get()->orm());

        // Faker
        $container->prepareSharedObject(FakerService::class);

        // Services
        $container->prepareSharedObject(DatabaseExportService::class);
        $container->prepareObject(MigrationService::class);
    }
}
