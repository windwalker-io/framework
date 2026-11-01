<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Database\DatabaseFactory;
use Windwalker\Database\Driver\AbstractDriver;
use Windwalker\Database\Exception\DatabaseQueryException;

class ReadonlyTest extends TestCase
{
    #[Test]
    #[DataProvider('queryProvider')]
    public function isWriteQuery(string $sql, bool $expected): void
    {
        self::assertSame($expected, AbstractDriver::isWriteQuery($sql));
    }

    public static function queryProvider(): array
    {
        return [
            ['SELECT * FROM a', false],
            ['  /* c */ (SELECT 1)', false],
            ['INSERT INTO a VALUES (1)', true],
            ["-- x\nupdate a set b = 1", true],
            ['DELETE FROM a', true],
            ['CREATE TABLE a (id int)', true],
            ['WITH t AS (SELECT 1) DELETE FROM a', true],
            ['WITH t AS (SELECT 1) SELECT * FROM t', false],
            ['SELECT * FROM a FOR UPDATE', true],
        ];
    }

    #[Test]
    public function readonlyThrow(): void
    {
        $db = $this->createDb(true);

        $this->expectException(DatabaseQueryException::class);

        $db->execute('CREATE TABLE a (id int)');
    }

    #[Test]
    public function readonlySilent(): void
    {
        $db = $this->createDb(false);

        $db->execute('CREATE TABLE a (id int)');

        self::assertSame(1, (int) $db->prepare('SELECT 1')->result());
    }

    private function createDb(bool $throw)
    {
        return new DatabaseFactory()->create(
            'pdo_sqlite',
            [
                'dbname' => ':memory:',
                'readonly' => true,
                'readonlyThrow' => $throw,
                'readonlyNative' => true,
            ]
        );
    }
}
