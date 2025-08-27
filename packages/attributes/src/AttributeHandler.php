<?php

declare(strict_types=1);

namespace Windwalker\Attributes;

use Reflector;

/**
 * The AttributeHandler class.
 */
class AttributeHandler
{
    /**
     * @var callable
     */
    public $handler;

    /**
     * AttributeHandler constructor.
     *
     * @param callable           $handler
     * @param Reflector          $reflector
     * @param object|null        $object
     * @param AttributesResolver $resolver
     * @param array              $options
     */
    public function __construct(
        callable $handler,
        public protected(set) Reflector $reflector,
        public protected(set) ?object $object,
        public protected(set) AttributesResolver $resolver,
        public protected(set) array $options = [],
    ) {
        $this->handler = $handler;

        $this->options = array_merge(
            $this->resolver->getOptions(),
            $this->options
        );
    }

    public function __invoke(&...$args): mixed
    {
        // try {
        return ($this->handler)(...$args);
        // } catch (\Throwable $e) {
        //     show($this->handler, $this);
        //     throw $e;
        // }
    }

    public function set(callable $handler): static
    {
        $this->handler = $handler;

        return $this;
    }

    public function get(): callable
    {
        return $this->handler;
    }

    /**
     * @return Reflector
     */
    public function getReflector(): Reflector
    {
        return $this->reflector;
    }

    /**
     * @return AttributesResolver
     */
    public function getResolver(): AttributesResolver
    {
        return $this->resolver;
    }

    /**
     * @return mixed
     */
    public function getObject(): mixed
    {
        return $this->object;
    }

    /**
     * @return array
     */
    public function &getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     *
     * @return  static  Return self to support chaining.
     */
    public function setOptions(array $options): static
    {
        $this->options = $options;

        return $this;
    }
}
