<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Promise\Test;

use Exception;
use ReflectionException;
use Windwalker\Promise\Enum\PromiseState;
use Windwalker\Promise\Exception\UncaughtException;
use Windwalker\Promise\Promise;
use Windwalker\Promise\Scheduler\TaskQueue;
use Windwalker\Test\Traits\TestAccessorTrait;
use Windwalker\Utilities\Reflection\ReflectAccessor;

use function Windwalker\nope;
use function Windwalker\Promise\await;

/**
 * The SwoolePromiseTest class.
 *
 * @since  __DEPLOY_VERSION__
 */
class PromiseThenTest extends AbstractPromiseTestCase
{
    use TestAccessorTrait;

    /**
     * @throws ReflectionException
     * @see  Promise::then
     */
    public function testThenWithPending(): void
    {
        $p = new Promise(nope());

        $p2 = $p->then($rsv1 = nope(), $rej1 = nope());
        $p->then($rsv2 = nope(), $rej2 = nope());

        $p3 = $p2->then($rsv3 = nope(), $rej3 = nope());

        self::assertNotSame($p2, $p);

        // Handlers
        $children = ReflectAccessor::getValue($p, 'children');

        self::assertSame($children[0][0], $p2);
        self::assertSame($children[0][1], $rsv1);
        self::assertSame($children[0][2], $rej1);
        self::assertSame($children[1][1], $rsv2);

        $children = ReflectAccessor::getValue($p2, 'children');

        self::assertSame($children[0][0], $p3);
        self::assertSame($children[0][1], $rsv3);
        self::assertSame($children[0][2], $rej3);
    }

    public function testResolvedThenWithFulfilledHandler(): void
    {
        $p = Promise::resolved(1)
            ->then(
                function ($v) {
                    return $v + 1;
                }
            );

        $v = $p->wait();

        self::assertEquals(PromiseState::FULFILLED, $p->getState());
        self::assertEquals(2, $v);
    }

    public function testResolvedThenWithRejectedHandler(): void
    {
        $p = Promise::resolved(1)
            ->then(
                null,
                function ($v) {
                    return $v + 1;
                }
            );

        $v = $p->wait();

        self::assertEquals(PromiseState::FULFILLED, $p->getState());
        self::assertEquals(1, $v);
    }

    public function testRejectedThenWithFulfilledHandler(): void
    {
        $this->expectException(UncaughtException::class);

        $p = Promise::rejected(1)
            ->then(
                function ($v) {
                    return $v + 1;
                },
            );

        $v = $p->wait();

        self::assertEquals(PromiseState::REJECTED, $p->getState());
        self::assertEquals(1, $v);
    }

    public function testRejectedThenWithRejectedHandler(): void
    {
        $p = Promise::rejected(1)
            ->then(
                null,
                function ($v) {
                    return $v + 1;
                }
            );

        $v = $p->wait();

        self::assertEquals(PromiseState::FULFILLED, $p->getState());
        self::assertEquals(2, $v);
    }

    public function testRejectedThenWithBoth(): void
    {
        $p = Promise::rejected(1)
            ->then(
                function ($v) {
                    return $v + 10;
                },
                function ($v) {
                    return $v + 1;
                }
            );

        $v = $p->wait();

        self::assertEquals(PromiseState::FULFILLED, $p->getState());
        self::assertEquals(2, $v);
    }

    public function testRejectedWithoutThen(): void
    {
        $this->expectException(UncaughtException::class);
        $this->expectExceptionMessage('1');

        $p = Promise::rejected('1');

        $p->wait();
    }

    public function testRejectedWithCatchAndThrowAgain(): void
    {
        $this->expectException(UncaughtException::class);
        $this->expectExceptionMessage('2');

        $p = Promise::rejected('1')
            ->catch(
                function ($reason) {
                    self::assertEquals('1', $reason);
                    $this->addToAssertionCount(1);

                    throw new \RuntimeException('2');
                }
            );

        try {
            $p->wait();
        } catch (UncaughtException $e) {
            $previous = $e->getPrevious();

            self::assertInstanceOf(\RuntimeException::class, $previous);
            self::assertEquals('2', $previous->getMessage());

            throw $e;
        }
    }

