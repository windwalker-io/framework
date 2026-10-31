<?php

declare(strict_types=1);

namespace Windwalker\Promise\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\Promise\Promise;

use function Windwalker\Promise\asyncable;
use function Windwalker\Promise\is_thenable;
use function Windwalker\Promise\reject;
use function Windwalker\Promise\resolve;

class HelperFunctionsTest extends AbstractPromiseTestCase
{
    public static function thenableProvider(): array
    {
        return [
            'promise' => [new Promise(), true],
            'object with then' => [
                new class () {
                    public function then(): void {}
                },
                true,
            ],
            'plain object' => [new \stdClass(), false],
            'string' => ['then', false],
            'null' => [null, false],
        ];
    }

    #[Test]
    #[DataProvider('thenableProvider')]
    public function isThenableDetectsObjectsWithThenMethod(mixed $value, bool $expected): void
    {
        self::assertSame($expected, is_thenable($value));
    }

    #[Test]
    public function resolveCreatesFulfilledPromise(): void
    {
        self::assertSame('A', resolve('A')->wait());
    }

    #[Test]
    public function rejectCreatesRejectedPromise(): void
    {
        $e = new \Exception('no');

        $caught = reject($e)->catch(static fn($r) => $r);

        self::assertSame($e, $caught->wait());
    }

    #[Test]
    public function asyncableWrapsCallableAndPassesArguments(): void
    {
        $add = asyncable(static fn(int $a, int $b) => $a + $b);

        self::assertSame(5, $add(2, 3)->wait());
    }
}
