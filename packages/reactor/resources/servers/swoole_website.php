<?php

declare(strict_types=1);

namespace App;

use Windwalker\Core\Application\WebApplication;
use Windwalker\Core\CliServer\CliServerRuntime;
use Windwalker\Core\CliServer\Swoole\Subscriber\ServerStartedListener;
use Windwalker\Core\CliServer\Swoole\Subscriber\SwooleProcessSubscriber;
use Windwalker\Core\CorePackage;
use Windwalker\DI\Container;
use Windwalker\Http\Event\RequestEvent;
use Windwalker\Reactor\Swoole\Event\StartEvent;
use Windwalker\Reactor\Swoole\SwooleHttpServer;
use Windwalker\Reactor\Swoole\SwooleServer;

use function Swoole\Coroutine\run;

include __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/../etc/define.php';

/*
 * --------------------------------------------------------------------------
 * Bootstrap Windwalker Runtime
 * --------------------------------------------------------------------------
 * Boot Runtime and set server type as `swoole`.
 */

/** @var Container $container */
$container = (include CorePackage::path('bin/server-boot.php'))('swoole', $argv);

/*
 * --------------------------------------------------------------------------
 * Server State
 * --------------------------------------------------------------------------
 * Get server state of current runtime.
 * The startup options controls the workers and task numbers.
 */

$serverState = CliServerRuntime::getServerState();

$startupOptions = $serverState->getStartupOptions();

/*
 * --------------------------------------------------------------------------
 * Create Server Instance
 * --------------------------------------------------------------------------
 * Create the server instance by factory, and configure server here.
 * You may set the workers or task numbers here, these are swoole specific
 * settings, otherwise we set default value by cpu numbers.
 */

/** @var SwooleHttpServer $server */
$server = $container->resolve(SwooleHttpServer::factory());

$server->set('max_request', $startupOptions['max_requests'] ?? 500);
$server->set('task_max_request', $startupOptions['max_requests'] ?? 500);
$server->set('worker_num', $workers = $startupOptions['workers'] ?? swoole_cpu_num());
$server->set('task_worker_num', $startupOptions['task_workers'] ?? swoole_cpu_num());

// Set worker numbers to state, that Database connection pool can use this
// value as max numbers. This is cross platform settings.
$serverState->setWorkerNumber($workers);

// Custom configuration
// --------------------------------------------------------------------------

// --------------------------------------------------------------------------
// End custom configuration

/*
 * --------------------------------------------------------------------------
 * WebSocket Application
 * --------------------------------------------------------------------------
 * Let's create WsApplication, and send child container into it.
 */

$app = new WebApplication($container->createChild());

// Load the root config
$app->loadConfig(__DIR__ . '/app/' . ($startupOptions['app'] ?? 'main') . '.php');

/*
 * --------------------------------------------------------------------------
 * Dependency Injections
 * --------------------------------------------------------------------------
 * Register some important services into container.
 */

$container->share(SwooleServer::class, $server);
$container->share(SwooleHttpServer::class, $server);

/*
 * --------------------------------------------------------------------------
 * Server Events
 * --------------------------------------------------------------------------
 * Register some server lifecycle events, for example, the terminal output,
 * process name or gc collections.
 */

$server->subscribe(new SwooleProcessSubscriber($container));
$server->on(StartEvent::class, new ServerStartedListener());

// Subscribe custom events
// --------------------------------------------------------------------------

// --------------------------------------------------------------------------
// End subscribe custom events

/*
 * --------------------------------------------------------------------------
 * Web Request Event
 * --------------------------------------------------------------------------
 * This event triggered if user send a request.
 */

$server->onRequest(function (RequestEvent $event) use ($app) {
    $app->runCliServerRequest($event);
});

/*
 * --------------------------------------------------------------------------
 * Server Start
 * --------------------------------------------------------------------------
 * The server start event. It shows some messages to terminal.
 */

$server->onStart(function (StartEvent $event) {
    $serv = $event->getSwooleServer();

    CliServerRuntime::logNewLine(1);
    CliServerRuntime::logLine('Start listening: http://localhost:' . $serv->port);
    CliServerRuntime::logLine('<fg=yellow>Press Ctrl+C to stop the server</>');
    CliServerRuntime::logNewLine(1);
});

/*
 * --------------------------------------------------------------------------
 * Boot Application
 * --------------------------------------------------------------------------
 * Boot application for current server instance.
 */

run(
    function () use ($app, $server) {
        try {
            $app->bootForServer($server);
        } catch (\Throwable $e) {
            CliServerRuntime::handleThrowable($e);
            die;
        }
    }
);

// Override display_errors before ini move to runtime
// Todo: Move ini settings to runtime
ini_set('display_errors', 'stderr');

try {
    /*
     * --------------------------------------------------------------------------
     * Run Server
     * --------------------------------------------------------------------------
     * After events configured, let's start running server.
     * This will hang process util a new request.
     * To stop server, use `$server->shutdown()` in request or press CTRL + C.
     */

    $server->listen('0.0.0.0', $serverState->getPort() ?: 9501);
} catch (\Throwable $e) {
    CliServerRuntime::handleThrowable($e);
}
