<?php

declare(strict_types=1);

namespace Windwalker\Session;

use Windwalker\Core\Application\ApplicationInterface;
use Windwalker\Core\DI\RequestReleasableProviderInterface;
use Windwalker\Core\Manager\SessionManager;
use Windwalker\Core\Package\AbstractPackage;
use Windwalker\Core\Package\PackageInstaller;
use Windwalker\Core\Provider\IniSetterTrait;
use Windwalker\Core\Security\CsrfService;
use Windwalker\DI\BootableProviderInterface;
use Windwalker\DI\Container;
use Windwalker\DI\ServiceProviderInterface;
use Windwalker\Session\Cookie\ArrayCookies;
use Windwalker\Session\Cookie\Cookies;
use Windwalker\Session\Cookie\CookiesInterface;
use Windwalker\Utilities\Str;

/**
 * The SessionPackage class.
 */
class SessionPackage extends AbstractPackage implements ServiceProviderInterface, BootableProviderInterface
{
    use IniSetterTrait;

    public function install(PackageInstaller $installer): void
    {
        $installer->installConfig(__DIR__ . '/../etc/*.php', 'config');
    }

    public function boot(Container $container): void
    {
        $app = $container->get(ApplicationInterface::class);

        if (!$app->getType()->isCliWeb()) {
            $iniValues = $container->getParam('session.ini') ?? [];

            $options = [];

            foreach ($iniValues as $key => $value) {
                $key = Str::ensureLeft('session.', $key);

                $options[$key] = $value;
            }

            static::setINIValues($options, $container);
        }
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
            fn(SessionManager $manager, ?string $tag = null) => $manager->get($tag),
            Container::ISOLATION
        )
            ->alias(SessionInterface::class, Session::class);

        $container->bindShared(
            CookiesInterface::class,
            function (SessionManager $manager, ?string $tag = null) {
                return $manager->get($tag)->getCookies();
            },
            Container::ISOLATION
        );

        $container->prepareSharedObject(CsrfService::class, null, Container::ISOLATION);
    }
}
