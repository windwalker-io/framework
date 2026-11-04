<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Platform\Type;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Database\Platform\Type\DataType;
use Windwalker\Database\Platform\Type\MySQLDataType;
use Windwalker\Database\Platform\Type\OracleDataType;
use Windwalker\Database\Platform\Type\PostgreSQLDataType;
use Windwalker\Database\Platform\Type\SQLServerDataType;
use Windwalker\Database\Schema\Ddl\Column;

class DataTypeTest extends TestCase
{
    #[Test]
    public function castForSaveUsesPhpTypeOfColumn(): void
    {
        $column = new Column('c', DataType::INTEGER);

        self::assertSame(5, DataType::castForSave('5', $column));
    }

    #[Test]
    public function unknownTypeFallsBackToStringAndNull(): void
    {
        self::assertSame('string', DataType::getPhpType('no_such_type'));
        self::assertNull(DataType::getLength('no_such_type'));
    }

    #[Test]
    public function postgresqlBooleanCastsToBitString(): void
    {
        $column = new Column('c', PostgreSQLDataType::BOOLEAN);

        self::assertSame('1', PostgreSQLDataType::castForSave(true, $column));
        self::assertSame('0', PostgreSQLDataType::castForSave(0, $column));
    }

    #[Test]
    public function postgresqlOtherTypesUseParentCast(): void
    {
        $column = new Column('c', PostgreSQLDataType::INTEGER);

        self::assertSame(7, PostgreSQLDataType::castForSave('7', $column));
    }

    #[Test]
    public function blobTypeHasNoLengthAndNoDefaultValue(): void
    {
        self::assertNull(DataType::getLength(DataType::BLOB));
        self::assertFalse(DataType::getDefaultValue(DataType::BLOB));
        self::assertSame('string', DataType::getPhpType(DataType::BLOB));
        self::assertTrue(MySQLDataType::isNoLength(DataType::BLOB));
    }

    #[Test]
    public function blobTypeMapsToPlatformSpecificType(): void
    {
        self::assertSame(DataType::BLOB, MySQLDataType::getAvailableType(DataType::BLOB));
        self::assertSame(PostgreSQLDataType::BYTEA, PostgreSQLDataType::getAvailableType(DataType::BLOB));
        self::assertSame(SQLServerDataType::VARBINARY, SQLServerDataType::getAvailableType(DataType::BLOB));
        self::assertSame(DataType::BLOB, OracleDataType::getAvailableType(DataType::BLOB));
    }
}
