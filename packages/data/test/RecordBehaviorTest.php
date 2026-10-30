<?php

declare(strict_types=1);

namespace Windwalker\Data\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Data\Record;
use Windwalker\ORM\Attributes\JsonNoSerialize;
use Windwalker\ORM\Attributes\JsonSerializer;

class RecordBehaviorTest extends TestCase
{
    #[Test]
    public function itWrapsObjectsAndConstructorArguments(): void
    {
        $record = RecordBehaviorFixture::wrap((object) ['name' => 'Sakura', 'tags' => ['a'], 'extra' => 1]);

        self::assertSame('Sakura', $record->name);
        self::assertSame(['a'], $record->tags);
        self::assertSame(1, $record->extra);
        self::assertSame([], RecordBehaviorFixture::wrap(null)->tags);
    }

    #[Test]
    public function itClonesExistingRecordsWhenRequested(): void
    {
        $record = RecordBehaviorFixture::wrap(['name' => 'Sakura']);
        $clone = RecordBehaviorFixture::wrap($record, true);

        self::assertNotSame($record, $clone);
        self::assertSame('Sakura', $clone->name);
        self::assertSame('Rose', RecordBehaviorFixture::wrapWith(name: 'Rose')->name);
    }

    #[Test]
    public function itFillsValues(): void
    {
        $record = new RecordBehaviorFixture();
        $record->fill(['name' => 'Rose', 'tags' => ['x']]);

        self::assertSame('Rose', $record->name);
        self::assertSame(['x'], $record->tags);
    }

    #[Test]
    public function itMergesAndSkipsProtectedAndReadonlyProperties(): void
    {
        $record = RecordBehaviorFixture::wrap(['name' => 'Sakura']);
        $record->merge(['name' => 'Rose', 'locked' => 'x', 'readonly' => 'y']);

        self::assertSame('Rose', $record->name);
        self::assertSame('fixed', $record->readonly);
        self::assertSame('Rose', $record->name);
    }

    #[Test]
    public function itMergesRecursivelyAndIgnoresNulls(): void
    {
        $record = RecordBehaviorFixture::wrap(['name' => 'Sakura', 'tags' => ['a' => 1]]);
        $record->child = RecordBehaviorFixture::wrap(['name' => 'Child', 'tags' => ['x' => 1]]);

        $merged = $record->withMerge(
            ['tags' => ['b' => 2], 'child' => ['tags' => ['y' => 2]], 'name' => null],
            true,
            true
        );

        self::assertEquals(['a' => 1, 'b' => 2], $merged->tags);
        self::assertEquals(['x' => 1, 'y' => 2], $merged->child->tags);
        self::assertSame('Sakura', $merged->name);
        self::assertSame(['a' => 1], $record->tags);

        $merged = $record->withMerge(['name' => null]);
        self::assertNull($merged->name);
    }

    #[Test]
    public function itAppliesDefaultsWithoutOverridingExistingValues(): void
    {
        $record = RecordBehaviorFixture::wrap(['name' => null, 'tags' => ['a' => 1]]);

        $new = $record->withDefaults(['name' => 'Default', 'tags' => ['b' => 2]], true);

        self::assertSame('Default', $new->name);
        self::assertEquals(['b' => 2, 'a' => 1], $new->tags);
        self::assertNull($record->name);

        $record->defaults(['name' => 'Default']);
        self::assertSame('Default', $record->name);
    }

    #[Test]
    public function itDumpsRecursivelyAndConvertsClasses(): void
    {
        $record = RecordBehaviorFixture::wrap(['name' => 'Sakura']);
        $record->child = RecordBehaviorFixture::wrap(['name' => 'Child']);

        self::assertSame('Child', $record->dump(true)['child']['name']);
        self::assertSame($record, $record->as(RecordBehaviorFixture::class));

        $other = $record->as(Record::class);
        self::assertInstanceOf(Record::class, $other);
        self::assertSame('Sakura', $other->name);
    }

    #[Test]
    public function itAppliesJsonSerializationAttributes(): void
    {
        $record = RecordBehaviorFixture::wrap(['name' => 'Sakura']);
        $record->secret = 'hidden';
        $record->upper = 'abc';

        $data = $record->jsonSerialize();

        self::assertArrayNotHasKey('secret', $data);
        self::assertSame('ABC', $data['upper']);
    }
}

class RecordBehaviorFixture extends Record
{
    public ?string $name = '';

    public array $tags = [];

    public ?self $child = null;

    public readonly string $readonly;

    protected string $locked = 'locked';

    #[JsonNoSerialize]
    public string $secret = '';

    #[JsonSerializer('strtoupper')]
    public string $upper = '';

    public function __construct(?string $name = '')
    {
        $this->name = $name;
        $this->readonly = 'fixed';
    }
}
