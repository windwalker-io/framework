<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver\Pdo;

use PDO;
use Windwalker\Database\Driver\AbstractConnection;
use Windwalker\Database\Driver\Pdo\AbstractPdoConnection;
use Windwalker\Database\Test\Driver\AbstractConnectionTestCase;

/**
 * The AbstractPdoConnectionTest class.
 */
abstract class AbstractPdoConnectionTestCase extends AbstractConnectionTestCase
{
    /**
     * assertConnected
     *
     * @param  AbstractPdoConnection  $conn
     *
     * @return  void
     */
    public function assertConnected(AbstractConnection $conn): void
    {
        $pdo = $conn->get();

        $r = $pdo->query('SELECT 1')->fetch(PDO::FETCH_NUM);

        self::assertEquals([1], $r);
    }
}
