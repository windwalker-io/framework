<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver\Pdo;

use RuntimeException;
use Windwalker\Database\Driver\Pdo\PdoSqliteConnection;

/**
 * The PdoSqliteConnection class.
 */
class PdoSqliteConnectionTest extends AbstractPdoConnectionTestCase
{
    protected static string $platform = 'SQLite';

    protected static string $className = PdoSqliteConnection::class;

    public function testConnectWrong()
    {
        $conn = $this->instance;

        // Direct to self so that sqlite unable to create db
        $conn->setOption('dbname', __DIR__);

        $this->expectException(RuntimeException::class);
        $conn->connect();
    }
}
