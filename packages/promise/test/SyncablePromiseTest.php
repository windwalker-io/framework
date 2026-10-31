<?php

declare(strict_types=1);

namespace Windwalker\Promise\Test;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Promise\Enum\PromiseState;
use Windwalker\Promise\SyncablePromise;

class SyncablePromiseTest extends AbstractPromiseTestCase
{
    #[Test]
    public function thenRunsHandlersSynchronouslyOnSettledPromise(): void
    {
        $value = null;

        $p = SyncablePromise::resolve('Hello')
            ->then(
                function ($v) use (&$value) {
                    $value = $v;

                    return $v . ' World';
                }
            );

        self::assertSame('Hello', $value);
        self::assertSame(PromiseState::FULFILLED, $p->getState());
        self::assertSame('Hello World', $p->wait());
    }
}
