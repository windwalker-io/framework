<?php

declare(strict_types=1);

namespace Windwalker\Attributes;

use Attribute;
use LogicException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionObject;
use ReflectionParameter;
use ReflectionProperty;
use Reflector;
use Windwalker\Utilities\Classes\ObjectBuilder;
use Windwalker\Utilities\Options\OptionAccessTrait;
use Windwalker\Utilities\Reflection\ReflectionCallable;

/**
 * The AttributesResolver class.
 */
class AttributesResolver extends ObjectBuilder
{
    use OptionAccessTrait;

    protected array $registry = [];

    /**
     * @var callable
     */
    protected $invokeHandler;

    /**
     * AttributesResolver constructor.
     *
     * @param array $options
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
     * @param string $class
     * @param mixed  ...$args
     *
     * @return  object
     * @throws ReflectionException
     */
    public function createObject(string $class, ...$args): object
    {
        $ref         = new ReflectionClass($class);
        $constructor = $ref->getConstructor();

        if ($constructor) {
            $args = $this->resolveCallArguments($constructor, $args);
        }

        return $this->resolveClassCreate($class)(...$args);
    }

    /**
     * Resolve class constructor and return create function.
     *
     * @param string        $class
     * @param callable|null $builder
     * @param array         $options
     *
     * @return  AttributeHandler
     *
     * @throws ReflectionException
     */
    public function resolveClassCreate(
        string $class,
        ?callable $builder = null,
        array $options = []
    ): AttributeHandler {
        $ref = new ReflectionClass($class);

        $builder = $builder ?? fn(...$args) => $this->getBuilder()($class, ...$args);

        $handler = $this->createHandler($builder, $ref, null, $options);

        foreach ($ref->getAttributes() as $attribute) {
            if ($this->hasAttribute($attribute, Attribute::TARGET_CLASS)) {
                $handler = $this->runAttribute($attribute, $handler);
            }
        }

        return $handler;
    }

    /**
     * Decorate object by attributes.
     *
     * @param object $object
     * @param array  $options
     *
     * @return  object
     */
    public function decorateObject(object $object, array $options = []): object
    {
        return $this->resolveObjectDecorate($object, $options)();
    }

    /**
     * Resolve object decorate function.
     *
     * @param object $instance
     *
     * @return  AttributeHandler
     */
    public function resolveObjectDecorate(object $instance, array $options = []): AttributeHandler
    {
        $ref = $instance instanceof Reflector ? $instance : new ReflectionObject($instance);
//        $object = $ref instanceof ReflectionObject ? $instance : null;

        // If is closure, get closure back.
        if ($instance instanceof ReflectionFunction) {
            $instance = $instance->getClosure();
        }

        $builder = $this->createHandler(fn() => $instance, $ref, null, $options);

        foreach ($ref->getAttributes() as $attribute) {
            if ($this->hasAttribute($attribute, Attribute::TARGET_CLASS)) {
                $builder = $this->runAttribute($attribute, $builder);
            }
        }

        return $builder;
    }

    /**
     * call
     *
     * @param callable    $callable
     * @param mixed       ...$args
     * @param object|null $context
     *
     * @return mixed
     */
    public function call(callable $callable, array $args = [], ?object $context = null, array $options = []): mixed
    {
        if ($this->invokeHandler) {
            return ($this->invokeHandler)($callable, $args, $context);
        }

        $args = $this->resolveCallArguments($callable, $args);

        return $this->resolveCallable($callable, $context, $options)(...$args);
    }

    public function resolveCallable(mixed $callable, ?object $context = null, array $options = []): callable
    {
        $ref     = new ReflectionCallable($callable);
        $funcRef = $ref->getReflector();

        $closure = $ref->getClosure();

        if ($context) {
            $closure = $closure->bindTo($context, $context);
        }

        $attributes = $funcRef->getAttributes();

        if (!$attributes) {
            return $closure;
        }

        $handler = $this->createHandler($closure, $ref, $ref->getObject(), $options);

        foreach ($attributes as $attribute) {
            if ($this->hasAttribute($attribute, AttributeType::CALLABLE)) {
                $handler = $this->runAttribute($attribute, $handler);
            }
        }

        return $handler;
    }

