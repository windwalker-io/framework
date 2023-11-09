<?php

declare(strict_types=1);

namespace App;

use App\Web\WsApplication;
use Windwalker\Core\CliServer\CliServerRuntime;
use Windwalker\Core\CliServer\Swoole\Subscriber\ServerStartedListener;
use Windwalker\Core\CliServer\Swoole\Subscriber\SwooleProcessSubscriber;
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

/*
 * --------------------------------------------------------------------------
 * Bootstrap Windwalker Runtime
 * --------------------------------------------------------------------------
 * Boot Runtime and set server type as `swoole`.
 */

/** @var Container $container */
$container = (include __DIR__ . '/bootstrap.php')('swoole', $argv);

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

// UserFdMapping, RoomMapping and RequestRegistry are swoole specific services.
// They keep a Swoole Table into it to sync fs mappings cross processes.
// We create and cache them here that every worker will get same table.
$userFdMapping = $container->createSharedObject(UserFdMapping::class);
$roomMapping = $container->createSharedObject(RoomMapping::class);
$requestRegistry = $container->createSharedObject(
    RequestRegistry::class,
    [
        'size' => $startupOptions['max_requests'] ?? null,
    ]
);

// Register custom services
// --------------------------------------------------------------------------

// --------------------------------------------------------------------------
// End custom services

/*
 * --------------------------------------------------------------------------
 * Boot Application
 * --------------------------------------------------------------------------
 * Boot application for current server instance.
 */

run(fn() => $app->bootForServer($server));

// Override display_errors before ini move to runtime
// Todo: Move ini settings to runtime
ini_set('display_errors', 'stderr');

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
 * App initialize
 * --------------------------------------------------------------------------
 * Initialize Application.
 * This will run user init code, put your code into `WsApplication::init()`
 */

$app->initialize();

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
    function (OpenEvent $event) use ($requestRegistry, $app) {
        $request = $event->getRequest();

        // Keep request in memory, so we can use this request cross process.
        $requestRegistry->store($request->getFd(), $request);

        // Run custom open() code.
        $app->openConnection($request);
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
    function (MessageEvent $event) use ($requestRegistry, $app) {
        // Get request object from memory
        $request = $requestRegistry->get($event->getFd())
            ->withFrame($event->getFrame());

        try {
            $app->runContextByRequest($request);
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
 * Connection Close
 * --------------------------------------------------------------------------
 * The event of connection closing.
 * This event will release the objects which keeping in memory.
 * To add custom close handler, put your code into WsApplication::started()
 */

$server->onClose(
    function (CloseEvent $event) use ($userFdMapping, $roomMapping, $app, $requestRegistry) {
        // Get request from memory
        $request = $requestRegistry->get($event->getFd())
            ->withFrame($event->createWocketFrame());

        // Release request object from memory
        $requestRegistry->remove($event->getFd());

        // Run custom close handler.
        $app->closeConnection($request);

        // Leave rooms
        $roomMapping->leaveAllRooms($request->getFd());
        $userFdMapping->removeFd($request->getFd());
    }
);

/*
 * --------------------------------------------------------------------------
 * Run Server
 * --------------------------------------------------------------------------
 * After events configured, let's start running server.
 * This will hang process util a new request.
 * To stop server, use `$server->shutdown()` in request or press CTRL + C.
 */

$server->listen('0.0.0.0', $serverState->getPort() ?: 9501);
