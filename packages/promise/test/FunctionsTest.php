<?php

declare(strict_types=1);

namespace Windwalker\Promise\Test;

use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\Test;
use Swoole\Coroutine\Channel;
use Throwable;
use Windwalker\Promise\ExtendedPromiseInterface;
use Windwalker\Promise\Promise;
use Windwalker\Promise\Scheduler\DeferredScheduler;
use Windwalker\Promise\Scheduler\ImmediateScheduler;
use Windwalker\Promise\Scheduler\SwooleScheduler;
use Windwalker\Test\Traits\Reactor\SwooleTestTrait;

use function Windwalker\Promise\async;
use function Windwalker\Promise\await;
use function Windwalker\Promise\coroutine;
use function Windwalker\Promise\coroutineable;
use function Windwalker\run;

/**
 * The FunctionsTest class.
 */
class FunctionsTest extends AbstractPromiseTestCase
{
    use SwooleTestTrait;

    #[BeforeClass]
    public static function useDeferredScheduler(): void
    {
        static::useScheduler(new DeferredScheduler());
    }

    protected function flushAsyncEvents(): void
    {
        // Ensure async events ran
        $this->nextTick();
    }

    #[Test]
    public function asyncRunsCallbackAndWaitReturnsItsResult()
    {
        static::skipIfSwooleNotInstalled();

        static::useScheduler(new SwooleScheduler());

        go(
            function () {
                $p = async(
                    function () {
                        $this->values['v1'] = 'Flower';

                        return 'Sakura';
                    }
                );

                self::assertArrayNotHasKey('v1', $this->values);

                self::assertEquals('Sakura', $p->wait());
            }
        );
    }

    #[Test]
    public function awaitReturnsResolvedValuesInsideAsyncCallbacks()
    {
        static::skipIfSwooleNotInstalled();

        static::useScheduler(new SwooleScheduler());

        run(
            function () {
                $v = await($this->runAsync('Rose'));

                self::assertEquals('Rose', $v);

                $p = async(
                    function () {
                        $this->values['v1'] = await($this->runAsync('Sakura'));
                        $this->values['v2'] = await($this->runAsync('Sunflower'));

                        return 'Lilium';
                    }
                )
                    ->then(
                        function ($v) {
                            $this->values['v3'] = $v;

                            return $v;
                        }
                    );

                $v = $p->wait();
            }
        );

        self::assertEquals('Sakura', $this->values['v1']);
        self::assertEquals('Sunflower', $this->values['v2']);
        self::assertEquals('Lilium', $this->values['v3']);
    }

    #[Test]
    public function coroutineYieldsPromisesAndReturnsCombinedResult(): void
    {
        static::useScheduler(new ImmediateScheduler());

        $v = coroutine(
            function () {
                $v1 = yield $this->runAsync('Sakura');
                $v2 = yield $this->runAsync('Rose');

                return $v1 . ' ' . $v2;
            }
        )
            ->wait();

        self::assertEquals('Sakura Rose', $v);
    }

    #[Test]
    public function coroutineYieldsPromisesInsideSwooleCoroutine(): void
    {
        static::skipIfSwooleNotInstalled();

        static::useScheduler(new SwooleScheduler());

        go(
            function () {
                $v = coroutine(
                    function () {
                        $v1 = yield $this->runAsync('Sakura');
                        $v2 = yield $this->runAsync('Rose');

                        return $v1 . ' ' . $v2;
                    }
                )->wait();

                self::assertEquals('Sakura Rose', $v);
            }
        );
    }

    #[Test]
    public function coroutineableReturnsCallableThatPassesArguments(): void
    {
        static::useScheduler(new ImmediateScheduler());

        $c = coroutineable(
            function ($arg) {
                $v1 = yield $this->runAsync($arg);
                $v2 = yield $this->runAsync('Rose');

                return $v1 . ' ' . $v2;
            }
        );

        self::assertEquals('Sakura Rose', $c('Sakura')->wait());
    }

    /**
     * runAsync
     *
     * @param  mixed  $value
     *
     * @return  ExtendedPromiseInterface
     */
    protected function runAsync($value): ExtendedPromiseInterface
    {
        return async(
            static function () use ($value) {
                return $value;
            }
        );
    }
}
