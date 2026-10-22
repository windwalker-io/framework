<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver\Pdo;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Windwalker\Database\Driver\DriverOptions;
use Windwalker\Database\Driver\Pdo\DsnHelper;
use Windwalker\Database\Driver\Pdo\PdoMysqlConnection;
use Windwalker\Database\Driver\Pdo\PdoPgsqlConnection;
use Windwalker\Database\Driver\Pgsql\PgsqlConnection;

class DsnHelperTest extends TestCase
{
    public function testLibpqValuesAreQuoted(): void
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

    public function testPreservesEmptyAndZeroValues(): void
    {
        self::assertSame(
            "password='' dbname='0' port='0'",
            DsnHelper::build(['password' => '', 'dbname' => '0', 'port' => 0, 'host' => null], null, ' ')
        );
        self::assertSame('mysql:dbname=0', DsnHelper::build(['dbname' => '0'], 'mysql'));
    }

    public function testPostgresqlStartupOptionsAreQuotedOnce(): void
    {
        $options = PgsqlConnection::prepareDbOptions(
            new DriverOptions(host: 'localhost', password: "a b'c\\d", charset: 'utf8')
        );

        self::assertSame(
            "host='localhost' password='a b\'c\\\\d' options='--client_encoding=UTF8'",
            $options->dsn
        );
    }

    public function testPdoPostgresqlUsesLibpqQuoting(): void
    {
        $options = PdoPgsqlConnection::prepareDbOptions(
            new DriverOptions(host: 'localhost', dbname: "demo' host=attacker")
        );

        self::assertSame("pgsql:host='localhost';dbname='demo\' host=attacker'", $options->dsn);
    }

    public function testMysqlDsnRemainsCompatible(): void
    {
        $options = PdoMysqlConnection::prepareDbOptions(new DriverOptions(dbname: 'test', port: 3306));

        self::assertSame('mysql:host=localhost;port=3306;dbname=test;charset=utf8mb4', $options->dsn);
    }

    #[DataProvider('invalidDsnProvider')]
    public function testRejectsInvalidDsnParameters(array $params, ?string $type, string $delimiter): void
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

    public function testRejectsAdditionalCharsetStartupOptions(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PgsqlConnection::prepareDbOptions(new DriverOptions(charset: 'UTF8 -c search_path=public'));
    }
}
