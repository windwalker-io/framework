<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver\Pdo;

use Windwalker\Database\Test\Driver\AbstractDriverTest;

/**
 * The PdoPgsqlDriverTest class.
 */
class PdoPgsqlDriverTest extends AbstractDriverTest
{
    protected static string $platform = 'PostgreSQL';

    protected static string $driverName = 'pdo_pgsql';
}
