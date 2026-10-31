<?php

declare(strict_types=1);

namespace Windwalker\Form;

use Windwalker\Core\Package\AbstractPackage;
use Windwalker\Core\Package\PackageInstaller;

/**
 * The FormPackage class.
 *
 * Application-level wiring that only installs config; no standalone tests needed.
 *
 * @codeCoverageIgnore
 */
class FormPackage extends AbstractPackage
{
    public function install(PackageInstaller $installer): void
    {
        $installer->installConfig(__DIR__ . '/../etc/*.php', 'config');
    }
}
