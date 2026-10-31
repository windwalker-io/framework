<?php

declare(strict_types=1);

namespace Windwalker\Utilities;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * The ArrConverterTest class.
 */
class ArrConverterTraitTest extends TestCase
{
    #[Test]
    public function flipMatrixMapsInnerValuesToOuterKeys(): void
    {
        $src = [
            'A' => [
                'id1',
                'Julius Caesar',
            ],
            'B' => [
                'id2',
                'title' => 'Macbeth',
            ],
            'C' => [
                'id3',
                'title' => 'Othello',
            ],
            'D' => [
                'id4',
                'title' => 'Hamlet',
            ],
        ];
        $expected = [
            'id1' => 'A',
            'Julius Caesar' => 'A',
            'id2' => 'B',
            'Macbeth' => 'B',
            'id3' => 'C',
            'Othello' => 'C',
            'id4' => 'D',
            'Hamlet' => 'D',
        ];

        self::assertEquals($expected, Arr::flipMatrix($src));
    }

    #[Test]
    #[DataProvider('groupByPathProvider')]
    public function groupByPathGroupsItemsByKeyPath($source, $key, $expected, int $type, ?string $delimiter = ''): void
    {
        self::assertEquals($expected, Arr::groupByPath($source, $key, $type, (string) $delimiter));
    }

    public static function groupByPathProvider(): array
    {
        return [
            'A scalar array' => [
                // Source
                [
                    1 => 'a',
                    2 => 'b',
                    3 => 'b',
                    4 => 'c',
                    5 => 'a',
                    6 => 'a',
                ],
                // Index
                null,
                // Expected
                [
                    'a' => [1, 5, 6],
                    'b' => [2, 3],
                    'c' => 4,
                ],
                Arr::GROUP_TYPE_MIX,
            ],
            'A scalar array force child array' => [
                // Source
                [
                    1 => 'a',
                    2 => 'b',
                    3 => 'b',
                    4 => 'c',
                    5 => 'a',
                    6 => 'a',
                ],
                // Index
                null,
                // Expected
                [
                    'a' => [1, 5, 6],
                    'b' => [2, 3],
                    'c' => [4],
                ],
                Arr::GROUP_TYPE_ARRAY,
            ],
            'An array of associative arrays' => [
                // Source
                [
                    1 => ['id' => 41, 'title' => 'boo'],
                    2 => ['id' => 42, 'title' => 'boo'],
                    3 => ['title' => 'boo'],
                    4 => ['id' => 42, 'title' => 'boo'],
                    5 => ['id' => 43, 'title' => 'boo'],
                ],
                // Index
                'id',
                // Expected
                [
                    41 => ['id' => 41, 'title' => 'boo'],
                    42 => [
                        ['id' => 42, 'title' => 'boo'],
                        ['id' => 42, 'title' => 'boo'],
                    ],
                    43 => ['id' => 43, 'title' => 'boo'],
                ],
                Arr::GROUP_TYPE_MIX,
            ],
            'An array of associative arrays but use key by' => [
                // Source
                [
                    1 => ['id' => 41, 'title' => 'boo'],
                    2 => ['id' => 42, 'title' => 'boo1'],
                    3 => ['title' => 'boo'],
                    4 => ['id' => 42, 'title' => 'boo2'],
                    5 => ['id' => 43, 'title' => 'boo'],
                ],
                // Index
                'id',
                // Expected
                [
                    41 => ['id' => 41, 'title' => 'boo'],
                    42 => ['id' => 42, 'title' => 'boo1'],
                    43 => ['id' => 43, 'title' => 'boo'],
                ],
                Arr::GROUP_TYPE_KEY_BY,
            ],
            'An array of associative arrays but use key by and override same key' => [
                // Source
                [
                    1 => ['id' => 41, 'title' => 'boo'],
                    2 => ['id' => 42, 'title' => 'boo1'],
                    3 => ['title' => 'boo'],
                    4 => ['id' => 42, 'title' => 'boo2'],
                    5 => ['id' => 43, 'title' => 'boo'],
                ],
                // Index
                'id',
                // Expected
                [
                    41 => ['id' => 41, 'title' => 'boo'],
                    42 => ['id' => 42, 'title' => 'boo2'],
                    43 => ['id' => 43, 'title' => 'boo'],
                ],
                Arr::GROUP_TYPE_KEY_BY | Arr::SAME_KEY_OVERRIDE,
            ],
            'An array of associative arrays force child array' => [
                // Source
                [
                    1 => ['id' => 41, 'title' => 'boo'],
                    2 => ['id' => 42, 'title' => 'boo'],
                    3 => ['title' => 'boo'],
                    4 => ['id' => 42, 'title' => 'boo'],
                    5 => ['id' => 43, 'title' => 'boo'],
                ],
                // Index
                'id',
                // Expected
                [
                    41 => [['id' => 41, 'title' => 'boo']],
                    42 => [
                        ['id' => 42, 'title' => 'boo'],
                        ['id' => 42, 'title' => 'boo'],
                    ],
                    43 => [['id' => 43, 'title' => 'boo']],
                ],
                Arr::GROUP_TYPE_ARRAY,
            ],
            'An array of objects' => [
                // Source
                [
                    1 => (object) ['id' => 41, 'title' => 'boo'],
                    2 => (object) ['id' => 42, 'title' => 'boo'],
                    3 => (object) ['title' => 'qoo'],
                    4 => (object) ['id' => 42, 'title' => 'boo'],
                    5 => (object) ['id' => 43, 'title' => 'boo'],
                ],
                // Index
                'id',
                // Expected
                [
                    41 => (object) ['id' => 41, 'title' => 'boo'],
                    42 => [
                        (object) ['id' => 42, 'title' => 'boo'],
                        (object) ['id' => 42, 'title' => 'boo'],
                    ],
                    43 => (object) ['id' => 43, 'title' => 'boo'],
                ],
                Arr::GROUP_TYPE_MIX,
            ],
            'group by path' => [
                // Source
                [
                    1 => (object) ['id' => 41, 'title' => 'boo', 'country' => ['id' => 55]],
                    2 => (object) ['id' => 42, 'title' => 'boo', 'country' => ['id' => 56]],
                    3 => (object) ['title' => 'qoo'],
                    4 => (object) ['id' => 42, 'title' => 'boo', 'country' => ['id' => 56]],
                    5 => (object) ['id' => 43, 'title' => 'boo', 'country' => ['id' => 57]],
                ],
                // Index
                'country.id',
                // Expected
                [
                    55 => (object) ['id' => 41, 'title' => 'boo', 'country' => ['id' => 55]],
                    56 => [
                        (object) ['id' => 42, 'title' => 'boo', 'country' => ['id' => 56]],
                        (object) ['id' => 42, 'title' => 'boo', 'country' => ['id' => 56]],
                    ],
                    57 => (object) ['id' => 43, 'title' => 'boo', 'country' => ['id' => 57]],
                ],
                Arr::GROUP_TYPE_MIX,
                '.'
            ],
        ];
    }