    public function testResolveRejected(): void
    {
        $this->expectException(UncaughtException::class);
        $this->expectExceptionMessage('1');

        $p = Promise::resolved(Promise::rejected('1'));

        try {
            $p->wait();
        } finally {
            self::assertEquals(PromiseState::REJECTED, $p->getState());
            $this->addToAssertionCount(1);
        }

        self::assertEquals(3, $this->numberOfAssertionsPerformed());
    }

    /**
     * @throws ReflectionException
     * @see  Promise::then
     */
    public function testThenAlreadyFulfilled(): void
    {
        $p = Promise::resolved(1);

        $p2 = $p
            ->then(
                function ($v) {
                    return ++$v;
                }
            )
            ->then(
                function ($v) {
                    return ++$v;
                }
            );

        // Test return new Promise
        $p3 = $p->then(
            function ($v) {
                return new Promise(
                    function ($resolve) {
                        $resolve('Hello');
                    }
                );
            }
        );

        $newValue = null;

        $p4 = $p3->then(
            function ($v2) use (&$newValue) {
                $newValue = $v2;
            }
        );

        $v = $p->wait();

        self::assertEquals(PromiseState::FULFILLED, $p->getState());
        self::assertEquals(1, $v);

        $v2 = $p2->wait();

        self::assertEquals(PromiseState::FULFILLED, $p3->getState());
        self::assertEquals(3, $v2);

        $v3 = $p3->wait();

        self::assertEquals('Hello', $v3);

        $v4 = $p4->wait();

        self::assertNull($v4);
        self::assertEquals('Hello', $newValue);
    }

    public function testThenAndResolveDeferred(): void
    {
        $p = new Promise(
            function () {
                $this->values['v0'] = 'Init';
            }
        );

        $p
            ->then(
                function ($v) {
                    $this->values['v1'] = $v;

                    return $v . ' World';
                },
                function ($r) {
                    $this->values['r1'] = $r;

                    return $r;
                }
            )
            ->then(
                function ($v) {
                    $this->values['v2'] = $v;

                    return $v;
                },
                function ($r) {
                    $this->values['r2'] = $r;

                    return $r;
                }
            );

        $p->resolve('Hello');

        $p->wait();

        self::assertEquals('Init', $this->values['v0']);
        self::assertEquals('Hello', $this->values['v1']);
        self::assertEquals('Hello World', $this->values['v2']);
        self::assertArrayNotHasKey('r1', $this->values);
        self::assertArrayNotHasKey('r2', $this->values);
    }

    /**
     * @throws ReflectionException
     * @see  Promise::then
     */
    public function testThenAlreadyRejected(): void
    {
        $p = Promise::rejected($this->values['e1'] = new Exception('Sakura'));

        $p2 = $p
            ->then(
                null,
                function (Exception $e) {
                    $this->values['e2'] = $e;

                    return 'New state';
                }
            )
            ->then(
                function ($v) {
                    $this->values['t1'] = $v;

                    return $v;
                },
                function ($r) {
                    $this->values['t2'] = $r;

                    return $r;
                }
            );

        // Test return new Promise
        $p3 = $p->then(
            null,
            function ($reason) {
                return new Promise(
                    function ($resolve) {
                        $resolve('Hello');
                    }
                );
            }
        );

        $newValue = null;

        $p4 = $p3->then(
            function ($v2) use (&$newValue) {
                $newValue = $v2;
            }
        );

        $p4->wait();

        self::assertEquals(PromiseState::REJECTED, $p->getState());
        self::assertSame($this->values['e1'], $this->values['e2']);
        self::assertEquals('New state', $this->values['t1']);
        self::assertArrayNotHasKey('t2', $this->values);
        self::assertEquals('Hello', $this->getValue($p3, 'value'));
        self::assertNull($this->getValue($p4, 'value'));
        self::assertEquals('Hello', $newValue);
    }

