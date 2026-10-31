<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Schema\Ddl;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Database\Schema\Ddl\Column;

/**
 * The ColumnTest class.
 */
class ColumnTest extends TestCase
{
    #[Test]
    #[DataProvider('columnDefinitionsProvider')]
    public function constructParsesDataTypeLength(
        string $name,
        string $definition,
        string $dataType,
        ?int $precision,
        ?int $scale,
        int|string|null $characterLength,
        ?string $lengthExpression
    ): void {
        $column = new Column($name, $definition, false, 1);

        self::assertSame($dataType, $column->getDataType());
        self::assertSame($precision, $column->getNumericPrecision());
        self::assertSame($scale, $column->getNumericScale());
        self::assertSame($characterLength, $column->getCharacterMaximumLength());
        self::assertSame($lengthExpression, $column->getLengthExpression());
    }

    public static function columnDefinitionsProvider(): array
    {
        return [
            'integer without length' => ['id', 'integer', 'integer', null, null, null, null],
            'varchar with character length' => ['alias', 'varchar(255)', 'varchar', null, null, 255, '255'],
            'integer with precision' => ['id', 'int(11)', 'int', 11, null, null, '11'],
            'decimal with precision and scale' => ['price', 'decimal(10,6)', 'decimal', 10, 6, null, '10,6']
        ];
    }
}
