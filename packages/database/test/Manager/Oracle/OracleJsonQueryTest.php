<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\Oracle;

use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Manager\AbstractJsonQueryTestCase;

class OracleJsonQueryTest extends AbstractJsonQueryTestCase
{
    protected static DbPlatform $platform = DbPlatform::Oracle;

    protected static string $driver = 'pdo_oci';

    protected static function createJsonTable(): void
    {
        self::$baseConn->exec("BEGIN EXECUTE IMMEDIATE 'DROP TABLE ww_json_items'; EXCEPTION WHEN OTHERS THEN NULL; END;");
        self::$baseConn->exec('CREATE TABLE ww_json_items (id NUMBER PRIMARY KEY, data CLOB CHECK (data IS JSON))');
    }
}
