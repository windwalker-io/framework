<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver\Mysqli;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Exception\StatementException;
use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Driver\AbstractDriverTestCase;

/**
 * The MysqliDriverTest class.
 */
class MysqliDriverTest extends AbstractDriverTestCase
{
    protected static DbPlatform $platform = DbPlatform::MySQL;

    protected static string $driverName = 'mysqli';

    /**
     * @see  AbstractDriver::quote
     */
    #[Test]
    public function quote(): void
    {
        self::assertEquals(
            "'foo\'s #hello --options'",
            static::$driver->quote("foo's #hello --options")
        );
    }

    /**
     * @see  AbstractDriver::escape
     */
    #[Test]
    public function escape(): void
    {
        self::assertEquals(
            "foo\'s #hello --options",
            static::$driver->escape("foo's #hello --options")
        );
    }

    #[Test]
    public function getVersion(): void
    {
        self::assertMatchesRegularExpression('/^\d+\.\d+\.\d+/', static::$driver->getVersion());
    }

    #[Test]
    public function transactionCommit(): void
    {
        static::$driver->execute('CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tran (id INT)');
        static::$driver->transactionStart();
        static::$driver->execute('INSERT INTO tmp_tran (id) VALUES (1)');
        self::assertTrue(static::$driver->transactionCommit());

        self::assertEquals(1, static::$driver->prepare('SELECT COUNT(*) AS c FROM tmp_tran')->fetch()['c']);
        static::$driver->execute('DROP TEMPORARY TABLE tmp_tran');
    }

    #[Test]
    public function transactionRollback(): void
    {
        $driver = static::$driver;
        $driver->execute('DROP TABLE IF EXISTS ww_tran_test');
        $driver->execute('CREATE TABLE ww_tran_test (id INT) ENGINE=InnoDB');

        try {
            $driver->transactionStart();
            $driver->execute('INSERT INTO ww_tran_test (id) VALUES (1)');
            self::assertTrue($driver->transactionRollback());

            self::assertEquals(0, $driver->prepare('SELECT COUNT(*) AS c FROM ww_tran_test')->fetch()['c']);
        } finally {
            $driver->execute('DROP TABLE IF EXISTS ww_tran_test');
        }
    }

    #[Test]
    public function statementCountAffectedThrowsWhenClosed(): void
    {
        $stmt = static::$driver->prepare('SELECT 1');

        $this->expectException(StatementException::class);
        $stmt->countAffected();
    }

    #[Test]
    public function statementCloseResetsState(): void
    {
        $stmt = static::$driver->prepare('SELECT 1 AS n');
        $stmt->execute();

        self::assertEquals(1, $stmt->fetch()['n']);

        $stmt->close();

        self::assertNull($stmt->cursor);
        self::assertFalse($stmt->executed);
    }
}
