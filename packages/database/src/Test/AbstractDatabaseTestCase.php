<?php

declare(strict_types=1);

namespace Windwalker\Database\Test;

use Windwalker\Database\DatabaseAdapter;
use Windwalker\Database\DatabaseFactory;
use Windwalker\Database\Event\QueryEndEvent;

/**
 * The AbstractDatabaseTestCase class.
 */
abstract class AbstractDatabaseTestCase extends AbstractDatabaseDriverTestCase
{
    protected static string $platform = 'MySQL';

    protected static string $driver = 'pdo_mysql';

    protected static bool $logInited = false;

    protected static ?DatabaseAdapter $db;

    protected static array $lastQueries = [];

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        static::$db = self::createAdapter();
    }

    protected static function createAdapter(?array $params = null): DatabaseAdapter
    {
        $params = $params ?? self::getTestParams();
        $params['driver'] = static::$driver;
        static::$lastQueries = [];

        $db = (new DatabaseFactory())->create(
            static::$driver,
            $params
        );

        $logFile = __DIR__ . '/../../tmp/test-sql.sql';

        if (!self::$logInited) {
            @unlink($logFile);

            self::$logInited = true;
        }

        $db->on(
            QueryEndEvent::class,
            function (QueryEndEvent $event) use ($logFile) {
                static::$lastQueries[] = $event->sql;

                $fp = fopen($logFile, 'ab+');

                // Use getter to test the B/C
                fwrite($fp, $event->getDebugQueryString() . ";\n\n");

                fclose($fp);
            }
        );

        return $db;
    }

    /**
     * @inheritDoc
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        static::$db->getDriver()->disconnectAll();
        static::$db = null;
    }

    public function logQueries(callable $callback, &$result = null): array
    {
        $logs = [];
        $fp = function (QueryEndEvent $event) use (&$logs) {
            return $logs[] = $event->debugQueryString;
        };

        static::$db->on(QueryEndEvent::class, $fp);

        $result = $callback();

        static::$db->getEventDispatcher()->remove($fp);

        return $logs;
    }
}
