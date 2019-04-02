<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Queue\Job;

use SuperClosure\Serializer;

/**
 * The CallableJob class.
 *
 * @since  3.2
 */
class CallableJob implements JobInterface, \Serializable
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
    protected $name;

    /**
     * CallableJob constructor.
     *
     * @param string   $name
     * @param callable $callback
     */
    public function __construct(callable $callback, $name = null)
    {
        if (!class_exists(Serializer::class)) {
            throw new \DomainException('Please install jeremeamia/SuperClosure ^2.0 first');
        }

        $this->callback = $callback;
        $this->name = $name;
    }

    /**
     * getName
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * handle
     *
     * @return  void
     */
    public function execute()
    {
        $callback = $this->callback;

        $callback();
    }

    /**
     * serialize
     *
     * @return  string
     *
     * @since  3.5.2
     */
    public function serialize()
    {
        $serializer = new Serializer();

        return $serializer->serialize($this->callback);
    }

    /**
     * unserialize
     *
     * @param string $serialized
     *
     * @return  void
     *
     * @since  3.5.2
     */
    public function unserialize($serialized)
    {
        $serializer = new Serializer();

        $this->callback = $serializer->unserialize($serialized);
    }
}