    public function resolveCallArguments(
        callable|ReflectionFunctionAbstract $ref,
        array $args
    ): array {
        if (!$ref instanceof ReflectionFunctionAbstract) {
            $callableRef = new ReflectionCallable($ref);
            $ref         = $callableRef->getReflector();
        }

        $parameters = $ref->getParameters();
        $newArgs    = [];

        foreach ($parameters as $i => $parameter) {
            $key = $parameter->getName();

            if ($parameter->isVariadic()) {
                if ($parameter->getPosition() === 0) {
                    return $args;
                }

                $trailing = [];

                foreach ($args as $key => $v) {
                    if (is_numeric($key)) {
                        $trailing[] = &$args[$key];
                    }

                    unset($v);
                }

                $trailing = array_slice($trailing, $i);
                $newArgs  = [...$newArgs, ...$trailing];
            } elseif (array_key_exists($parameter->getName(), $args)) {
                $newArgs[$key] = &$args[$parameter->getName()];
            } elseif (array_key_exists($i, $args)) {
                $newArgs[$key] = &$args[$i];
            } elseif ($parameter->isDefaultValueAvailable()) {
                $newArgs[$key] = $parameter->getDefaultValue();
            }

            $newArgs[$key] = &$this->resolveParameter($newArgs[$key], $parameter);
        }

        return $newArgs;
    }

    public function &resolveParameter(&$value, ReflectionParameter $ref, array $options = []): mixed
    {
        $attributes = $ref->getAttributes();

        if ($attributes === []) {
            return $value;
        }

        $func = fn() => $value;

        $handler = $this->createHandler($func, $ref, null, $options);

        foreach ($ref->getAttributes() as $attribute) {
            if ($this->hasAttribute($attribute, Attribute::TARGET_PARAMETER)) {
                $handler = $this->runAttribute($attribute, $handler);
            }
        }

        $value = $handler();

        return $value;
    }

    public function resolveProperties(object $instance, array $options = []): object
    {
        $ref    = $instance instanceof Reflector ? $instance : new ReflectionObject($instance);
        $object = $ref instanceof ReflectionObject ? $instance : null;

        /** @var ReflectionProperty $property */
        foreach ($ref->getProperties() as $property) {
            $attributes = $property->getAttributes();

            if ($attributes === []) {
                continue;
            }

            if ($property->isPrivate() || $property->isProtected()) {
                $property->setAccessible(true);
            }

            $shouldCall = false;
            $getter     = static fn() => $object && $property->isInitialized($object)
                ? $property->getValue($object)
                : $property->getDefaultValue();

            $handler = $this->createHandler($getter, $property, $object, $options);

            foreach ($attributes as $attribute) {
                if ($this->hasAttribute($attribute, Attribute::TARGET_PROPERTY)) {
                    $handler    = $this->runAttribute($attribute, $handler);
                    $shouldCall = true;
                }
            }

            if ($shouldCall === true) {
                $handler();
            }

            if ($property->isPrivate() || $property->isProtected()) {
                $property->setAccessible(false);
            }
        }

        return $instance;
    }

    public function resolveMethods(object $instance, array $options = []): object
    {
        $ref    = $instance instanceof Reflector ? $instance : new ReflectionObject($instance);
        $object = $ref instanceof ReflectionObject ? $instance : null;
        $target = $ref instanceof ReflectionObject ? $instance : $ref->getName();

        foreach ($ref->getMethods() as $method) {
            $attributes = $method->getAttributes();

            if ($attributes === []) {
                continue;
            }

            $getter     = static fn(): array => [$target, $method->getName()];
            $shouldCall = false;

            $handler = $this->createHandler($getter, $method, $object, $options);

            foreach ($method->getAttributes() as $attribute) {
                if ($this->hasAttribute($attribute, Attribute::TARGET_METHOD)) {
                    $handler    = $this->runAttribute($attribute, $handler);
                    $shouldCall = true;
                }
            }

            if ($shouldCall === true) {
                $handler();
            }
        }

        return $instance;
    }

