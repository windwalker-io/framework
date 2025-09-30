<?php

declare(strict_types=1);

namespace App\Config;

use Windwalker\Core\Application\AppContext;
use Windwalker\Core\Attributes\ConfigModule;
use Windwalker\Core\Factory\SessionFactory;
use Windwalker\Core\Session\CookiesAutoSecureSubscriber;
use Windwalker\Core\Session\SessionRobotSubscriber;
use Windwalker\Session\Bridge\NativeBridge;
use Windwalker\Session\Bridge\PhpBridge;
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

return #[ConfigModule(name: 'session', enabled: true, priority: 100, belongsTo: SessionPackage::class)]
static fn() => [
    'default' => env('SESSION_DEFAULT') ?: 'filesystem',

    'cookie_params' => [
        'expires' => '+150minutes',
        'path' => '/',
        'domain' => null,
        'secure' => false,
        'httponly' => true,
        'samesite' => CookiesInterface::SAMESITE_LAX,
    ],

    'session_options' => [
        'name' => 'WINDWALKER_SESSID',
        'use_cookies' => '0',
        'gc_divisor' => '1000',
        'gc_probability' => env('SESSION_GC_PROBABILITY', '1'),
    ],

    'ini' => [
        'use_cookies' => '0',
        'gc_divisor' => '1000',
        'gc_probability' => env('SESSION_GC_PROBABILITY', '1'),
    ],

    'providers' => [
        SessionPackage::class,
    ],

    'listeners' => [
        AppContext::class => [
            SessionRobotSubscriber::class,
            create(
                CookiesAutoSecureSubscriber::class,
                enabled: (bool) env('COOKIES_AUTO_SECURE', '1')
            ),
        ],
    ],

    'bindings' => [
        //
    ],

    'factories' => [
        'instances' => [
            'native' => static fn () => SessionFactory::createSession(
                'php',
                'native',
                'request',
                [
                    SessionInterface::OPTION_AUTO_COMMIT => true,
                ]
            ),
            'filesystem' => static fn () => SessionFactory::createSession(
                'php',
                'filesystem',
                'request',
                [
                    SessionInterface::OPTION_AUTO_COMMIT => true,
                ]
            ),
            'database' => static fn () => SessionFactory::createSession(
                'php',
                'database',
                'request',
                [
                    SessionInterface::OPTION_AUTO_COMMIT => true,
                ]
            ),
            'null' => static fn () => SessionFactory::createSession(
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
            'database' => static fn() => create(
                DatabaseHandler::class,
                options: [
                    'table' => 'sessions',
                ]
            ),
            'filesystem' => static fn() => create(
                FilesystemHandler::class,
                path: fn() => sys_get_temp_dir() . '/sess',
                options: []
            ),
            'redis' => RedisHandler::class,
        ],
        'cookies' => [
            'request' => static fn () => SessionFactory::psrCookies(),
            'native' => static fn () => SessionFactory::nativeCookies(),
        ],
    ],
];
