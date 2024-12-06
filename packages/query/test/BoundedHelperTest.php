<?php

declare(strict_types=1);

namespace Windwalker\Query\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\Query\Bounded\BoundedHelper;
use Windwalker\Query\Bounded\ParamType;
use Windwalker\Query\Query;
use Windwalker\Query\Test\Mock\MockEscaper;
use Windwalker\Test\Traits\QueryTestTrait;

/**
 * The QueryHelperTest class.
 */
class BoundedHelperTest extends TestCase
{
    use QueryTestTrait;

    /**
     *
     * @param  string  $sql
     * @param  string  $symbol
     * @param  array   $params
     * @param  string  $expct
     * @param  array   $expctParams
     *
     * @see          BoundedHelper::replaceParams
     *
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('replaceParamsProvider')]
    public function testReplaceParams(
        string $sql,
        string $symbol,
        array $params,
        string $expct,
        array $expctParams
    ): void {
        [$sql2, $params2] = BoundedHelper::replaceParams($sql, $symbol, $params);

        self::assertSqlEquals(
            $expct,
            $sql2
        );

        self::assertEquals(
            $expctParams,
            $params2
        );
    }

    public static function replaceParamsProvider(): array
    {
        return [
            [
                'SELECT * FROM foo WHERE id IN(?, ?) AND title != :title AND alias != :alias AND created_by = ?',
                '?',
                [
                    ':title' => 'A',
                    0 => 5,
                    1 => 7,
                    ':alias' => 'B',
                    2 => 123,
                ],
                'SELECT * FROM foo WHERE id IN(?, ?) AND title != ? AND alias != ? AND created_by = ?',
                [
                    5,
                    7,
                    'A',
                    'B',
                    123,
                ],
            ],
            [
                'SELECT * FROM foo WHERE id IN(?, ?) AND title != :title AND alias != :alias AND created_by = ?',
                '$%d',
                [
                    ':title' => 'A',
                    0 => 5,
                    1 => 7,
                    ':alias' => 'B',
                    2 => 123,
                ],
                'SELECT * FROM foo WHERE id IN($1, $2) AND title != $3 AND alias != $4 AND created_by = $5',
                [
                    5,
                    7,
                    'A',
                    'B',
                    123,
                ],
            ],
            [
                'SELECT * FROM foo WHERE id IN(?, ?) AND title != :title AND alias != :alias AND created_by = ?',
                '$%d',
                [
                    0 => 5,
                    1 => 7,
                    ':alias' => 'B',
                    2 => 123,
                ],
                'SELECT * FROM foo WHERE id IN($1, $2) AND title != :title AND alias != $3 AND created_by = $4',
                [
                    5,
                    7,
                    'B',
                    123,
                ],
            ],
            [
                'SELECT * FROM foo WHERE id = ? AND title = :title',
                '?',
                [
                    0 => [
                        'value' => 5,
                        'dataType' => ParamType::INT,
                    ],
                    ':title' => [
                        'value' => 'B',
                        'dataType' => ParamType::STRING,
                    ],
                ],
                'SELECT * FROM foo WHERE id = ? AND title = ?',
                [
                    0 => [
                        'value' => 5,
                        'dataType' => ParamType::INT,
                    ],
                    1 => [
                        'value' => 'B',
                        'dataType' => ParamType::STRING,
                    ],
                ],
            ],
        ];
    }

    /**
     * @see  BoundedHelper::emulatePrepared
     */
    public function testEmulatePrepared(): void
    {
        $sql = 'SELECT * FROM foo WHERE foo = :foo AND bar = ? AND yoo IN(?, ?, ?) AND flower = :flower';

        $query = new Query();
        $query->bind(
            [
                'baz',
                1,
                2,
                3,
                ':foo' => 'FOO',
                ':flower' => 'Sakura',
            ]
        );

        $sql2 = BoundedHelper::emulatePrepared(
            new MockEscaper(),
            $sql,
            $query->getBounded()
        );

        self::assertEquals(
            "SELECT * FROM foo WHERE foo = 'FOO' AND bar = 'baz' AND yoo IN(1, 2, 3) AND flower = 'Sakura'",
            $sql2
        );
    }
}
