<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver\Pdo;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Database\Driver\DriverOptions;
use Windwalker\Database\Driver\Pdo\DsnHelper;
use Windwalker\Database\Driver\Pdo\PdoMysqlConnection;
use Windwalker\Database\Driver\Pdo\PdoPgsqlConnection;
use Windwalker\Database\Driver\Pgsql\PgsqlConnection;

class DsnHelperTest extends TestCase
{
    #[Test]
    public function libpqValuesAreQuoted(): void
    {
        self::assertSame(
            "host='localhost' password='a b\'c\\\\d' dbname='demo host=attacker'",
            DsnHelper::build(
                ['host' => 'localhost', 'password' => "a b'c\\d", 'dbname' => 'demo host=attacker'],
                null,
                ' '
            )
        );
    }

    #[Test]
    public function preservesEmptyAndZeroValues(): void
    {
        self::assertSame(
            "password='' dbname='0' port='0'",
            DsnHelper::build(['password' => '', 'dbname' => '0', 'port' => 0, 'host' => null], null, ' ')
        );
        self::assertSame('mysql:dbname=0', DsnHelper::build(['dbname' => '0'], 'mysql'));
    }

    #[Test]
    public function postgresqlStartupOptionsAreQuotedOnce(): void
    {
        $options = PgsqlConnection::prepareDbOptions(
            new DriverOptions(host: 'localhost', password: "a b'c\\d", charset: 'utf8')
        );

        self::assertSame(
            "host='localhost' password='a b\'c\\\\d' options='--client_encoding=UTF8'",
            $options->dsn
        );
    }

    #[Test]
    public function pdoPostgresqlUsesLibpqQuoting(): void
    {
        $options = PdoPgsqlConnection::prepareDbOptions(
            new DriverOptions(host: 'localhost', dbname: "demo' host=attacker")
        );

        self::assertSame("pgsql:host='localhost';dbname='demo\' host=attacker'", $options->dsn);
    }

    #[Test]
    public function mysqlDsnRemainsCompatible(): void
    {
        $options = PdoMysqlConnection::prepareDbOptions(new DriverOptions(dbname: 'test', port: 3306));

        self::assertSame('mysql:host=localhost;port=3306;dbname=test;charset=utf8mb4', $options->dsn);
    }

    #[DataProvider('invalidDsnProvider')]
    #[Test]
    public function rejectsInvalidDsnParameters(array $params, ?string $type, string $delimiter): void
    {
        $this->expectException(InvalidArgumentException::class);

        DsnHelper::build($params, $type, $delimiter);
    }

    public static function invalidDsnProvider(): array
    {
        return [
            'mysql separator' => [['dbname' => 'demo;host=attacker'], 'mysql', ';'],
            'pgsql separator' => [['dbname' => 'demo;host=attacker'], 'pgsql', ';'],
            'sqlsrv separator' => [['Database' => 'demo;Server=attacker'], 'sqlsrv', ';'],
            'null byte' => [['password' => "secret\0host=attacker"], null, ' '],
            'parameter name' => [['dbname host' => 'attacker'], null, ' '],
            'delimiter' => [['host' => 'localhost'], null, ','],
        ];
    }

    #[Test]
    public function rejectsAdditionalCharsetStartupOptions(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PgsqlConnection::prepareDbOptions(new DriverOptions(charset: 'UTF8 -c search_path=public'));
    }
}
