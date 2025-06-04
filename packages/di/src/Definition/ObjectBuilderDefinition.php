<?php

declare(strict_types=1);

namespace Windwalker\DI\Definition;

use BadMethodCallException;
use Closure;
use ReflectionException;
use Windwalker\DI\Container;
use Windwalker\DI\Exception\DependencyResolutionException;

/**
 * The ObjectBuilder class.
 *
 * @method object newInstance(Container $container, string $class, array $args = [], int $options = 0)
 * @method object createObject(Container $container, string $class, array $args = [], int $options = 0)
 * @method object createSharedObject(Container $container, string $class, array $args = [], int $options = 0)
 */
class ObjectBuilderDefinition implements DefinitionInterface
{
    /**
     * Property class.
     *
     * @var  string|callable
     */
    protected $class;

    /**
     * Property arguments.
     *
     * @var  array
     */
    protected array $arguments = [];

    /**
     * Property caches.
     *
     * @var  array
     */
    protected array $caches = [];

    /**
     * @var callable[]
     */
    protected array $extends = [];

    protected ?object $instance = null;

    public ?string $tag = null;

    public function fork(?array $args = null): static
    {
        $new = clone $this;

        if ($args !== null) {
            $new->addArguments($args);
        }

        return $new;
    }

    /**
     * ClassMeta constructor.
     *
     * @param  string|callable  $class
     */
    public function __construct(callable|string $class)
    {
        $this->class = $class;
    }

    /**
     * Resolve this definition.
     *
     * @param  Container  $container  The Container object.
     *
     * @return object
     * @throws ReflectionException
     */
    public function resolve(Container $container): object
    {
        $object = $container->newInstance(
            $this->getClass(),
            $this->resolveArguments($container)
        );

        foreach ($this->extends as $extend) {
            $object = $extend($object, $container);
        }

        return $object;
    }

    /**
     * Set new value or factory callback to this definition.
     *
     * @param  mixed  $value  Value or callable.
     *
     * @return  void
     */
    public function set(mixed $value): void
    {
        $this->class = $value;
    }

    /**
     * Method to get property Argument
     *
     * @param  string  $name
     * @param  mixed   $default
     *
     * @return array
     * @throws ReflectionException
     */
    public function getArgument(mixed $name, mixed $default = null): mixed
    {
        return $this->arguments[$name] ?? $default;
    }

    public function resolveArgument(Container $container, mixed $name, mixed $default = null): mixed
    {
        if (!isset($this->arguments[$name])) {
            return $default;
        }

        return $this->caches[$name] ??= $container->call($this->arguments[$name]);
    }

    /**
     * Method to set property argument
     *
     * @param  string  $name
     * @param  mixed   $value
     *
     * @return  static Return self to support chaining.
     */
    public function setArgument(mixed $name, mixed $value): static
    {
        if (!$value instanceof Closure) {
            $value = static fn() => $value;
        }

        $this->arguments[$name] = $value;
        unset($this->caches[$name]);

        return $this;
    }

    /**
     * hasArgument
     *
     * @param  string  $name
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
     * @param  string  $name
     *
     * @return  static
     */
    public function removeArgument(string $name): static
    {
        unset($this->arguments[$name], $this->caches[$name]);

        return $this;
    }

    /**
     * Method to get property Arguments
     *
     * @return  array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function resolveArguments(Container $container): array
    {
        $args = [];

        foreach ($this->arguments as $name => $callable) {
            $args[$name] = $this->resolveArgument($container, $name);
        }

        return $args;
    }

    /**
     * Method to set property arguments
     *
     * @param  array  $arguments
     *
     * @return  static  Return self to support chaining.
     */
    public function addArguments(array $arguments): static
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
    public function reset(): static
    {
        $this->arguments = [];
        $this->caches = [];
        $this->extends = [];

        return $this;
    }

    /**
     * extend
     *
     * @param  callable  $handler
     *
     * @return  $this
     *
     * @since  3.5.20
     */
    public function extend(callable $handler): static
    {
        $this->extends[] = $handler;

        return $this;
    }

    /**
     * clearExtends
     *
     * @return  $this
     *
     * @since  3.5.20
     */
    public function clearExtends(): static
    {
        $this->extends = [];

        return $this;
    }

    /**
     * __call
     *
     * @param  string  $name
     * @param  array   $args
     *
     * @return  mixed
     * @throws DependencyResolutionException
     * @throws ReflectionException
     */
    public function __call(string $name, array $args): mixed
    {
        $allowMethods = [
            'newInstance',
            'createObject',
            'createSharedObject',
        ];

        if (in_array($name, $allowMethods, true)) {
            /** @var Container $container */
            $container = $args[0];

            $arguments = array_merge($this->resolveArguments($container), $args[1] ?? []);

            $object = $container->$name($this->class, $arguments);

            foreach ($this->extends as $extend) {
                $object = $extend($container, $object);
            }

            return $object;
        }

        throw new BadMethodCallException(__METHOD__ . '::' . $name . '() not found.');
    }

    /**
     * Method to get property Class
     *
     * @return  callable|string
     *
     * @since  3.5.19
     */
    public function getClass(): callable|string
    {
        return $this->class;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function tag(?string $tag): static
    {
        $this->tag = $tag;

        return $this;
    }
}
