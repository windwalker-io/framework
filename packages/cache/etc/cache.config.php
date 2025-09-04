<?php

declare(strict_types=1);

namespace App\Config;

use Windwalker\Cache\CachePackage;
use Windwalker\Cache\CachePool;
use Windwalker\Cache\Serializer\PhpSerializer;
use Windwalker\Cache\Serializer\RawSerializer;
use Windwalker\Cache\Storage\NullStorage;
use Windwalker\Core\Attributes\ConfigModule;
use Windwalker\Core\Factory\CacheFactory;

return #[ConfigModule(name: 'cache', enabled: true, priority: 100, belongsTo: CachePackage::class)]
static fn() => [
    'no_cache' => env('CACHE_DISABLED'),

    // The default cache profile
    'default' => 'global',

    'providers' => [
        CachePackage::class
    ],

    'bindings' => [
        //
    ],

    'factories' => [
        'instances' => [
            'none' => static fn(): CachePool => new CachePool(new NullStorage()),
            'global' => static fn () => CacheFactory::cachePoolFactory('file', PhpSerializer::class),
            'html' => static fn () => CacheFactory::cachePoolFactory('file', RawSerializer::class),
        ],
        'storages' => [
            'file' => static fn () => CacheFactory::fileStorage()
        ],
    ],
];
