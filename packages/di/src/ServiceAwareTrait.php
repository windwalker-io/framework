<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2020 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\DI;

use ReflectionException;

/**
 * Trait ServiceAwareTrait
 */
trait ServiceAwareTrait
{
    use ContainerAwareTrait;

    /**
     * make
     *
     * @param  mixed  $class
     * @param  array  $args
     * @param  int    $options
     *
     * @return  object
     */
    public function make($class, array $args = [], int $options = 0): object
    {
        return $this->getContainer()->newInstance($class, $args, $options);
    }

    /**
     * service
     *
     * @param  mixed  $class
     * @param  array  $args
     * @param  int    $options
     *
     * @return  object
     *
     * @throws Exception\DefinitionException
     */
    public function service(string $class, array $args = [], int $options = 0): object
    {
        $container = $this->getContainer();

        if ($container->has($class)) {
            return $container->get($class);
        }

        return $container->createSharedObject($class, $args, $options);
    }

    /**
     * call
     *
     * @param  callable     $callable
     * @param  array        $args
     * @param  object|null  $context
     * @param  int          $options
     *
     * @return  mixed
     *
     * @throws ReflectionException
     */
    public function call(callable $callable, array $args, ?object $context, int $options = 0)
    {
        return $this->getContainer()->call($callable, $args, $context, $options);
    }

    /**
     * bind
     *
     * @param  string  $id
     * @param  mixed   $value
     * @param  int     $options
     *
     * @return  static
     *
     * @throws Exception\DefinitionException
     */
    public function bind(string $id, $value, int $options = 0)
    {
        $this->getContainer()->bind($id, $value, $options);

        return $this;
    }

    /**
     * resolve
     *
     * @param  mixed $source
     * @param  bool  $forceNew
     *
     * @return  mixed|object|string
     *
     * @throws ReflectionException
     */
    public function resolve($source, bool $forceNew = false)
    {
        return $this->getContainer()->resolve($source);
    }
}
