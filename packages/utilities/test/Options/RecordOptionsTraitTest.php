<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Options;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Test\Stub\Options\StubRecordOptions;

class RecordOptionsTraitTest extends TestCase
{
    #[Test]
    public function wrapReturnsSameInstanceOrCloneForSameType(): void
    {
        $options = new StubRecordOptions();

        self::assertSame($options, StubRecordOptions::wrap($options));
        self::assertNotSame($options, StubRecordOptions::wrap($options, true));
        self::assertEquals($options, StubRecordOptions::wrap($options, true));
    }

    #[Test]
    public function wrapSplitsConstructorArgumentsFromOtherValues(): void
    {
        $options = StubRecordOptions::wrap(['name' => 'Sakura', 'plain' => 'x', 'foo_bar' => 'y']);

        self::assertSame('Sakura', $options->name);
        self::assertSame('x', $options->plain);
        self::assertSame('y', $options->fooBar);
    }

    #[Test]
    public function wrapTreatsNullAsEmptyValues(): void
    {
        self::assertSame('default', StubRecordOptions::wrap(null)->name);
    }

    #[Test]
    public function wrapWithAlwaysClones(): void
    {
        $options = new StubRecordOptions();

        self::assertNotSame($options, StubRecordOptions::wrapWith($options));
    }

    #[Test]
    public function tryWrapReturnsNullForNull(): void
    {
        self::assertNull(StubRecordOptions::tryWrap(null));
        self::assertSame('a', StubRecordOptions::tryWrap(['name' => 'a'])->name);
    }

    #[Test]
    public function mergeOverwritesValuesAndNormalizesSnakeCaseKeys(): void
    {
        $options = new StubRecordOptions();

        self::assertSame($options, $options->merge(['foo_bar' => 'z', 'plain' => 1]));
        self::assertSame('z', $options->fooBar);
        self::assertSame(1, $options->plain);
    }

    #[Test]
    public function mergeIgnoresNullsOnlyWhenRequested(): void
    {
        $options = new StubRecordOptions('keep');

        $options->merge(['name' => null], false, true);
        self::assertSame('keep', $options->name);

        $options->plain = 'x';
        $options->merge(['plain' => null]);
        self::assertNull($options->plain);
    }

    #[Test]
    public function recursiveMergeMergesArraysAndNestedRecords(): void
    {
        $options = new StubRecordOptions();
        $options->list = ['a' => 1, 'b' => ['x' => 1]];
        $options->child = new StubRecordOptions('child');

        $options->merge(['list' => ['b' => ['y' => 2]], 'child' => ['plain' => 'p']], true);

        self::assertEquals(['a' => 1, 'b' => ['x' => 1, 'y' => 2]], $options->list);
        self::assertSame('child', $options->child->name);
        self::assertSame('p', $options->child->plain);
    }

    #[Test]
    public function nonRecursiveMergeReplacesArrays(): void
    {
        $options = new StubRecordOptions();
        $options->list = ['a' => 1];

        $options->merge(['list' => ['b' => 2]]);

        self::assertSame(['b' => 2], $options->list);
    }

    #[Test]
    public function withMergeDoesNotMutateOriginal(): void
    {
        $options = new StubRecordOptions();

        $new = $options->withMerge(['plain' => 'x']);

        self::assertNotSame($options, $new);
        self::assertNull($options->plain);
        self::assertSame('x', $new->plain);
    }

    #[Test]
    public function withMergesNamedArguments(): void
    {
        $options = new StubRecordOptions();

        $new = $options->with(plain: 'x', fooBar: 'y');

        self::assertNull($options->plain);
        self::assertSame('x', $new->plain);
        self::assertSame('y', $new->fooBar);
    }

    #[Test]
    public function defaultsFillsOnlyUnsetValuesInPlace(): void
    {
        $options = new StubRecordOptions();
        $options->plain = 'mine';

        $result = $options->defaults(['plain' => 'def', 'fooBar' => 'def']);

        self::assertSame($options, $result);
        self::assertSame('mine', $options->plain);
        self::assertSame('def', $options->fooBar);
    }

    #[Test]
    public function withDefaultsKeepsOriginalUntouched(): void
    {
        $options = new StubRecordOptions();
        $options->plain = 'mine';

        $new = $options->withDefaults(['plain' => 'def', 'fooBar' => 'def']);

        self::assertNotSame($options, $new);
        self::assertSame('mine', $new->plain);
        self::assertSame('def', $new->fooBar);
        self::assertNull($options->fooBar);
    }

    #[Test]
    public function withDefaultsRecursivelyMergesArrays(): void
    {
        $options = new StubRecordOptions();
        $options->list = ['a' => 1];

        $new = $options->withDefaults(['list' => ['a' => 0, 'b' => 2]], true);

        self::assertSame(['a' => 1, 'b' => 2], $new->list);
    }
}
