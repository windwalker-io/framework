<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver\Pdo;

use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Driver\AbstractDriverTestCase;

/**
 * The PdoOciDriverTest class.
 */
class PdoOciDriverTest extends AbstractDriverTestCase
{
    protected static DbPlatform $platform = DbPlatform::Oracle;

    protected static string $driverName = 'pdo_oci';
}
