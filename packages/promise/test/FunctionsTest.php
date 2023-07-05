<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Promise\Test;

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

    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        static::useScheduler(new DeferredScheduler());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Ensure async events ran
        $this->nextTick();
    }

    public function testAsync()
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

    public function testAwait()
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

                show($v);
            }
        );

        self::assertEquals('Sakura', $this->values['v1']);
        self::assertEquals('Sunflower', $this->values['v2']);
        // self::assertEquals('Lilium', $this->values['v3']);
    }

    /**
     * testCoroutine
     *
     * @return  void
     *
     * @throws Throwable
     */
    public function testCoroutine(): void
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

    public function testCoroutineInSwoole(): void
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

    /**
     * testCoroutineable
     *
     * @return  void
     *
     * @throws Throwable
     */
    public function testCoroutineable(): void
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
