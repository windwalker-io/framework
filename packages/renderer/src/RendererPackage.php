<?php

declare(strict_types=1);

namespace Windwalker\Renderer;

use Windwalker\Core\Package\AbstractPackage;
use Windwalker\Core\Package\PackageInstaller;

/**
 * The RendererPackage class.
 *
 * @codeCoverageIgnore Application wiring that only installs config files.
 */
class RendererPackage extends AbstractPackage
{
    public function install(PackageInstaller $installer): void
    {
        $installer->installConfig(__DIR__ . '/../etc/*.php', 'config');
    }
}
