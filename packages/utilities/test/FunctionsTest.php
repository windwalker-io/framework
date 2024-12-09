<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Windwalker\Test\Traits\BaseAssertionTrait;
use Windwalker\Utilities\Arr;

use function show;
use function Windwalker\clamp;
use function Windwalker\fread_all;

/**
 * Tests for the global PHP methods.
 *
 * @since  2.0
 */
class FunctionsTest extends TestCase
{
    use BaseAssertionTrait;

    public function testShow(): void
    {
        $data = [
            'test',
            1,
            2,
            ['foo' => 'bar'],
            ['max' => ['level' => ['test' => ['this' => ['no' => 'show']]]]],
            4,
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

    #[DataProvider('clampProvider')]
    public function testClamp(int|float $num, int|float|null $min, int|float|null $max, int|float $expected): void
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