    #[Test]
    public function transposeSwapsRowsAndColumns(): void
    {
        $src = [
            [
                'value' => 'aaa',
                'text' => 'aaa',
            ],
            [
                'value' => 'bbb',
                'text' => 'bbb',
            ],
            [
                'value' => 'ccc',
                'text' => 'ccc',
            ],
        ];

        $expected = [
            'value' => [
                0 => 'aaa',
                1 => 'bbb',
                2 => 'ccc',
            ],
            'text' => [
                0 => 'aaa',
                1 => 'bbb',
                2 => 'ccc',
            ],
        ];

        self::assertEquals($expected, Arr::transpose($src));
    }

    #[Test]
    public function groupPrefixExtractsPrefixedKeysAndKeepsOthers(): void
    {
        $src = [
            'id' => 123,
            'title' => 'Hello',
            'params_foo' => 'Foo',
            'params_bar' => 'Bar',
            'params_yoo' => 'Yoo',
        ];

        $exp = [
            'foo' => 'Foo',
            'bar' => 'Bar',
            'yoo' => 'Yoo',
        ];

        self::assertEquals($exp, Arr::groupPrefix($src, 'params_'));
        self::assertEquals($exp, Arr::groupPrefix($src, 'params_', true));
        self::assertEquals(
            [
                'id' => 123,
                'title' => 'Hello',
            ],
            $src
        );
    }

    #[Test]
    public function extractPrefixAddsPrefixToKeys(): void
    {
        $item = [
            'id' => 123,
            'title' => 'Hello',
        ];

        $src = [
            'foo' => 'Foo',
            'bar' => 'Bar',
            'yoo' => 'Yoo',
        ];

        self::assertEquals(
            [
                'params_foo' => 'Foo',
                'params_bar' => 'Bar',
                'params_yoo' => 'Yoo',
            ],
            Arr::extractPrefix($src, 'params_')
        );
    }

    #[Test]
    public function extractPrefixMergesIntoGivenItem(): void
    {
        $item = [
            'id' => 123,
            'title' => 'Hello',
        ];

        $src = [
            'foo' => 'Foo',
            'bar' => 'Bar',
            'yoo' => 'Yoo',
        ];

        self::assertEquals(
            [
                'id' => 123,
                'title' => 'Hello',
                'params_foo' => 'Foo',
                'params_bar' => 'Bar',
                'params_yoo' => 'Yoo',
            ],
            Arr::extractPrefix($src, 'params_', $item)
        );
    }

