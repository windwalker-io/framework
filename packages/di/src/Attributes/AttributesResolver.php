<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI\Attributes;

use ReflectionException;
use ReflectionObject;
use Reflector;
use Windwalker\Attributes\AttributeHandler as BaseAttributeHandler;
use Windwalker\Attributes\AttributesResolver as BaseAttributesResolver;
use Windwalker\DI\Container;
use Windwalker\Utilities\Reflection\ReflectAccessor;

/**
 * The AttributesResolver class.
 */
class AttributesResolver extends BaseAttributesResolver
{
    protected Container $container;

    /**
     * AttributesResolver constructor.
     *
     * @param Container $container
     * @param array     $options
     */
    public function __construct(Container $container, array $options = [])
    {
        $this->container = $container;

        parent::__construct($options);
    }

    /**
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * @param Container $container
     *
     * @return  static  Return self to support chaining.
     */
    public function setContainer(Container $container): static
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Create object by class and resolve attributes.
     *
     * @param string $class
     * @param mixed  ...$args
     *
     * @return  object
     */
    public function createObject(string $class, ...$args): object
    {
        return $this->container->newInstance($class, $args);
    }

    /**
     * Resolve class constructor and return create function.
     *
     * @param string        $class
     * @param callable|null $builder
     * @param array         $options
     *
     * @return  BaseAttributeHandler
     *
     * @throws ReflectionException
     */
    public function resolveClassCreate(
        string $class,
        ?callable $builder = null,
        array $options = []
    ): BaseAttributeHandler {
        /*
         * Container builder use `(array $args, int $options)` signature.
         * So we should change the default builder to fit it.
         */
        $builder = $builder ?? fn($args, int $options) => $this->getBuilder()($class, ...$args);

        return parent::resolveClassCreate($class, $builder, $options);
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
        $args    = [];
        $containerOptions = 0;

        /*
         * Container builder use `(array $args, int $options)` signature.
         * So we must add 2 argument to polyfill it.
         */

        return $this->resolveObjectDecorate($object, $options)($args, $options);
    }

    protected function prepareAttribute(object $attribute): void
    {
        // If Attribute need inject, we inject services here.
        $ref = new ReflectionObject($attribute);

        foreach ($ref->getProperties() as $property) {
            $attrs = $property->getAttributes(Inject::class);

            foreach ($attrs as $attr) {
                ReflectAccessor::setValue($attribute, $property->getName(), $attr);
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function createHandler(
        callable $getter,
        Reflector $reflector,
        ?object $object = null,
        array $options = []
    ): BaseAttributeHandler {
        return new AttributeHandler($getter, $reflector, $object, $this, $options, $this->container);
    }

    public function call(
        callable $callable,
        $args = [],
        ?object $context = null,
        array $options = []
    ): mixed {
        // call() for Container unable to send options into it.
        return $this->container->call($callable, $args, $context);
    }
}
