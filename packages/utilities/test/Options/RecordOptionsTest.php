<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Options;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Options\RecordOptions;

class RecordOptionsTest extends TestCase
{
    protected function createRecord(): RecordOptions
    {
        return new class () extends RecordOptions {
            public mixed $fooBar = null;

            public ?string $other = null;
        };
    }

    #[Test]
    public function offsetSetAndGetUseNormalizedKeys(): void
    {
        $record = $this->createRecord();

        $record['foo_bar'] = 'x';

        self::assertSame('x', $record->fooBar);
        self::assertSame('x', $record['foo_bar']);
        self::assertSame('x', $record['fooBar']);
    }

    #[Test]
    public function offsetExistsIsFalseForNullOrMissing(): void
    {
        $record = $this->createRecord();

        self::assertFalse(isset($record['other']));

        $record['other'] = 'v';

        self::assertTrue(isset($record['other']));
    }

    #[Test]
    public function offsetUnsetRemovesProperty(): void
    {
        $record = $this->createRecord();
        $record['other'] = 'v';

        unset($record['other']);

        self::assertFalse(isset($record['other']));
    }
}
