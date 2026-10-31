<?php

declare(strict_types=1);

namespace Windwalker\Query\Test\Bounded;

use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Query\Bounded\ParamType;

class ParamTypeTest extends TestCase
{
    #[Test]
    #[DataProvider('pdoTypesProvider')]
    public function convertToPdoMapsTypeToPdoConstant(?string $type, ?int $expected): void
    {
        self::assertSame($expected, ParamType::convertToPDO($type));
    }

    public static function pdoTypesProvider(): array
    {
        return [
            'string' => [ParamType::STRING, PDO::PARAM_STR],
            'int' => [ParamType::INT, PDO::PARAM_INT],
            'float' => [ParamType::FLOAT, PDO::PARAM_STR],
            'blob' => [ParamType::BLOB, PDO::PARAM_LOB],
            'bool' => [ParamType::BOOL, PDO::PARAM_BOOL],
            'null' => [ParamType::NULL, PDO::PARAM_NULL],
            'null type' => [null, null],
        ];
    }

    #[Test]
    #[DataProvider('mysqliTypesProvider')]
    public function convertToMysqliMapsTypeToBindChar(string $type, string $expected): void
    {
        self::assertSame($expected, ParamType::convertToMysqli($type));
    }

    public static function mysqliTypesProvider(): array
    {
        return [
            'string' => [ParamType::STRING, 's'],
            'int' => [ParamType::INT, 'i'],
            'bool' => [ParamType::BOOL, 'i'],
            'null' => [ParamType::NULL, 'i'],
            'float' => [ParamType::FLOAT, 'd'],
            'blob' => [ParamType::BLOB, 'b'],
            'unknown passes through' => ['x', 'x'],
        ];
    }
}
