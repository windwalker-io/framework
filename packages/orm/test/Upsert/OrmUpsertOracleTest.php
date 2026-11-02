<?php

declare(strict_types=1);

namespace Windwalker\ORM\Test\Upsert;

use Windwalker\Database\Platform\DbPlatform;

final class OrmUpsertOracleTest extends AbstractOrmUpsertTestCase
{
    protected static DbPlatform $platform = DbPlatform::Oracle;
    protected static string $driver = 'pdo_oci';
}
