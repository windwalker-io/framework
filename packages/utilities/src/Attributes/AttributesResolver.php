<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Attributes;

use Windwalker\Utilities\Classes\ObjectBuilder;
use Windwalker\Utilities\Classes\OptionAccessTrait;
use Windwalker\Utilities\Reflection\ReflectionCallable;

/**
 * The AttributesResolver class.
 */
class AttributesResolver extends ObjectBuilder
{
    use OptionAccessTrait;

    protected array $registry = [
        AttributeType::CLASSES => [],
        AttributeType::PROPERTIES => [],
        AttributeType::FUNCTION_METHOD => [],
        AttributeType::PARAMETERS => [],
    ];

    /**
     * AttributesResolver constructor.
     *
     * @param  array  $options
     */
    public function __construct(array $options = [])
    {
        $this->prepareOptions(
            [],
            $options
        );
    }

    /**
     * Create object by class and resolve attributes.
     *
     * @param  string  $class
     * @param  mixed   ...$args
     *
     * @return  object
     * @throws \ReflectionException
     */
    public function createObject(string $class, ...$args): object
    {
        $ref = new \ReflectionClass($class);
        $constructor = $ref->getConstructor();

        if ($constructor) {
            $args = $this->resolveCallArguments($constructor, $args);
        }

        return $this->resolveClassCreate($class)(...$args);
    }

    /**
     * Resolve class constructor and return create function.
     *
     * @param  string         $class
     * @param  callable|null  $builder
     *
     * @return  AttributeHandler
     *
     * @throws \ReflectionException
     */
    public function resolveClassCreate(string $class, ?callable $builder = null): AttributeHandler
    {
        $ref = new \ReflectionClass($class);

        $builder = $builder ?? function (...$args) use ($class) {
            return $this->getBuilder()($class, ...$args);
        };

        $handler = $this->createHandler($builder, $ref);

        foreach ($ref->getAttributes() as $attribute) {
            if ($this->hasAttribute($attribute->getName(), AttributeType::CLASSES)) {
                $handler = $this->runAttribute($attribute, $handler);
            }
        }

        return $handler;
    }

    /**
     * Decorate object by attributes.
     *
     * @param  object  $object
     *
     * @return  object
     */
    public function decorateObject(object $object): object
    {
        return $this->resolveObjectDecorate($object)();
    }

    /**
     * Resolve object decorate function.
     *
     * @param  object  $object
     *
     * @return  AttributeHandler
     */
    public function resolveObjectDecorate(object $object): AttributeHandler
    {
        $ref = new \ReflectionObject($object);

        $builder = fn () => $object;

        foreach ($ref->getAttributes() as $attribute) {
            if ($this->hasAttribute($attribute->getName(), AttributeType::CLASSES)) {
                $builder = $this->runAttribute($attribute, $this->createHandler($builder, $ref)) ?? $builder;
            }
        }

        return $builder;
    }

    /**
     * call
     *
     * @param  callable     $callable
     * @param  mixed        ...$args
     * @param  object|null  $context
     *
     * @return
     */
    public function call(callable $callable, $args = [], ?object $context = null)
    {
        $args = $this->resolveCallArguments($callable, $args);

        return $this->resolveCallable($callable, $context)(...$args);
    }

    public function resolveCallable(callable $callable, ?object $context = null): AttributeHandler
    {
        $ref = new ReflectionCallable($callable);
        $funcRef = $ref->getReflector();

        $closure = $ref->getClosure();

        if ($context) {
            $closure = $closure->bindTo($context, $context);
        }

        $handler = $this->createHandler($closure, $ref);

        foreach ($funcRef->getAttributes() as $attribute) {
            if ($this->hasAttribute($attribute->getName(), AttributeType::FUNCTION_METHOD)) {
                $handler = $this->runAttribute($attribute, $handler);
            }
        }

        return $handler;
    }

