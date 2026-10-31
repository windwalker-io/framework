<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver\Pdo;

use RuntimeException;
use Windwalker\Database\Driver\Pdo\PdoSqliteConnection;
use Windwalker\Database\Platform\AbstractPlatform;

/**
 * The PdoSqliteConnection class.
 */
class PdoSqliteConnectionTest extends AbstractPdoConnectionTestCase
{
    protected static string $platform = AbstractPlatform::SQLITE;

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
