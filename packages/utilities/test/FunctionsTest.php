<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Test\Traits\BaseAssertionTrait;
use Windwalker\Utilities\Arr;

use function show;
use function Windwalker\clamp;
use function Windwalker\depth;
use function Windwalker\fread_all;

/**
 * Tests for the global PHP methods.
 */
class FunctionsTest extends TestCase
{
    use BaseAssertionTrait;

    #[Test]
    public function showPrintsValuesAndTruncatesAtMaxDepth(): void
    {
        $data = [
            'test',
            1,
            2,
            ['foo' => 'bar'],
            ['max' => ['level' => ['test' => ['this' => ['no' => 'show']]]]],
            depth(4),
        ];

        $expected = <<<OUT
[Value 1]
test

[Value 2]
1

[Value 3]
2

[Value 4]
Array
(
    [foo] => bar
)

[Value 5]
Array
(
    [max] => Array
        (
            [level] => Array
                (
                    [test] => Array
                        (
                            [this] => Array
                                (
                                    *MAX LEVEL*
                                )

                        )

                )

        )

)
OUT;

        Arr::$output = fopen('php://memory', 'wb+');
        show(...$data);
        rewind(Arr::$output);

        $c = stream_get_contents(Arr::$output);
        self::assertStringSafeEquals($expected, $c);

        fclose(Arr::$output);
    }

    #[Test]
    public function showPrintsNamedArgumentsWithDepthOption(): void
    {
        $expected = <<<OUT
[Value 1]
Hello

[foo]
bar

[yoo]
Array
(
    [A] => Array
        (
            [B] => Array
                (
                    [C] => Array
                        (
                            *MAX LEVEL*
                        )

                )

        )

)
OUT;

        Arr::$output = fopen('php://memory', 'wb+');
        show(
            'Hello',
            foo: 'bar',
            yoo: [
                'A' => [
                    'B' => [
                        'C' => [
                            'D' => [
                                'E' => 'F'
                            ]
                        ]
                    ]
                ]
            ],
            depth: depth(3)
        );
        rewind(Arr::$output);

        $c = stream_get_contents(Arr::$output);
        self::assertStringSafeEquals($expected, $c);

        fclose(Arr::$output);
    }

    #[Test]
    #[DataProvider('clampProvider')]
    public function clampLimitsNumberWithinMinAndMax(int|float $num, int|float|null $min, int|float|null $max, int|float $expected): void
    {
        self::assertEquals(
            $expected,
            clamp($num, $min, $max)
        );
    }

    public static function clampProvider(): array
    {
        return [
            [
                50,
                10,
                20,
                20
            ],
            [
                5,
                10,
                20,
                10
            ],
            [
                50,
                10,
                null,
                50
            ],
            [
                10,
                null,
                50,
                10
            ]
        ];
    }
}
