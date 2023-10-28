<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\Test\Traits\BaseAssertionTrait;
use Windwalker\Utilities\Arr;

use function show;
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

        self::assertStringSafeEquals($expected, stream_get_contents(Arr::$output));

        fclose(Arr::$output);
    }
}
