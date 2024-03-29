<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver\Pdo;

use Windwalker\Database\Test\Driver\AbstractDriverTestCase;

/**
 * The PdoPgsqlDriverTest class.
 */
class PdoPgsqlDriverTest extends AbstractDriverTestCase
{
    protected static string $platform = 'PostgreSQL';

    protected static string $driverName = 'pdo_pgsql';
}
