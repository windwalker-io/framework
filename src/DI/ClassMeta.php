<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\DI;

/**
 * The ClassMeta class.
 *
 * @method  object  newInstance(array $args = [])
 * @method  object  createObject(array $args = [], $shared = false, $protected = false)
 * @method  object  createSharedObject(array $args = [], $protected = false)
 * @method  Container  bind($value, $shared = false, $protected = false)
 * @method  Container  bindShared($value, $protected = false)
 * @method  Container  prepareObject($extend = null, $shared = false, $protected = false)
 * @method  Container  prepareSharedObject($extend = null, $protected = false)
 *
 * @since  3.0
 */
class ClassMeta
{
    /**
     * Property class.
     *
     * @var  string
     */
    protected $class;

    /**
     * Property arguments.
     *
     * @var  array
     */
    protected $arguments = [];

    /**
     * Property caches.
     *
     * @var  array
     */
    protected $caches = [];

    /**
     * Property container.
     *
     * @var  Container
     */
    protected $container;

    /**
     * ClassMeta constructor.
     *
     * @param string|callable $class
     * @param Container       $container
     */
    public function __construct($class, ?Container $container = null)
    {
        $this->class     = $class;
        $this->container = $container;
    }

    /**
     * Method to get property Argument
     *
     * @param  string $name
     * @param  mixed  $default
     *
     * @return array
     * @throws Exception\DependencyResolutionException
     * @throws \ReflectionException
     */
    public function getArgument($name, $default = null)
    {
        if (!isset($this->arguments[$name])) {
            return $default;
        }

        if (isset($this->caches[$name])) {
            return $this->caches[$name];
        }

        return $this->caches[$name] = $this->container->execute($this->arguments[$name]);
    }

    /**
     * Method to set property argument
     *
     * @param   string $name
     * @param   mixed  $value
     *
     * @return  static Return self to support chaining.
     */
    public function setArgument($name, $value)
    {
        if (!$value instanceof \Closure) {
            $value = function () use ($value) {
                return $value;
            };
        }

        $this->arguments[$name] = $value;
        unset($this->caches[$name]);

        return $this;
    }

    /**
     * hasArgument
     *
     * @param string $name
     *
     * @return  bool
     *
     * @since  3.5.1
     */
    public function hasArgument(string $name): bool
    {
        return isset($this->arguments[$name]);
    }

    /**
     * removeArgument
     *
     * @param   string $name
     *
     * @return  static
     */
    public function removeArgument($name)
    {
        unset($this->arguments[$name], $this->caches[$name]);

        return $this;
    }

    /**
     * Method to get property Arguments
     *
     * @return  array
     * @throws Exception\DependencyResolutionException
     * @throws \ReflectionException
     */
    public function getArguments()
    {
        $args = [];

        foreach ($this->arguments as $name => $callable) {
            $args[$name] = $this->getArgument($name);
        }

        return $args;
    }

    /**
     * Method to set property arguments
     *
     * @param   array $arguments
     *
     * @return  static  Return self to support chaining.
     */
    public function setArguments($arguments)
    {
        foreach ($arguments as $name => $argument) {
            $this->setArgument($name, $argument);
        }

        return $this;
    }

    /**
     * reset
     *
     * @return  static
     */
    public function reset()
    {
        $this->arguments = [];

        return $this;
    }

    /**
     * __call
     *
     * @param   string $name
     * @param   array  $args
     *
     * @return  mixed
     * @throws Exception\DependencyResolutionException
     * @throws \ReflectionException
     */
    public function __call($name, $args)
    {
        $allowMethods = [
            'bind',
            'bindShared',
        ];

        if (in_array($name, $allowMethods, true)) {
            array_unshift($args, $this->class);

            return $this->container->$name(...$args);
        }

        $allowMethods = [
            'newInstance',
            'createObject',
            'createSharedObject',
        ];

        if (in_array($name, $allowMethods, true)) {
            $args[0] = array_merge($this->getArguments(), $args[0] ?? []);

            array_unshift($args, $this->class);

            return $this->container->$name(...$args);
        }

        throw new \BadMethodCallException(__METHOD__ . '::' . $name . '() not found.');
    }

    /**
     * Method to get property Container
     *
     * @return  Container
     *
     * @since  3.5.1
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Method to set property container
     *
     * @param   Container $container
     *
     * @return  static  Return self to support chaining.
     *
     * @since  3.5.1
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }
}
