<?php

declare(strict_types=1);

namespace Windwalker\DI\Definition;

use Closure;
use Windwalker\DI\Attributes\Isolation;
use Windwalker\DI\Container;
use Windwalker\DI\Exception\DefinitionException;

use function Windwalker\has_attributes;

/**
 * The NewStoreDefinition class.
 */
class StoreDefinition implements StoreDefinitionInterface
{
    public protected(set) mixed $cache = null;

    protected array $extends = [];

    public protected(set) ?Container $container = null;

    public Closure|array|int|null $providedIn = null;

    public function __construct(
        public string $id,
        public protected(set) mixed $value,
        public int $options = 0,
        public ?string $tag = null
    ) {
        if (!$this->value instanceof DefinitionInterface && !$this->value instanceof Closure) {
            $this->cache = $this->value;
        }

        if (
            class_exists($id)
            && has_attributes(new \ReflectionClass($id), Isolation::class, true)
        ) {
            $this->options |= Container::ISOLATION;
        }
    }

    /**
     * @throws \ReflectionException
     * @throws DefinitionException
     */
    public function resolve(?Container $container = null, array $args = [], ?string $tag = null): mixed
    {
        $container ??= $this->container ?? throw new DefinitionException('This definition has no container.');

        if ($this->cache !== null) {
            return $this->cache;
        }

        if (!$this->validateProvidedIn($container)) {
            throw new DefinitionException(
                "Container ID: {$this->id} cannot provide to level: {$container->getLevel()}"
            );
        }

        $value = $this->value;
        $tag ??= $this->tag;

        // Build object if is builder
        if ($this->value instanceof ObjectBuilderDefinition) {
            $define = clone $this->value;
            $define->addArguments($args);
            $define->tag($tag);

            $value = $define->resolve($container);
        }

        // Invoke
        if ($this->value instanceof Closure) {
            $value = ($this->value)($container, $tag);
        }

        // Cache
        if ($this->options & Container::SHARED) {
            $this->cache = $value;
        }

        // Extends
        foreach ($this->extends as $extend) {
            $value = $extend($value, $container, $tag) ?? $value;
        }

        foreach ($container->findExtends($this->id) as $extend) {
            $value = $extend($value, $container, $tag) ?? $value;
        }

        // Cache again
        if ($this->options & Container::SHARED) {
            $this->cache = $value;
        }

        return $value;
    }

    public function set(mixed $value): void
    {
        $this->cache = null;
        $this->value = $value;
    }

    public function isShared(): bool
    {
        return (bool) ($this->options & Container::SHARED);
    }

    public function isProtected(): bool
    {
        return (bool) ($this->options & Container::PROTECTED);
    }

    public function extend(Closure $closure): static
    {
        $this->extends[] = $closure;

        return $this;
    }

    public function alias(string $alias, string $id): static
    {
        $this->container->alias($alias, $id);

        return $this;
    }

    public function reset(): void
    {
        $this->cache = null;
    }

    /**
     * @return int
     */
    public function getOptions(): int
    {
        return $this->options;
    }

    /**
     * @param  int  $options
     *
     * @return  static  Return self to support chaining.
     */
    public function setOptions(int $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param  string  $id
     *
     * @return  static  Return self to support chaining.
     */
    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getCache(): mixed
    {
        return $this->cache;
    }

    public function __clone(): void
    {
        if (is_object($this->value)) {
            $this->value = clone $this->value;
        }
    }

    public function getContainer(): ?Container
    {
        return $this->container;
    }

    /**
     * @param  Container|null  $container
     *
     * @return  static  Return self to support chaining.
     */
    public function setContainer(?Container $container): static
    {
        $this->container = $container;

        return $this;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function providedIn(int|array|Closure|null $levels): static
    {
        $this->providedIn = $levels;

        return $this;
    }

    public function getProvidedIn(): int|array|Closure|null
    {
        return $this->providedIn;
    }

    public function validateProvidedIn(Container $container): bool
    {
        if ($this->providedIn === null) {
            return true;
        }

        $level = $container->getLevel();

        $provided = $this->providedIn;

        if (is_int($provided)) {
            return $provided === $level;
        }

        if (is_array($provided)) {
            return in_array($level, $provided, true);
        }

        return (bool) $provided($level);
    }
}
