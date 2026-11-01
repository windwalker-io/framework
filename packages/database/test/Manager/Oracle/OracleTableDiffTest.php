<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\Oracle;

use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Manager\AbstractTableDiffTestCase;

class OracleTableDiffTest extends AbstractTableDiffTestCase
{
    protected static DbPlatform $platform = DbPlatform::Oracle;

    protected static string $driver = 'pdo_oci';
}
