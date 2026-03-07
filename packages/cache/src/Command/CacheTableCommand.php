<?php

declare(strict_types=1);

namespace Windwalker\Cache\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Windwalker\Console\CommandInterface;
use Windwalker\Console\CommandWrapper;
use Windwalker\Console\IOInterface;
use Windwalker\Core\Console\ConsoleApplication;
use Windwalker\Core\Migration\MigrationService;

/**
 * The CacheTableCommand class.
 */
#[CommandWrapper(
    description: 'Create cache migration file.'
)]
class CacheTableCommand implements CommandInterface
{
    public function __construct(protected ConsoleApplication $app)
    {
    }

    /**
     * configure
     *
     * @param  Command  $command
     *
     * @return  void
     */
    public function configure(Command $command): void
    {
        $command->addArgument(
            'name',
            InputArgument::OPTIONAL,
            'Migration name.',
            'CacheInit'
        );
    }

    /**
     * Executes the current command.
     *
     * @param  IOInterface  $io
     *
     * @return  int Return 0 is success, 1-255 is failure.
     */
    public function execute(IOInterface $io): int
    {
        $migrationService = $this->app->service(MigrationService::class);

        $migrationService->copyMigrationFile(
            $this->app->path('@migrations'),
            $io->getArgument('name'),
            __DIR__ . '/../../resources/templates/*'
        );

        return 0;
    }
}
