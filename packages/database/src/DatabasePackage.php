<?php

declare(strict_types=1);

namespace Windwalker\Database;

use Monolog\Handler\AbstractHandler;
use Monolog\Handler\Handler;
use Monolog\Handler\HandlerWrapper;
use Monolog\Logger;
use Monolog\LogRecord;
use Windwalker\Core\Application\ApplicationInterface;
use Windwalker\Core\Application\AppType;
use Windwalker\Core\CliServer\CliServerClient;
use Windwalker\Core\CliServer\CliServerRuntime;
use Windwalker\Core\Database\DatabaseExportService;
use Windwalker\Core\Factory\DatabaseServiceFactory;
use Windwalker\Core\Manager\DatabaseManager;
use Windwalker\Core\Migration\MigrationService;
use Windwalker\Core\Package\AbstractPackage;
use Windwalker\Core\Package\PackageInstaller;
use Windwalker\Core\Seed\FakerService;
use Windwalker\DI\BootableProviderInterface;
use Windwalker\DI\Container;
use Windwalker\DI\DIOptions;
use Windwalker\DI\ServiceProviderInterface;
use Windwalker\ORM\ORM;
use Windwalker\Pool\PoolInterface;
use Windwalker\Pool\PoolOptions;
use Windwalker\Pool\Stack\SingleStack;
use Windwalker\Pool\Stack\SwooleStack;

use function Windwalker\swoole_in_coroutine;

/**
 * The DatabasePackage class.
 */
class DatabasePackage extends AbstractPackage implements ServiceProviderInterface, BootableProviderInterface
{
    public function __construct(protected ApplicationInterface $app)
    {
    }

    public function install(PackageInstaller $installer): void
    {
        $installer->installConfig(__DIR__ . '/../etc/*.php', 'config');
    }

    /**
     * @inheritDoc
     */
    public function boot(Container $container): void
    {
        if (
            $this->app->getType() === AppType::CLI_WEB
            && CliServerRuntime::getInspector()->shouldEnableConnectionPool()
        ) {
            // Init Connection Pools
            $this->initDriverAndConnectionPools($container);
        }
    }

    /**
     * Registers the service provider with a DI container.
     *
     * @param  Container  $container  The DI container.
     *
     * @return  void
     * @throws \Windwalker\DI\Exception\DefinitionException
     */
    public function register(Container $container): void
    {
        $container->prepareSharedObject(DatabaseManager::class);
        $container->prepareSharedObject(DatabaseServiceFactory::class);
        $container->prepareSharedObject(DatabaseFactory::class);
        $container->bindShared(
            DatabaseAdapter::class,
            fn(DatabaseServiceFactory $factory, ?string $tag = null) => $factory->get($tag),
            new DIOptions(isolation: true)
        );
        $container->bindShared(
            ORM::class,
            fn(Container $container, ?string $tag = null)
                => $container->get(DatabaseAdapter::class, tag: $tag)->orm(),
            new DIOptions(isolation: true)
        );

        // Faker
        $container->prepareSharedObject(FakerService::class);

        // Services
        $container->prepareSharedObject(DatabaseExportService::class);
        $container->prepareObject(MigrationService::class);
    }

    public function initDriverAndConnectionPools(Container $container): void
    {
        $databaseFactory = $container->newInstance(DatabaseFactory::class);
        $connections = $container->getParam('database.connections');

        $this->app->log("Enable DB Connection Pool");

        foreach ($connections as $connection => $connConfig) {
            $this->app->log("[DB][$connection] Initializing connection pool");

            $poolConfig = PoolOptions::wrapWith($connConfig['pool'] ?? []);
            $poolConfig = $this->preparePoolConfig($poolConfig);

            $pool = $databaseFactory->createConnectionPool(
                $poolConfig,
                $this->app->isCliRuntime() && swoole_in_coroutine()
                    ? new SwooleStack()
                    : new SingleStack(),
                $this->createPoolLogger($connection)
            );

            $driver = $databaseFactory->createDriver(
                $connConfig['driver'],
                $connConfig['options'],
                $pool
            );

            $pool->setConnectionBuilder(fn () => $driver->createConnection());
            $pool->init();

            $this->app->log("  Connections created, count: " . $pool->count());

            $container->share('database.connection.driver.' . $connection, $driver);

            $this->app->log("  Create DB driver: " . $connConfig['driver']);
        }
    }

    protected function preparePoolConfig(PoolOptions $poolConfig): PoolOptions
    {
        $state = CliServerRuntime::getServerState();
        $mainServState = $state->getServer();

        // Set MAX_SIZE if not exists
        if ($poolConfig->maxSize >= 0) {
            $poolMaxSize = $mainServState['worker_num'] ?? null;

            $poolConfig[PoolInterface::MAX_SIZE] = $poolMaxSize ?? swoole_cpu_num();
        }

        return $poolConfig;
    }

    /**
     * @param  int|string  $connection
     *
     * @return  Logger
     */
    protected function createPoolLogger(int|string $connection): Logger
    {
        $logger = new Logger('connection-pool-' . $connection);

        $handler = new class extends AbstractHandler {
            public ApplicationInterface $app;

            public function handle(LogRecord $record): bool
            {
                $this->app->log(
                    '  ' . $record->message,
                    [],
                    $record->level->toPsrLogLevel()
                );
                return true;
            }
        };
        $handler->app = $this->app;

        return $logger->pushHandler($handler);
    }
}
