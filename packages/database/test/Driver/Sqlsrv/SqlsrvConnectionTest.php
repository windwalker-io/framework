<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver\Sqlsrv;

use Windwalker\Database\Driver\AbstractConnection;
use Windwalker\Database\Driver\Sqlsrv\SqlsrvConnection;
use Windwalker\Database\Test\Driver\AbstractConnectionTest;

/**
 * The SqlsrvConnectionTest class.
 */
class SqlsrvConnectionTest extends AbstractConnectionTest
{
    protected static string $platform = 'SQLServer';

    protected static string $className = SqlsrvConnection::class;

    public function testConnect(): void
    {
        $conn = $this->instance;
        $conn->connect();

        $this->assertConnected($conn);
    }

    /**
     * assertConnected
     *
     * @param  SqlsrvConnection  $conn
     *
     * @return  void
     */
    public function assertConnected(AbstractConnection $conn): void
    {
        $res = $conn->get();

        $cursor = sqlsrv_query($res, 'SELECT 1');

        $r = sqlsrv_fetch($cursor, 0);

        self::assertEquals('', $r);
    }
}
