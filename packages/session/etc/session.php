<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2020 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

use Windwalker\Core\Attributes\Ref;
use Windwalker\Core\Manager\SessionManager;
use Windwalker\DI\Container;
use Windwalker\Session\Bridge\BridgeInterface;
use Windwalker\Session\Bridge\NativeBridge;
use Windwalker\Session\Bridge\PhpBridge;
use Windwalker\Session\Cookie\Cookies;
use Windwalker\Session\Cookie\CookiesInterface;
use Windwalker\Session\Handler\ArrayHandler;
use Windwalker\Session\Handler\DatabaseHandler;
use Windwalker\Session\Handler\FilesystemHandler;
use Windwalker\Session\Handler\NativeHandler;
use Windwalker\Session\Handler\RedisHandler;
use Windwalker\Session\Session;
use Windwalker\Session\SessionPackage;

use function Windwalker\DI\create;
use function Windwalker\ref;

return [
    'session' => [
        'enabled' => true,

        'default' => env('SESSION_DEFAULT') ?: 'native',

        'cookie_params' => [
            'expires' => '+15minutes',
            'path' => '/',
            'domain' => null,
            'secure' => false,
            'httponly' => true,
            'samesite' => Cookies::SAMESITE_LAX,
        ],

        'ini' => [
            'name' => 'WINDWALKER_SESSID',
            // 'save_path' => '@temp/sess',
            'use_cookies' => '0',
        ],

        'providers' => [
            SessionPackage::class,
        ],

        'bindings' => [
            CookiesInterface::class => fn (Container $container)
            => $container->resolve('session.factories.cookies.request')
        ],

        'factories' => [
            'instances' => [
                'native' => create(
                             Session::class,
                    options: fn(#[Ref('session.ini')] array $ini) => [
                        Session::OPTION_AUTO_COMMIT => true,
                        'ini' => $ini,
                    ],
                    bridge: ref('session.factories.bridges.php'),
                    cookies: ref('session.factories.cookies.request')
                ),
            ],
            'bridges' => [
                'native' => create(
                             NativeBridge::class,
                    options: [],
                    handler: ref('session.factories.handlers.native')
                ),
                'php' => create(
                             PhpBridge::class,
                    options: [
                                 BridgeInterface::OPTION_AUTO_COMMIT => true,
                                 BridgeInterface::OPTION_WITH_SUPER_GLOBAL => false,
                             ],
                    handler: ref('session.factories.handlers.filesystem')
                ),
            ],
            'handlers' => [
                'array' => create(ArrayHandler::class),
                'native' => create(NativeHandler::class),
                'database' => create(
                             DatabaseHandler::class,
                    db: ref('database.connections.local'),
                    options: []
                ),
                'filesystem' => create(
                             FilesystemHandler::class,
                    path: sys_get_temp_dir() . '/sess',
                    options: []
                ),
                'redis' => create(RedisHandler::class),
            ],
            'cookies' => [
                'request' => create(SessionManager::psrCookies()),
                'native' => create(Cookies::class, ref('session.cookie_params')),
            ],
        ],
    ],
];
