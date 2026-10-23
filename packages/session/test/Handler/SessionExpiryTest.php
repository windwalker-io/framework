<?php

declare(strict_types=1);

namespace Windwalker\Session\Test\Handler;

use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use Windwalker\Database\DatabaseFactory;
use Windwalker\Database\Driver\ConnectionInterface;
use Windwalker\Session\Bridge\PhpBridge;
use Windwalker\Session\Handler\AbstractHandler;
use Windwalker\Session\Handler\DatabaseHandler;
use Windwalker\Session\Handler\FilesystemHandler;
use Windwalker\Session\Handler\PdoHandler;
use Windwalker\Session\Test\SessionVfsTestTrait;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class SessionExpiryTest extends TestCase
{
    use SessionVfsTestTrait;

    #[DataProvider('handlerProvider')]
    public function testExpiredSessionsCannotBeReadOrValidated(string $type, bool $useOption, bool $customColumns): void
    {
        $previous = ini_set('session.gc_maxlifetime', $useOption ? '3600' : '60');

        try {
            $handler = $this->createHandler($type, $useOption, $customColumns);

            self::assertSame(serialize(['user' => 'fresh']), $handler->read('fresh'));
            self::assertTrue($handler->validateId('fresh'));
            self::assertSame(serialize(['user' => 'fresh']), $handler->read('fresh'));
            self::assertSame('', $handler->read('expired'));
            self::assertFalse($handler->validateId('expired'));
        } finally {
            ini_set('session.gc_maxlifetime', $previous);
        }
    }

    #[DataProvider('handlerProvider')]
    public function testStrictModeReplacesExpiredIdWithoutGarbageCollection(
        string $type,
        bool $useOption,
        bool $customColumns
    ): void {
        $previous = ini_set('session.gc_maxlifetime', $useOption ? '3600' : '60');

        try {
            $handler = $this->createHandler($type, $useOption, $customColumns);
            $bridge = new PhpBridge(['use_strict_mode' => true, 'gc_probability' => 0], $handler);
            $bridge->setId('expired');
            $bridge->start();

            self::assertNotSame('expired', $bridge->getId());
            self::assertSame([], $bridge->getStorage());
            $bridge->destroy();
        } finally {
            ini_set('session.gc_maxlifetime', $previous);
        }
    }

    public static function handlerProvider(): iterable
    {
        foreach (['filesystem', 'pdo', 'database'] as $type) {
            foreach ([true, false] as $useOption) {
                yield "$type:$useOption:default" => [$type, $useOption, false];

                if ($type !== 'filesystem') {
                    yield "$type:$useOption:custom" => [$type, $useOption, true];
                }
            }
        }
    }

    protected function createHandler(string $type, bool $useOption, bool $customColumns): AbstractHandler
    {
        $options = $useOption ? ['gc_maxlifetime' => 60] : [];

        if ($type === 'filesystem') {
            $this->prepareVfs(['tmp' => []]);
            $handler = new FilesystemHandler(self::getSessionPath(), $options);

            foreach (['fresh', 'expired'] as $id) {
                $handler->write($id, serialize(['user' => $id]));
            }

            touch($handler->getFilePath('expired'), time() - 120);
            clearstatcache();

            return $handler;
        }

        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            self::markTestSkipped('The pdo_sqlite extension is required.');
        }

        $database = null;

        if ($type === 'database') {
            $database = new DatabaseFactory()->create('pdo_sqlite', ['dsn' => 'sqlite::memory:']);
            $storage = $database->getDriver()->useConnection(static fn(ConnectionInterface $connection) => $connection->get());
        } else {
            $storage = new PDO('sqlite::memory:');
        }

        $options['table'] = $customColumns ? 'custom_sessions' : 'windwalker_sessions';
        $options['columns'] = $customColumns
            ? ['id' => 'session_id', 'data' => 'payload', 'time' => 'updated_at']
            : ['id' => 'id', 'data' => 'data', 'time' => 'time'];
        $columns = $options['columns'];
        $storage->exec(
            sprintf(
                'CREATE TABLE "%s" ("%s" TEXT PRIMARY KEY, "%s" TEXT NOT NULL, "%s" INTEGER NOT NULL)',
                $options['table'],
                $columns['id'],
                $columns['data'],
                $columns['time']
            )
        );
        $insert = $storage->prepare(sprintf('INSERT INTO "%s" VALUES (?, ?, ?)', $options['table']));
        $insert->execute(['fresh', serialize(['user' => 'fresh']), time()]);
        $insert->execute(['expired', serialize(['user' => 'expired']), time() - 120]);

        return $type === 'database' ? new DatabaseHandler($database, $options) : new PdoHandler($storage, $options);
    }
}

