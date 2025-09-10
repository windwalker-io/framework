<?php

declare(strict_types=1);

namespace Windwalker\DI\Definition;

use Closure;
use Windwalker\DI\Attributes\Isolation;
use Windwalker\DI\Container;
use Windwalker\DI\DIOptions;
use Windwalker\DI\Exception\DefinitionException;

use function Windwalker\has_attributes;
use function Windwalker\unwrap_enum;

/**
 * The NewStoreDefinition class.
 */
class StoreDefinition implements StoreDefinitionInterface
{
    protected DIOptions $options;

    protected array $cache = [];

    protected array $extends = [];

    public protected(set) ?Container $container = null;

    public Closure|array|int|null $providedIn = null;

    public function __construct(
        public string $id,
        public protected(set) mixed $value,
        DIOptions|int $options = new DIOptions(),
        public \UnitEnum|string|null $tag = null
    ) {
        $this->options = DIOptions::wrap($options);

        if (!$this->value instanceof DefinitionInterface && !$this->value instanceof Closure) {
            $this->setCache($this->value, $this->tag);
        }

        if (
            class_exists($id)
            && has_attributes(new \ReflectionClass($id), Isolation::class, true)
        ) {
            $this->options = $this->options->with(isolation: true);
        }
    }

    /**
     * @throws \ReflectionException
     * @throws DefinitionException
     */
    public function resolve(?Container $container = null, array $args = [], \UnitEnum|string|null $tag = null): mixed
    {
        $container ??= $this->container ?? throw new DefinitionException('This definition has no container.');
        $tag ??= $this->tag;

        if ($this->hasCache($tag)) {
            return $this->getCache($tag);
        }

        if (!$this->validateProvidedIn($container)) {
            throw new DefinitionException(
                "Container ID: {$this->id} cannot provide to level: {$container->getLevel()}"
            );
        }

        $value = $this->value;

        // Build object if is builder
        if ($this->value instanceof ObjectBuilderDefinition) {
            $define = clone $this->value;
            // Set into definition to support function resolve
            $define->addArguments($args);

            if ($tag !== null) {
                $define->tag($tag);
            }

            $value = $define->resolve($container);
        }

        // Invoke
        if ($this->value instanceof Closure) {
            if ($tag !== null) {
                $args['tag'] = $tag;
            }

            $value = $container->call($this->value, $args);
        }

        // Cache
        if ($this->options->shared) {
            $this->setCache($value, $tag);
        }

        // Extends
        foreach ($this->extends as $extend) {
            $value = $extend($value, $container, $tag) ?? $value;
        }

        foreach ($container->findExtends($this->id, $tag) as $extend) {
            $value = $extend($value, $container, $tag) ?? $value;
        }

        // Cache again
        if ($this->options->shared) {
            $this->setCache($value, $tag);
        }

        return $value;
    }

    public function set(mixed $value): void
    {
        $this->cache = [];
        $this->value = $value;
    }

    public function isShared(): bool
    {
        return (bool) $this->options->shared;
    }

    public function isProtected(): bool
    {
        return (bool) $this->options->protected;
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
        $this->cache = [];
    }

    /**
     * @return DIOptions
     */
    public function getOptions(): DIOptions
    {
        return $this->options;
    }

    /**
     * @param  DIOptions  $options
     *
     * @return  static  Return self to support chaining.
     */
    public function setOptions(DIOptions $options): static
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

    public function getCache(\UnitEnum|string|null $tag = null): mixed
    {
        $key = $this->buildCacheKey($tag);

        return $this->cache[$key] ?? null;
    }

    protected function hasCache(\UnitEnum|string|null $tag = null): bool
    {
        $key = $this->buildCacheKey($tag);

        return isset($this->cache[$key]);
    }

    protected function setCache(mixed $value, \UnitEnum|string|null $tag = null): static
    {
        $key = $this->buildCacheKey($tag);

        $this->cache[$key] = $value;

        return $this;
    }

    public function __clone(): void
    {
        if ($this->value instanceof ObjectBuilderDefinition) {
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

    protected function buildCacheKey(\UnitEnum|string|null $tag = null): string
    {
        return unwrap_enum($tag ?? $this->tag ?? '__default__');
    }
}
