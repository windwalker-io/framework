<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2020 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

use Windwalker\Core\Manager\SessionManager;
use Windwalker\Core\Session\SessionRobotSubscriber;
use Windwalker\DI\Container;
use Windwalker\Session\Bridge\NativeBridge;
use Windwalker\Session\Bridge\PhpBridge;
use Windwalker\Session\Cookie\Cookies;
use Windwalker\Session\Cookie\CookiesInterface;
use Windwalker\Session\Handler\ArrayHandler;
use Windwalker\Session\Handler\DatabaseHandler;
use Windwalker\Session\Handler\FilesystemHandler;
use Windwalker\Session\Handler\NativeHandler;
use Windwalker\Session\Handler\NullHandler;
use Windwalker\Session\Handler\RedisHandler;
use Windwalker\Session\SessionInterface;
use Windwalker\Session\SessionPackage;

use function Windwalker\DI\create;
use function Windwalker\ref;

return [
    'session' => [
        'enabled' => true,

        'default' => env('SESSION_DEFAULT') ?: 'database',

        'cookie_params' => [
            'expires' => '+150minutes',
            'path' => '/',
            'domain' => null,
            'secure' => false,
            'httponly' => true,
            'samesite' => CookiesInterface::SAMESITE_LAX,
        ],

        'ini' => [
            'name' => 'WINDWALKER_SESSID',
            // 'save_path' => '@temp/sess',
            'use_cookies' => '0',
        ],

        'providers' => [
            SessionPackage::class,
        ],

        'listeners' => [
            SessionRobotSubscriber::class
        ],

        'bindings' => [
            CookiesInterface::class => fn (Container $container)
            => $container->resolve('session.factories.cookies.request')
        ],

        'factories' => [
            'instances' => [
                'native' => SessionManager::createSession(
                    'php',
                    'native',
                    'request',
                    [
                        SessionInterface::OPTION_AUTO_COMMIT => true
                    ]
                ),
                'filesystem' => SessionManager::createSession(
                    'php',
                    'filesystem',
                    'request',
                    [
                        SessionInterface::OPTION_AUTO_COMMIT => true
                    ]
                ),
                'database' => SessionManager::createSession(
                    'php',
                    'database',
                    'request',
                    [
                        SessionInterface::OPTION_AUTO_COMMIT => true
                    ]
                ),
                'null' => SessionManager::createSession(
                    'php',
                    'null',
                    'request',
                ),
            ],
            'bridges' => [
                'native' => NativeBridge::class,
                'php' => PhpBridge::class,
            ],
            'handlers' => [
                'array' => ArrayHandler::class,
                'null' => NullHandler::class,
                'native' => NativeHandler::class,
                'database' => create(
                    DatabaseHandler::class,
                    options: [
                        'table' => 'sessions'
                    ]
                ),
                'filesystem' => create(
                    FilesystemHandler::class,
                    path: fn () => sys_get_temp_dir() . '/sess',
                    options: []
                ),
                'redis' => RedisHandler::class,
            ],
            'cookies' => [
                'request' => SessionManager::psrCookies(),
                'native' => create(Cookies::class, ref('session.cookie_params')),
            ],
        ],
    ],
];
