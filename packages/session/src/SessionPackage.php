<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Session;

use Windwalker\Core\DI\RequestReleasableProviderInterface;
use Windwalker\Core\Manager\SessionManager;
use Windwalker\Core\Package\AbstractPackage;
use Windwalker\Core\Package\PackageInstaller;
use Windwalker\Core\Security\CsrfService;
use Windwalker\DI\Container;
use Windwalker\DI\ServiceProviderInterface;
use Windwalker\Session\Cookie\ArrayCookies;
use Windwalker\Session\Cookie\Cookies;
use Windwalker\Session\Cookie\CookiesInterface;

/**
 * The SessionPackage class.
 */
class SessionPackage extends AbstractPackage implements ServiceProviderInterface
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
        $container->prepareSharedObject(SessionManager::class, null, Container::ISOLATION);

        // Cookies
        // $container->prepareSharedObject(Cookies::class, null, Container::ISOLATION);
        // $container->prepareObject(ArrayCookies::class, null, Container::ISOLATION);

        $container->bindShared(
            Session::class,
            fn(SessionManager $manager) => $manager->get(),
            Container::ISOLATION
        )
            ->alias(SessionInterface::class, Session::class);

        $container->bindShared(
            CookiesInterface::class,
            function (SessionManager $manager) {
                return $manager->get()->getCookies();
            },
            Container::ISOLATION
        );

        $container->prepareSharedObject(CsrfService::class, null, Container::ISOLATION);
    }
}