    public function resolveConstants(object $instance, array $options = []): object
    {
        $ref = $instance instanceof Reflector ? $instance : new ReflectionObject($instance);

        $object = $ref instanceof ReflectionObject ? $instance : null;

        /** @var ReflectionClassConstant $constant */
        foreach ($ref->getReflectionConstants() as $constant) {
            $attributes = $constant->getAttributes();

            if ($attributes === []) {
                continue;
            }

            $getter     = static fn(): array => [$object, $constant];
            $shouldCall = false;

            $handler = $this->createHandler($getter, $constant, $object, $options);

            foreach ($constant->getAttributes() as $attribute) {
                if ($this->hasAttribute($attribute, Attribute::TARGET_METHOD)) {
                    $handler    = $this->runAttribute($attribute, $handler);
                    $shouldCall = true;
                }
            }

            if ($shouldCall === true) {
                $handler();
            }
        }

        return $instance;
    }

    public function resolveObjectMembers(object $instance, array $options = []): object
    {
        /** @var ReflectionObject|ReflectionClass $ref */
        $ref = $instance instanceof Reflector ? $instance : new ReflectionObject($instance);

        if ($ref->getConstants() !== []) {
            $this->resolveConstants($instance, $options);
        }

        if ($ref->getProperties() !== []) {
            $this->resolveProperties($instance, $options);
        }

        if ($ref->getMethods() !== []) {
            $this->resolveMethods($instance, $options);
        }

        return $instance;
    }

    public function hasAttribute(
        string|ReflectionAttribute $attributeClass,
        int $target = Attribute::TARGET_ALL
    ): bool {
        if ($attributeClass instanceof ReflectionAttribute) {
            $attributeClass = $attributeClass->getName();
        }

        $attr = $this->registry[strtolower($attributeClass)] ?? null;

        if (!$attr) {
            return false;
        }

        return (bool) ($attr[1] & $target);
    }

    /**
     * registerAttribute
     *
     * @param string $attributeClass
     * @param int    $target
     *
     * @return  static
     */
    public function registerAttribute(
        string $attributeClass,
        int $target = Attribute::TARGET_ALL
    ): static {
        $this->registry[strtolower($attributeClass)] ??= [
            strtolower($attributeClass),
            $target,
        ];

        $this->registry[strtolower($attributeClass)][1] |= $target;

        return $this;
    }

    /**
     * removeAttribute
     *
     * @param string $attributeClass
     * @param int    $target
     *
     * @return  static
     */
    public function removeAttribute(
        string $attributeClass,
        int $target = Attribute::TARGET_ALL
    ): static {
        if ($target === Attribute::TARGET_ALL || $target === AttributeType::ALL) {
            unset($this->registry[strtolower($attributeClass)]);
        } else {
            $this->registry[strtolower($attributeClass)][1] ^= $target;
        }

        return $this;
    }

    public function runAttribute(
        ReflectionAttribute $attribute,
        AttributeHandler $handler
    ): AttributeHandler {
        /** @var callable|object $attrInstance */
        $attrInstance = $attribute->newInstance();

        $this->prepareAttribute($attribute);

        if (!is_callable($attrInstance)) {
            $class = $attrInstance::class;
            throw new LogicException("Attribute: {$class} is not invokable.");
        }

        $result = $attrInstance($handler);

        // Attribute ran, create new handler for next.
        return $this->createHandler(
            $result,
            $handler->reflector,
            $handler->object,
            $handler->options
        );
    }

    /**
     * createAttributeHandler
     *
     * @param callable    $getter
     * @param Reflector   $reflector
     * @param object|null $object
     * @param array       $options
     *
     * @return  AttributeHandler
     */
    protected function createHandler(
        callable $getter,
        Reflector $reflector,
        ?object $object = null,
        array $options = [],
    ): AttributeHandler {
        return new AttributeHandler($getter, $reflector, $object, $this, $options);
    }

    protected function prepareAttribute(object $attribute): void
    {
        //
    }

    protected static function normalizeClassName(string $className): string
    {
        return strtolower(trim($className, '\\'));
    }

    /**
     * @return callable
     */
    public function getInvokeHandler(): callable
    {
        return $this->invokeHandler;
    }

    /**
     * @param callable $invokeHandler
     *
     * @return  static  Return self to support chaining.
     */
    public function setInvokeHandler(callable $invokeHandler): static
    {
        $this->invokeHandler = $invokeHandler;

        return $this;
    }
}
