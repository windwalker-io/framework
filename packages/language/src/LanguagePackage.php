<?php

declare(strict_types=1);

namespace Windwalker\Language;

use Windwalker\Core\CorePackage;
use Windwalker\Core\DI\RequestBootableProviderInterface;
use Windwalker\Core\Language\LangService;
use Windwalker\Core\Package\AbstractPackage;
use Windwalker\Core\Package\PackageInstaller;
use Windwalker\DI\Container;
use Windwalker\DI\ServiceProviderInterface;

/**
 * The LanguagePackage class.
 */
class LanguagePackage extends AbstractPackage implements ServiceProviderInterface, RequestBootableProviderInterface
{
    public function install(PackageInstaller $installer): void
    {
        $installer->installConfig(__DIR__ . '/../etc/*.php', 'config');
    }

    /**
     * @inheritDoc
     */
    public function register(Container $container): void
    {
        $container->prepareSharedObject(
            LangService::class,
            fn(LangService $langService) => $langService->loadAllFromPath(
                CorePackage::dir() . '/../../resources/languages',
                'php'
            )
        );
    }

    /**
     * @inheritDoc
     */
    // public function bootDeferred(Container $container): void
    // {
    //     // todo: move to after request start
    //     $container->get(LangService::class)->loadAll();
    // }

    public function bootBeforeRequest(Container $container): void
    {
        $vendors = $container->getParam('language.vendors') ?? [];

        $lang = $container->get(LangService::class);

        foreach ($vendors as $format => $vendorNames) {
            foreach ($vendorNames as $vendorName) {
                $lang->loadAllFromVendor($vendorName, $format);
            }
        }
    }
}