    #[Test]
    public function mapWithKeysBuildsKeysFromCallback(): void
    {
        $src = [
            'A' => [
                'name' => 'Captain America',
                'id' => 1,
            ],
            'B' => [
                'name' => 'Luke Cage',
                'id' => 2,
            ],
            'C' => [
                'name' => 'Thor',
                'id' => 3,
            ],
        ];

        $expected = [
            1 => 'Captain America',
            2 => 'Luke Cage',
            3 => 'Thor',
        ];

        // Test return array
        self::assertEquals(
            $expected,
            Arr::mapWithKeys(
                $src,
                static function (array $item) {
                    return [$item['id'] => $item['name']];
                }
            )
        );

        // Test yield
        self::assertEquals(
            $expected,
            Arr::mapWithKeys(
                $src,
                static function (array $item) {
                    yield $item['id'] => $item['name'];
                }
            )
        );

        // Test multiple yield
        self::assertEquals(
            [
                'A' => 'Captain America',
                'a' => 'Captain America',
                'B' => 'Luke Cage',
                'b' => 'Luke Cage',
                'C' => 'Thor',
                'c' => 'Thor',
            ],
            Arr::mapWithKeys(
                $src,
                static function (array $item, $k) {
                    yield $k => $item['name'];
                    yield strtolower($k) => $item['name'];
                }
            )
        );

        // Test yield without key
        self::assertEquals(
            expected: [
                'Captain America',
                'Captain America',
                'Luke Cage',
                'Luke Cage',
                'Thor',
                'Thor',
            ],
            actual: Arr::mapWithKeys(
                $src,
                static function (array $item, $k) {
                    yield null => $item['name'];
                    yield null => $item['name'];
                }
            )
        );
    }

    #[Test]
    public static function mapGeneratorCollectsYieldedKeysAndValues(): void
    {
        $gen = static function () {
            yield 1 => 'A';
            yield 2 => 'B';
            yield 3 => 'C';
        };

        $expected = [
            1 => 'A',
            2 => 'B',
            3 => 'C',
        ];

        self::assertEquals(
            $expected,
            Arr::mapGenerator($gen)
        );
    }

    /**
     * @see  Arr::crossJoin()
     */
    #[Test]
    public function crossJoinCombinesTwoArrays(): void
    {
        $a = Arr::crossJoin([1, 2], ['a', 'b']);

        self::assertEquals(
            [
                [1, 'a'],
                [1, 'b'],
                [2, 'a'],
                [2, 'b'],
            ],
            $a
        );
    }

    #[Test]
    public function crossJoinCombinesThreeArrays(): void
    {
        $b = Arr::crossJoin([1, 2], ['a', 'b'], ['I', 'II']);

        self::assertEquals(
            [
                [1, 'a', 'I'],
                [1, 'a', 'II'],
                [1, 'b', 'I'],
                [1, 'b', 'II'],
                [2, 'a', 'I'],
                [2, 'a', 'II'],
                [2, 'b', 'I'],
                [2, 'b', 'II'],
            ],
            $b
        );
    }

    #[Test]
    public function groupDispatchesByPathOrCallback(): void
    {
        $src = [
            ['t' => 'a', 'v' => 1],
            ['t' => 'b', 'v' => 2],
            ['t' => 'a', 'v' => 3],
        ];

        $byPath = Arr::group($src, 't');
        $byCallback = Arr::group($src, fn(array $item) => $item['t']);

        self::assertSame([1, 3], array_column($byPath['a'], 'v'));
        self::assertEquals($byPath, $byCallback);
        self::assertSame(['a', 'b'], array_keys(Arr::group($src, 't', Arr::GROUP_TYPE_MIX)));
        self::assertSame(['x'], array_keys(Arr::group(['x'], null)));
    }

    #[Test]
    #[DataProvider('mapWithKeysProvider')]
    public function mapWithKeysAppliesGroupingTypeToDuplicateKeys(int $type, array $expected): void
    {
        $result = Arr::mapWithKeys(
            [1, 2, 3, 4],
            fn(int $v) => [$v % 2 ? 'odd' : 'even' => $v],
            $type
        );

        self::assertSame($expected, $result);
    }

    public static function mapWithKeysProvider(): array
    {
        return [
            'key by' => [Arr::GROUP_TYPE_KEY_BY, ['odd' => 3, 'even' => 4]],
            'array' => [Arr::GROUP_TYPE_ARRAY, ['odd' => [1, 3], 'even' => [2, 4]]],
            'mix' => [Arr::GROUP_TYPE_MIX, ['odd' => [1, 3], 'even' => [2, 4]]],
        ];
    }

    #[Test]
    public function mapWithKeysAppendsNullKeysAndAcceptsGenerators(): void
    {
        $result = Arr::mapWithKeys(
            ['a', 'b'],
            function (string $v) {
                yield null => $v;
                yield 'k' => $v;
            }
        );

        self::assertSame(['a', 'k' => 'b', 'b'], [$result[0], 'k' => $result['k'], $result[1]]);
    }

    #[Test]
    public function mapWithKeysMixKeepsThirdAndLaterValuesInArray(): void
    {
        $result = Arr::mapWithKeys(
            [1, 2, 3],
            fn(int $v) => ['k' => $v],
            Arr::GROUP_TYPE_MIX
        );

        self::assertSame(['k' => [1, 2, 3]], $result);
    }

    #[Test]
    public function mapWithKeysRejectsNonIterableHandlerResult(): void
    {
        $this->expectException(\TypeError::class);

        Arr::mapWithKeys([1], fn() => 'nope');
    }
}
