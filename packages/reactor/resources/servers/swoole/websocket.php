<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2022 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace App;

use App\Room\RoomMapping;
use App\Room\UserFdMapping;
use App\Web\WsApplication;
use NunoMaduro\Collision\Provider;
use Windwalker\Core\CliServer\CliServerRuntime;
use Windwalker\Core\CliServer\Swoole\Subscriber\ServerStartedListener;
use Windwalker\Core\CliServer\Swoole\Subscriber\SwooleProcessSubscriber;
use Windwalker\DI\Container;
use Windwalker\Http\Event\ErrorEvent;
use Windwalker\Http\Helper\ResponseHelper;
use Windwalker\Reactor\Swoole\Event\CloseEvent;
use Windwalker\Reactor\Swoole\Event\MessageEvent;
use Windwalker\Reactor\Swoole\Event\OpenEvent;
use Windwalker\Reactor\Swoole\Event\StartEvent;
use Windwalker\Reactor\Swoole\SwooleHttpServer;
use Windwalker\Reactor\Swoole\SwooleServer;
use Windwalker\Reactor\Swoole\SwooleWebsocketServer;
use Windwalker\WebSocket\Swoole\RequestRegistry;

use function Swoole\Coroutine\run;

/** @var Container $container */
$container = (include __DIR__ . '/bootstrap.php')('swoole', $argv);
$serverState = CliServerRuntime::getServerState();
$startupOptions = $serverState->getStartupOptions();

/** @var SwooleHttpServer $server */
$server = $container->resolve('factories.servers.websocket');
$server->setMode(SWOOLE_PROCESS);

$server->set('max_request', $startupOptions['max_requests'] ?? 500);
$server->set('task_max_request', $startupOptions['max_requests'] ?? 500);
$server->set('worker_num', $workers = $startupOptions['workers'] ?? swoole_cpu_num());
$server->set('task_worker_num', $startupOptions['task_workers'] ?? swoole_cpu_num());

$serverState->setWorkerNumber($workers);

/** @var WsApplication $app */
$app = $container->resolve('factories.apps.websocket');

// DI
$container->share(SwooleServer::class, $server);
$container->share(SwooleWebsocketServer::class, $server);
$container->share(RequestRegistry::class, $requestRegistry = new RequestRegistry($startupOptions['max_requests'] ?? null));
$container->prepareSharedObject(UserFdMapping::class)->get(UserFdMapping::class);
$container->prepareSharedObject(RoomMapping::class)->get(RoomMapping::class);

run(fn() => $app->bootForServer($server));

// Override display_errors before ini move to runtime
// Todo: Move ini settings to runtime
ini_set('display_errors', 'stderr');

$server->subscribe(new SwooleProcessSubscriber($container));
$server->on(StartEvent::class, new ServerStartedListener());

$app->initialize();

// $server->onRequest(function (RequestEvent $event) use ($app) {
//     $app->runCliServerRequest($event);
// });

$server->onOpen(
    function (OpenEvent $event) use ($requestRegistry, $app) {
        $request = $event->getRequest();

        $requestRegistry->store($request->getFd(), $request);

        $app->openConnection($request);
    }
);

$server->onMessage(
    function (MessageEvent $event) use ($requestRegistry, $app) {
        $request = $requestRegistry->get($event->getFd())
            ->withFrame($event->getFrame());

        $appContext = $app->createContext($request);

        try {
            $app->runContext($appContext);
        } catch (\Throwable $e) {
            CliServerRuntime::handleThrowable($e);
        }

        // gc_collect_cycles();
    }
);

$server->onStart(function (StartEvent $event) use ($server, $app) {
    try {
        $app->start();

        $serv = $event->getSwooleServer();

        CliServerRuntime::logNewLine(1);
        CliServerRuntime::logLine('Start listening: http://localhost:' . $serv->port);
        CliServerRuntime::logLine('<fg=yellow>Press Ctrl+C to stop the server</>');
        CliServerRuntime::logNewLine(1);
    } catch (\Throwable $e) {
        CliServerRuntime::handleThrowable($e);
        $server->shutdown();
    }
});

$server->onClose(
    function (CloseEvent $event) use ($app, $requestRegistry) {
        $request = $requestRegistry->get($event->getFd());

        $requestRegistry->remove($event->getFd());

        $request = $request->withFrame($event->createWocketFrame());

        $app->closeConnection($request);
    }
);

// $server->onDisconnect(
//     function (DisconnectEvent $event) {
//         dump('disconnect');
//     }
// );

// $server->onError(function (ErrorEvent $event) {
//     show($event->getException()->getMessage());
//
//     $event->stopPropagation();
// });

$server->listen('0.0.0.0', $serverState->getPort() ?: 9501);
