<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Schema\Ddl;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Database\Platform\Type\MySQLDataType;
use Windwalker\Database\Platform\Type\OracleDataType;
use Windwalker\Database\Platform\Type\PostgreSQLDataType;
use Windwalker\Database\Platform\Type\SQLServerDataType;
use Windwalker\Database\Platform\Type\SQLiteDataType;
use Windwalker\Database\Schema\Ddl\Column;

use function Windwalker\raw;

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

    #[Test]
    public function generatedColumn(): void
    {
        $column = new Column('double_price', 'integer', false, 5);

        self::assertFalse($column->isGenerated());
        self::assertTrue($column->canHasDefaultValue());

        $column->virtual('price * 2');

        self::assertTrue($column->isGenerated());
        self::assertFalse($column->isGeneratedStored());
        self::assertSame('price * 2', $column->getGenerationExpression());
        self::assertFalse($column->canHasDefaultValue());

        $column->stored(raw('price * 3'));

        self::assertTrue($column->isGeneratedStored());
        self::assertSame('price * 3', $column->getGenerationExpression());
    }

    #[Test]
    public function datetimeTypeKeepsFractionalSecondsLength(): void
    {
        $column = new Column('updated', 'datetime');
        $column->length(6);

        self::assertSame('datetime(6)', $column->getTypeExpression(new MySQLDataType()));
        self::assertSame('datetime(6)', $column->getTypeExpression(new PostgreSQLDataType()));
        self::assertSame('datetime(6)', $column->getTypeExpression(new SQLiteDataType()));
        self::assertSame('datetime(6)', $column->getTypeExpression(new SQLServerDataType()));

        $column = new Column('created', 'timestamp(3)');

        self::assertSame('timestamp(3)', $column->getTypeExpression(new MySQLDataType()));
        self::assertSame('date', new Column('d', 'date')->length(6)->getTypeExpression(new MySQLDataType()));
    }

    #[Test]
    public function blobTypeHasNoLengthAndNoDefault(): void
    {
        $column = new Column('data', 'blob');

        self::assertFalse($column->canHasDefaultValue());
        self::assertSame('blob', $column->getTypeExpression(new MySQLDataType()));
        self::assertSame('blob', $column->getTypeExpression(new SQLiteDataType()));
        self::assertSame('bytea', new Column('data', 'bytea')->getTypeExpression(new PostgreSQLDataType()));
        self::assertSame('varbinary', new Column('data', 'varbinary')->getTypeExpression(new SQLServerDataType()));
        self::assertSame('blob', new Column('data', 'blob')->getTypeExpression(new OracleDataType()));
    }
}
