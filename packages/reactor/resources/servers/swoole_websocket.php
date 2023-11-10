<?php

declare(strict_types=1);

namespace App;

use App\Web\WsApplication;
use Windwalker\Core\CliServer\CliServerRuntime;
use Windwalker\Core\CliServer\Swoole\Subscriber\ServerStartedListener;
use Windwalker\Core\CliServer\Swoole\Subscriber\SwooleProcessSubscriber;
use Windwalker\Core\CorePackage;
use Windwalker\DI\Container;
use Windwalker\Reactor\Swoole\Event\CloseEvent;
use Windwalker\Reactor\Swoole\Event\MessageEvent;
use Windwalker\Reactor\Swoole\Event\OpenEvent;
use Windwalker\Reactor\Swoole\Event\StartEvent;
use Windwalker\Reactor\Swoole\Room\RoomMapping;
use Windwalker\Reactor\Swoole\Room\UserFdMapping;
use Windwalker\Reactor\Swoole\SwooleHttpServer;
use Windwalker\Reactor\Swoole\SwooleServer;
use Windwalker\Reactor\Swoole\SwooleWebsocketServer;
use Windwalker\WebSocket\Swoole\RequestRegistry;

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
$container = (include CorePackage::path('../bin/server-boot.php'))('swoole', $argv);

/*
 * --------------------------------------------------------------------------
 * Configure Error Reporting
 * --------------------------------------------------------------------------
 * This will set display_errors output to STDERR and use built-in
 * error-reporting levels.
 */

CliServerRuntime::registerErrorReporting(include WINDWALKER_ETC . '/conf/error-reporting.php');

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

/** @var SwooleWebsocketServer $server */
$server = $container->resolve(SwooleWebsocketServer::factory());

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

$app = new WsApplication($container->createChild());

// Load the root config
$app->loadConfig(WINDWALKER_ETC . '/app/websocket.php');

/*
 * --------------------------------------------------------------------------
 * Dependency Injections
 * --------------------------------------------------------------------------
 * Register some important services into container.
 */

$container->share(SwooleServer::class, $server);
$container->share(SwooleHttpServer::class, $server);
$container->share(SwooleWebsocketServer::class, $server);

// Register custom services
// --------------------------------------------------------------------------

// --------------------------------------------------------------------------
// End custom services

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
 * Connection Open
 * --------------------------------------------------------------------------
 * The connection open event.
 * The WebSocket connection opening will get a HTTP request.
 * We must store this request object to sharing memory, then every message
 * later that can continue user session by the fd which stored in this request.
 *
 * To add custom opening handler, put code to WsApplication::opening().
 */

$server->onOpen(
    function (OpenEvent $event) use ($app) {
        $request = $event->getRequest();

        // Keep request in memory, so we can use this request cross processes.
        $app->rememberRequest($request);

        try {
            // Run custom open() code.
            $app->openConnection($request);
        } catch (\Throwable $e) {
            CliServerRuntime::handleThrowable($e);
        }
    }
);

/*
 * --------------------------------------------------------------------------
 * Message Event
 * --------------------------------------------------------------------------
 * The handler of receiving message.
 * The websocket frame will insert into the request object,
 * then create and run an AppContext.
 *
 * The request will dispatch to controller depends on message content
 * and middlewares, see config file.
 */

$server->onMessage(
    function (MessageEvent $event) use ($app) {
        // Get request object from memory
        $request = $event->getRequestFromMemory($app);

        try {
            $app->runContextByRequest($request);
        } catch (\Throwable $e) {
            CliServerRuntime::handleThrowable($e);
        }
    }
);

/*
 * --------------------------------------------------------------------------
 * Connection Close
 * --------------------------------------------------------------------------
 * The event of connection closing.
 * This event will release the objects which keeping in memory.
 * To add custom close handler, put your code into WsApplication::started()
 */

$server->onClose(
    function (CloseEvent $event) use ($app) {
        // Get request from memory and release it
        $request = $event->getAndForgetRequest($app);

        try {
            // Run custom close handler.
            $app->closeConnection($request);
        } catch (\Throwable $e) {
            CliServerRuntime::handleThrowable($e);
        }
    }
);

/*
 * --------------------------------------------------------------------------
 * Server Start
 * --------------------------------------------------------------------------
 * The server start event. It shows some messages to terminal.
 * To add custom started handler, put your code into WsApplication::started()
 */

$server->onStart(
    function (StartEvent $event) use ($server, $app) {
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
    }
);

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

try {
    /*
     * --------------------------------------------------------------------------
     * App initialize
     * --------------------------------------------------------------------------
     * Initialize Application.
     * This will run user init code, put your code into `WsApplication::init()`
     */

    $app->initialize();

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
