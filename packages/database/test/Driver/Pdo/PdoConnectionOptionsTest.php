<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver\Pdo;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Database\Driver\DriverOptions;
use Windwalker\Database\Driver\Pdo\PdoMysqlConnection;
use Windwalker\Database\Driver\Pdo\PdoPgsqlConnection;
use Windwalker\Database\Driver\Pdo\PdoSqlsrvConnection;

/**
 * Tests DSN/option building that does not require a live database.
 */
class PdoConnectionOptionsTest extends TestCase
{
    #[Test]
    public function mysqlBuildsDsnAndInitCommand(): void
    {
        $options = PdoMysqlConnection::prepareDbOptions(
            new DriverOptions(host: 'localhost', dbname: 'foo')
        );

        self::assertStringContainsString('host=localhost', $options->dsn);
        self::assertStringContainsString('dbname=foo', $options->dsn);
        self::assertStringContainsString('charset=utf8mb4', $options->dsn);
        self::assertSame('SET NAMES utf8mb4', $options->driverOptions[\Pdo\Mysql::ATTR_INIT_COMMAND]);
    }

    #[Test]
    public function mysqlUsesUtf8InitCommandAndKeepsExistingDsn(): void
    {
        $options = PdoMysqlConnection::prepareDbOptions(
            new DriverOptions(host: 'localhost', dsn: 'mysql:custom', charset: 'utf8')
        );

        self::assertSame('mysql:custom', $options->dsn);
        self::assertSame('SET NAMES utf8', $options->driverOptions[\Pdo\Mysql::ATTR_INIT_COMMAND]);
    }

    #[Test]
    public function mysqlMapsTlsOptions(): void
    {
        $options = PdoMysqlConnection::prepareDbOptions(
            new DriverOptions(host: 'localhost', tls: ['ca' => '/ca.pem', 'verify' => false])
        );

        self::assertSame('/ca.pem', $options->driverOptions[\Pdo\Mysql::ATTR_SSL_CA]);
        self::assertNull($options->driverOptions[\Pdo\Mysql::ATTR_SSL_KEY]);
        self::assertFalse($options->driverOptions[\Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT]);
    }

    #[Test]
    public function pgsqlBuildsDsnWithTls(): void
    {
        $options = PdoPgsqlConnection::prepareDbOptions(
            new DriverOptions(host: 'localhost', dbname: 'foo', tls: ['ca' => '/ca.pem', 'verify' => true])
        );

        self::assertStringStartsWith('pgsql:', $options->dsn);
        self::assertStringContainsString("sslrootcert='/ca.pem'", $options->dsn);
        self::assertStringContainsString("sslmode='verify-full'", $options->dsn);

        $options = PdoPgsqlConnection::prepareDbOptions(
            new DriverOptions(host: 'localhost', tls: ['verify' => false])
        );

        self::assertStringContainsString("sslmode='require'", $options->dsn);
    }

    #[Test]
    public function sqlsrvBuildsDsnWithPortAndEncryption(): void
    {
        $options = PdoSqlsrvConnection::prepareDbOptions(
            new DriverOptions(host: 'localhost', port: 1433, dbname: 'foo', tls: ['verify' => false])
        );

        self::assertStringStartsWith('sqlsrv:', $options->dsn);
        self::assertStringContainsString('Server=localhost,1433', $options->dsn);
        self::assertStringContainsString('Encrypt=yes', $options->dsn);
        self::assertStringContainsString('TrustServerCertificate=yes', $options->dsn);

        $options = PdoSqlsrvConnection::prepareDbOptions(
            new DriverOptions(host: 'localhost', tls: false)
        );

        self::assertStringContainsString('Encrypt=no', $options->dsn);
    }
}
