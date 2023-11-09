<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2022 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace App;

use Windwalker\Core\Application\WebApplication;
use Windwalker\Core\CliServer\CliServerRuntime;
use Windwalker\Core\CliServer\Swoole\Subscriber\ServerStartedListener;
use Windwalker\Core\CliServer\Swoole\Subscriber\SwooleProcessSubscriber;
use Windwalker\DI\Container;
use Windwalker\Http\Event\ErrorEvent;
use Windwalker\Http\Event\RequestEvent;
use Windwalker\Reactor\Swoole\Event\StartEvent;
use Windwalker\Reactor\Swoole\SwooleHttpServer;
use Windwalker\Reactor\Swoole\SwooleServer;

use function Swoole\Coroutine\run;

$_ENV['APP_ENV'] = 'prod';

/** @var Container $container */
$container = (include __DIR__ . '/bootstrap.php')('swoole', $argv);

$serverState = CliServerRuntime::getServerState();
$startupOptions = $serverState->getStartupOptions();

/** @var SwooleHttpServer $server */
$server = $container->resolve('factories.servers.swoole');
$container->share(SwooleServer::class, $server);
$container->share(SwooleHttpServer::class, $server);
$server->setMode(SWOOLE_PROCESS);
$server->setSockType(SWOOLE_TCP);

$server->set('max_request', $startupOptions['max_requests'] ?? 500);
$server->set('task_max_request', $startupOptions['max_requests'] ?? 500);
$server->set('worker_num', $workers = $startupOptions['workers'] ?? swoole_cpu_num());
$server->set('task_worker_num', $startupOptions['task_workers'] ?? swoole_cpu_num());

$serverState->setWorkerNumber($workers);

/** @var WebApplication $app */
$app = $container->resolve('factories.apps.' . ($startupOptions['app'] ?? 'main'));

run(fn() => $app->bootForServer($server));

// Override display_errors before ini move to runtime
// Todo: Move ini settings to runtime
ini_set('display_errors', 'stderr');

$server->subscribe(new SwooleProcessSubscriber($container));
$server->on(StartEvent::class, new ServerStartedListener());

$server->onRequest(function (RequestEvent $event) use ($app) {
    $app->runCliServerRequest($event);
});

$server->onStart(function (StartEvent $event) {
    $serv = $event->getSwooleServer();

    CliServerRuntime::logNewLine(1);
    CliServerRuntime::logLine('Start listening: http://localhost:' . $serv->port);
    CliServerRuntime::logLine('<fg=yellow>Press Ctrl+C to stop the server</>');
    CliServerRuntime::logNewLine(1);
});

$server->onError(function (ErrorEvent $event) {
    show($event->getException()->getMessage());

    $event->stopPropagation();
});

$server->listen('0.0.0.0', $serverState->getPort() ?: 9501);