    public function testThenAndRejectDeferred()
    {
        $p = new Promise(
            function () {
                $this->values['v0'] = 'Init';
            }
        );

        $p2 = $p->then(
            function ($v) {
                $this->values['v1'] = $v;

                return $v;
            },
            function (Exception $r) {
                $this->values['r1'] = $r;

                return $r->getMessage() . ' World';
            }
        );

        $p3 = $p2->then(
            function ($v) {
                $this->values['v2'] = $v;

                return $v;
            },
            function ($r) {
                $this->values['r2'] = $r;

                return $r;
            }
        );

        $p->reject($e = new Exception('Hello'));
        $v3 = $p3->wait();

        self::assertSame($e, $this->values['r1']);
        self::assertEquals('Hello World', $this->values['v2']);
        self::assertArrayNotHasKey('v1', $this->values);
        self::assertArrayNotHasKey('r2', $this->values);

        self::assertEquals(PromiseState::REJECTED, $p->getState());
        self::assertEquals(PromiseState::FULFILLED, $p2->getState());
        self::assertEquals(PromiseState::FULFILLED, $p3->getState());
        self::assertEquals('Hello World', $v3);
    }

    public function testThenWithNope(): void
    {
        $p1 = Promise::create();
        // Give no functions to then(), will just return same state Promise and same values.
        $p2 = $p1->then()
            ->then(
                function ($v) {
                    $this->values['v1'] = $v;

                    throw new Exception('Sakura');
                }
            )
            ->then()
            ->then(
                null,
                function ($r) {
                    $this->values['r1'] = $r;

                    return 'Olive';
                }
            )
            // If onRejected called, back to fulfilled
            ->then(
                function ($v) {
                    $this->values['v2'] = $v;
                }
            );

        $p1->resolve('Rose');

        $p2->wait();

        self::assertEquals('Rose', $this->values['v1']);
        self::assertEquals('Sakura', $this->values['r1']->getMessage());
        self::assertEquals('Olive', $this->values['v2']);
    }

    /**
     * @see  PromiseThen::then
     */
    public function testThenWithRejectedPromise(): void
    {
        $p = Promise::create(
            function ($re) {
                $re('Hello');
            }
        )
            ->then(
                function () {
                    $this->values['p2'] = $p2 = new Promise(
                        function ($re2, $rj2) {
                            $rj2('Error');
                        }
                    );

                    $p2->then(
                        nope(),
                        function ($reason) {
                            $this->values['r1'] = $reason;
                        }
                    );

                    return $p2;
                }
            )
            ->then(
                function ($v) {
                    $this->values['v1'] = $v;
                },
                function ($r2) {
                    $this->values['r2'] = $r2;
                }
            );

        $p->wait();

        self::assertEquals('Error', $this->values['r1']);
        self::assertEquals('Error', $this->values['r2']);
    }

    public function testThenWithRejectedThenable(): void
    {
        $p = Promise::create(
            function ($re) {
                $re('Hello');
            }
        )
            ->then(
                function () {
                    $this->values['p2'] = $p2 = new Promise(
                        function ($re2, $rj2) {
                            $rj2('Error');
                        }
                    );

                    $p2->then(
                        nope(),
                        function ($reason) {
                            $this->values['r1'] = $reason;
                        }
                    );

                    return $p2;
                }
            )
            ->then(
                function ($v) {
                    $this->values['v1'] = $v;
                },
                function ($r2) {
                    $this->values['r2'] = $r2;
                }
            );

        $p->wait();

        self::assertEquals('Error', $this->values['r1']);
        self::assertEquals('Error', $this->values['r2']);
    }
}
