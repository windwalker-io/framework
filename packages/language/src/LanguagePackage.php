<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Language;

use Windwalker\Core\CorePackage;
use Windwalker\Core\Language\LangService;
use Windwalker\Core\Package\AbstractPackage;
use Windwalker\Core\Package\PackageInstaller;
use Windwalker\DI\Container;
use Windwalker\DI\ServiceProviderInterface;

/**
 * The LanguagePackage class.
 */
class LanguagePackage extends AbstractPackage implements ServiceProviderInterface
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
        $container->prepareSharedObject(LangService::class)
            ->extend(
                LangService::class,
                function (LangService $langService) {
                    return $langService->loadAllFromPath(CorePackage::dir() . '/../../resources/languages', 'php');
                }
            );
    }

    /**
     * @inheritDoc
     */
    public function bootDeferred(Container $container): void
    {
        // todo: move to after request start
        $container->get(LangService::class)->loadAll();
    }
}
