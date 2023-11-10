<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver\Pgsql;

use Windwalker\Database\Test\Driver\AbstractDriverTestCase;

/**
 * The PgsqlDriverTest class.
 */
class PgsqlDriverTest extends AbstractDriverTestCase
{
    protected static string $platform = 'PostgreSQL';

    protected static string $driverName = 'pgsql';

    protected static function setupDatabase(): void
    {
        parent::setupDatabase();
    }
}
