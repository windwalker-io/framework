<?php

declare(strict_types=1);

namespace Windwalker\Data\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Data\Collection;
use Windwalker\Data\Format\FormatRegistry;
use Windwalker\Data\ReadonlyCloneableTrait;
use Windwalker\Stream\Stream;
use Windwalker\Utilities\Contract\DumpableInterface;

class ExtraBehaviorTest extends TestCase
{
    #[Test]
    public function readonlyCloneableTraitCreatesModifiedCopies(): void
    {
        $a = new CloneableFixture('a', 1);
        $b = $a->cloneWith(['name' => 'b']);

        self::assertNotSame($a, $b);
        self::assertSame('b', $b->name);
        self::assertSame(1, $b->count);
        self::assertSame('a', $a->name);
    }

    #[Test]
    public function registryLoadsStreamsAndMakesDumpableData(): void
    {
        $registry = new FormatRegistry();
        $stream = new Stream('php://memory', 'rb+');
        $stream->write('{"a":1}');
        $stream->rewind();

        self::assertSame(['a' => 1], $registry->load($stream));

        $dumpable = new class () implements DumpableInterface {
            public function dump(bool $recursive = false, bool $onlyDumpable = false): array
            {
                return ['d' => 1];
            }
        };

        self::assertSame(['d' => 1], FormatRegistry::makeDumpable($dumpable));
        self::assertSame(['t' => 2], FormatRegistry::makeDumpable(new \ArrayIterator(['t' => 2])));
        self::assertSame(['p' => 3], FormatRegistry::makeDumpable(new CloneableFixtureProps()));
    }

    #[Test]
    public function collectionLoadsStreamsFilesAndConvertsToStringObject(): void
    {
        $stream = new Stream('php://memory', 'rb+');
        $stream->write('{"a":1}');
        $stream->rewind();

        self::assertSame(['a' => 1], Collection::from($stream)->dump());

        $file = new \SplFileInfo(__DIR__ . '/fixtures/flower.json');

        self::assertSame(
            json_decode(file_get_contents($file->getPathname()), true),
            Collection::from($file)->dump()
        );
        self::assertSame('{"a":1}', (string) new Collection(['a' => 1])->toStringObject('json'));
    }

    #[Test]
    public function mapProxyRejectsMethodsThatDoNotReturnCollections(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Collection([['score' => 1]])->mapProxy('score')->sum();
    }
}

class CloneableFixture
{
    use ReadonlyCloneableTrait;

    public function __construct(public readonly string $name, public readonly int $count) {}
}

class CloneableFixtureProps
{
    public int $p = 3;
}
