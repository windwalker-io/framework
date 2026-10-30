<?php

declare(strict_types=1);

namespace Windwalker\Data\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Data\Record;

class RecordTest extends TestCase
{
    #[Test]
    public function itWrapsInputAndCreatesImmutableUpdates(): void
    {
        $record = RecordFixture::wrap(['name' => 'Sakura', 'visits' => 3]);
        $updated = $record->with(name: 'Rose', visits: 4);

        self::assertSame('Sakura', $record->name);
        self::assertSame(3, $record->visits);
        self::assertSame('Rose', $updated->name);
        self::assertSame(4, $updated->visits);
        self::assertSame(['name' => 'Rose', 'visits' => 4], $updated->dump());
    }

    #[Test]
    public function itWrapsExistingRecordsAndSupportsNullableWrapping(): void
    {
        $record = RecordFixture::wrap(['name' => 'Sakura']);

        self::assertSame($record, RecordFixture::wrap($record));
        self::assertNull(RecordFixture::tryWrap(null));
        self::assertSame('Rose', RecordFixture::tryWrapWith(name: 'Rose')->name);
    }

    #[Test]
    public function itSerializesRecordsAsTheirDumpedValues(): void
    {
        $record = RecordFixture::wrap(['name' => 'Sakura', 'visits' => 3]);

        self::assertSame(
            ['name' => 'Sakura', 'visits' => 3],
            $record->jsonSerialize()
        );
        self::assertSame(
            '{"name":"Sakura","visits":3}',
            json_encode($record, JSON_THROW_ON_ERROR)
        );
    }
}

class RecordFixture extends Record
{
    public string $name = '';

    public int $visits = 0;

    public function __construct(string $name = '')
    {
        $this->name = $name;
    }
}