    public function resolveCallArguments(callable|\ReflectionFunctionAbstract $ref, array $args): array
    {
        if (!$ref instanceof \ReflectionFunctionAbstract) {
            $callableRef = new ReflectionCallable($ref);
            $ref = $callableRef->getReflector();
        }

        $parameters = $ref->getParameters();
        $newArgs = [];

        foreach ($parameters as $i => $parameter) {
            $key = $parameter->getName();

            if (array_key_exists($parameter->getName(), $args)) {
                $newArgs[$key] = &$args[$parameter->getName()];
            } elseif (array_key_exists($i, $args)) {
                $newArgs[$key] = &$args[$i];
            } else {
                $newArgs[$key] = $parameter->getDefaultValue();
            }

            $newArgs[$key] = &$this->resolveParameter($newArgs[$key], $parameter);
        }

        return $newArgs;
    }

    public function &resolveParameter(&$value, \ReflectionParameter $ref)
    {
        $func = fn () => $value;

        $handler = $this->createHandler($func, $ref);

        foreach ($ref->getAttributes() as $attribute) {
            if ($this->hasAttribute($attribute->getName(), AttributeType::PARAMETERS)) {
                $handler = $this->runAttribute($attribute, $handler);
            }
        }

        $value = $handler();

        return $value;
    }

    public function resolveProperties(object $instance): object
    {
        $ref = new \ReflectionObject($instance);

        foreach ($ref->getProperties() as $property) {
            if ($property->isPrivate() || $property->isProtected()) {
                $property->setAccessible(true);
            }

            $getter = fn () => $property->getValue($instance);

            foreach ($property->getAttributes() as $attribute) {
                if ($this->hasAttribute($attribute->getName(), AttributeType::PROPERTIES)) {
                    $getter = $this->runAttribute($attribute, $this->createHandler($getter, $property)) ?? $instance;
                }
            }

            $value = $getter();

            $property->setValue($instance, $value);

            if ($property->isPrivate() || $property->isProtected()) {
                $property->setAccessible(false);
            }
        }

        return $instance;
    }

    public function hasAttribute(string $attributeClass, string $type): bool
    {
        return in_array(strtolower($attributeClass), $this->registry[$type], true);
    }

    /**
     * registerAttribute
     *
     * @param  string        $attributeClass
     * @param  array|string  $types
     *
     * @return  static
     */
    public function registerAttribute(string $attributeClass, array|string $types)
    {
        $types = (array) $types;

        foreach ($types as $type) {
            if (!$this->hasAttribute($attributeClass, $type)) {
                $this->registry[$type][] = strtolower($attributeClass);
            }
        }

        return $this;
    }

    /**
     * removeAttribute
     *
     * @param  string  $attributeClass
     * @param  string  $type
     *
     * @return  static
     */
    public function removeAttribute(string $attributeClass, string $type)
    {
        unset($this->registry[$type][strtolower($attributeClass)]);

        return $this;
    }

    protected function runAttribute(\ReflectionAttribute $attribute, AttributeHandler $handler): AttributeHandler
    {
        /** @var callable|object $attrInstance */
        $attrInstance = $attribute->newInstance();

        $this->prepareAttribute($attribute);

        if (!is_callable($attrInstance)) {
            $class = get_class($attribute);
            throw new \LogicException("Attribute: {$class} is not invokable.");
        }

        $result = $attrInstance($handler);

        return $this->createHandler($result, $handler->getReflactor());
    }

    /**
     * createAttributeHandler
     *
     * @param  callable    $getter
     * @param  \Reflector  $property
     *
     * @return  AttributeHandler
     */
    protected function createHandler(callable $getter, \Reflector $property): AttributeHandler
    {
        return new AttributeHandler($getter, $property, $this);
    }

    protected function prepareAttribute(object $attribute): void
    {
        //
    }

    protected static function normalizeClassName(string $className): string
    {
        return strtolower(trim($className, '\\'));
    }
}
