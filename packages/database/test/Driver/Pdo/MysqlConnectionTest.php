<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver\Pdo;

use PDO;
use Windwalker\Database\Driver\AbstractConnection;
use Windwalker\Database\Driver\Pdo\PdoMysqlConnection;
use Windwalker\Database\Test\Driver\AbstractConnectionTestCase;

/**
 * The MysqlConnectionTest class.
 */
class MysqlConnectionTest extends AbstractConnectionTestCase
{
    protected static string $platform = 'MySQL';

    protected static string $className = PdoMysqlConnection::class;

    public function assertConnected(AbstractConnection $conn): void
    {
        $pdo = $conn->get();

        $r = $pdo->query('SELECT 1')->fetch(PDO::FETCH_NUM);

        self::assertEquals([1], $r);
    }
}
