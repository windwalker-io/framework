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
use Windwalker\Core\Manager\DatabaseManager;
use Windwalker\Core\Migration\MigrationService;
use Windwalker\Core\Package\AbstractPackage;
use Windwalker\Core\Package\PackageInstaller;
use Windwalker\Core\Seed\FakerService;
use Windwalker\DI\BootableProviderInterface;
use Windwalker\DI\Container;
use Windwalker\DI\ServiceProviderInterface;
use Windwalker\ORM\ORM;
use Windwalker\Pool\PoolInterface;
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
        $container->prepareSharedObject(DatabaseFactory::class);
        $container->bindShared(
            DatabaseAdapter::class,
            fn(DatabaseManager $manager) => $manager->get(),
            Container::ISOLATION
        );
        $container->bindShared(
            ORM::class,
            fn(DatabaseManager $manager) => $manager->get()->orm(),
            Container::ISOLATION
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

            $poolConfig = $connConfig['pool'] ?? [];
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

    protected function preparePoolConfig(array $poolConfig): array
    {
        $state = CliServerRuntime::getServerState();
        $mainServState = $state->getServer();

        $default = [
            PoolInterface::MIN_SIZE => 1,
            PoolInterface::MAX_WAIT => -1,
            PoolInterface::WAIT_TIMEOUT => -1,
            PoolInterface::IDLE_TIMEOUT => 60,
            PoolInterface::CLOSE_TIMEOUT => 3,
        ];

        $poolConfig = array_merge($default, $poolConfig);

        // Set MAX_SIZE if not exists
        if ($poolConfig[PoolInterface::MAX_SIZE] ?? null) {
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
