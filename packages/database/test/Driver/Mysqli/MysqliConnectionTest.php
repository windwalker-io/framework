<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver\Mysqli;

use Windwalker\Database\Driver\AbstractConnection;
use Windwalker\Database\Driver\Mysqli\MysqliConnection;
use Windwalker\Database\Test\Driver\AbstractConnectionTest;
use Windwalker\Database\Test\Reseter\MySQLTestTrait;

/**
 * The MysqliConnectionTest class.
 */
class MysqliConnectionTest extends AbstractConnectionTest
{
    protected static string $platform = 'MySQL';

    protected static string $className = MysqliConnection::class;

    public function assertConnected(AbstractConnection $conn): void
    {
        $mysqli = $conn->get();

        $r = $mysqli->query('SELECT 1')->fetch_row();

        self::assertEquals([1], $r);
    }
}
