<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver\Pdo;

use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Windwalker\Database\Driver\Pdo\PdoSqliteConnection;
use Windwalker\Database\Platform\DbPlatform;

/**
 * The PdoSqliteConnection class.
 */
class PdoSqliteConnectionTest extends AbstractPdoConnectionTestCase
{
    protected static DbPlatform $platform = DbPlatform::SQLite;

    protected static string $className = PdoSqliteConnection::class;

    #[Test]
    public function connectFailsWithInvalidCredentials(): void
    {
        $conn = $this->instance;

        // Direct to self so that sqlite unable to create db
        $conn->setOption('dbname', __DIR__);

        $this->expectException(RuntimeException::class);
        $conn->connect();
    }
}
