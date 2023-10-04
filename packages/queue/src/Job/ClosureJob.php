<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Queue\Job;

use Closure;
use Laravel\SerializableClosure\SerializableClosure;
use Windwalker\DI\Container;

/**
 * The CallableJob class.
 *
 * @since  3.2
 */
class ClosureJob
{
    /**
     * Property callable.
     *
     * @var  callable
     */
    protected $callback;

    /**
     * Property name.
     *
     * @var  null|string
     */
    protected ?string $name = null;

    /**
     * CallableJob constructor.
     *
     * @param  callable     $callback
     * @param  string|null  $name
     */
    public function __construct(callable $callback)
    {
        if ($callback instanceof Closure) {
            if (!class_exists(SerializableClosure::class)) {
                throw new \DomainException('Please install `laravel/serializable-closure` first');
            }

            $callback = new SerializableClosure($callback);
        }

        $this->callback = $callback;
    }

    /**
     * handle
     *
     * @return  void
     * @throws \Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException
     */
    public function __invoke(?Container $container = null): mixed
    {
        $callback = $this->callback;

        if ($callback instanceof SerializableClosure) {
            $callback = $callback->getClosure();
        }

        if ($container) {
            return $container->call($callback);
        }

        return $callback();
    }

    // /**
    //  * serialize
    //  *
    //  * @return  string
    //  *
    //  * @since  3.5.2
    //  */
    // public function serialize()
    // {
    //     return \Windwalker\serialize($this->callback);
    // }
    //
    // /**
    //  * unserialize
    //  *
    //  * @param string $serialized
    //  *
    //  * @return  void
    //  *
    //  * @since  3.5.2
    //  */
    // public function unserialize($serialized)
    // {
    //     $this->callback = \Windwalker\unserialize($serialized);
    // }

    /**
     * @return callable
     */
    public function getCallback(): callable
    {
        return $this->callback;
    }
}
