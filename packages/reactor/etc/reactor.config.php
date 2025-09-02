<?php

declare(strict_types=1);

namespace App\Config;

use Windwalker\Core\Attributes\ConfigModule;
use Windwalker\Reactor\ReactorPackage;

return #[ConfigModule(name: 'reactor', enabled: true, priority: 100, belongsTo: ReactorPackage::class)]
static fn() => [
    'swoole' => [
        //
    ],

    'watch' => [
        'etc/**/*',
        'src/**/*',
        'views/**/*',
    ],
];
