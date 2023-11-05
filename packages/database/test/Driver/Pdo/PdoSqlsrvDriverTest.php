<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver\Pdo;

use Windwalker\Database\Test\Driver\AbstractDriverTest;

/**
 * The PdoSqlsrvDriverTest class.
 */
class PdoSqlsrvDriverTest extends AbstractDriverTest
{
    protected static string $platform = 'SQLServer';

    protected static string $driverName = 'pdo_sqlsrv';

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        // Fix for MacOS ODBC driver 17.2 issue
        // @see https://github.com/Microsoft/msphpsql/issues/909
        setlocale(LC_ALL, 'en_GB');

        parent::setUpBeforeClass();
    }
}
