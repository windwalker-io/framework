<?php

declare(strict_types=1);

namespace Windwalker\Reactor;

use Windwalker\Core\Package\AbstractPackage;
use Windwalker\Core\Package\PackageInstaller;
use Windwalker\Filesystem\Filesystem;

class ReactorPackage extends AbstractPackage
{
    public function install(PackageInstaller $installer): void
    {
        $installer->installConfig(static::path('etc/*.php'), 'config');

        $installer->installFiles(
            static::path('resources/servers/swoole/website.php'),
            'servers',
            'swoole_website'
        );
    }
}
