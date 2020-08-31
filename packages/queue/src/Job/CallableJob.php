<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Queue\Job;

/**
 * The CallableJob class.
 *
 * @since  3.2
 */
class CallableJob implements JobInterface
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
    public function __construct(callable $callback, ?string $name = null)
    {
        $this->callback = $callback;
        $this->name = $name;
    }

    /**
     * getName
     *
     * @return  string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * handle
     *
     * @return  void
     */
    public function __invoke(): void
    {
        $callback = $this->callback;

        $callback();
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
