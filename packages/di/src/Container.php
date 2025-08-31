<?php

declare(strict_types=1);

namespace Windwalker\DI;

use Closure;
use Countable;
use Generator;
use InvalidArgumentException;
use IteratorAggregate;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use Traversable;
use UnexpectedValueException;
use Windwalker\DI\Attributes\AttributesResolver;
use Windwalker\DI\Attributes\AttributeType;
use Windwalker\DI\Attributes\Autowire;
use Windwalker\DI\Attributes\Decorator;
use Windwalker\DI\Attributes\Inject;
use Windwalker\DI\Attributes\Lazy;
use Windwalker\DI\Attributes\Service;
use Windwalker\DI\Attributes\Setup;
use Windwalker\DI\Concern\ConfigRegisterTrait;
use Windwalker\DI\Definition\DefinitionInterface;
use Windwalker\DI\Definition\ObjectBuilderDefinition;
use Windwalker\DI\Definition\StoreDefinition;
use Windwalker\DI\Definition\StoreDefinitionInterface;
use Windwalker\DI\Exception\DefinitionException;
use Windwalker\DI\Exception\DefinitionNotFoundException;
use Windwalker\DI\Exception\DefinitionResolveException;
use Windwalker\DI\Exception\DependencyResolutionException;
use Windwalker\DI\Wrapper\CallbackWrapper;
use Windwalker\Event\EventListenableInterface;
use Windwalker\Event\Provider\SubscribableListenerProviderInterface;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Contract\ArrayAccessibleInterface;
use Windwalker\Utilities\Wrapper\RawWrapper;
use Windwalker\Utilities\Wrapper\ValueReference;

/**
 * The Container class.
 */
class Container implements ContainerInterface, IteratorAggregate, Countable, ArrayAccessibleInterface
{
    use ConfigRegisterTrait;

    /**
     * @deprecated  Use {@see DIOptions} instead.
     */
    public const int SHARED = 1 << 0;

    /**
     * @deprecated  Use {@see DIOptions} instead.
     */
    public const int PROTECTED = 1 << 1;

    /**
     * @deprecated  Use {@see DIOptions} instead.
     */
    public const int ISOLATION = 1 << 2;

    /**
     * @deprecated  Use {@see DIOptions} instead.
     */
    public const int AUTO_WIRE = 1 << 3;

    /**
     * @deprecated  Use {@see DIOptions} instead.
     */
    public const int IGNORE_ATTRIBUTES = 1 << 4;

    /**
     * @deprecated  Use {@see MergeOptions} instead.
     */
    public const int MERGE_OVERRIDE = 1 << 0;

    /**
     * @deprecated  Use {@see MergeOptions} instead.
     */
    public const int MERGE_RECURSIVE = 1 << 1;

    public protected(set) DIOptions $options;

    public protected(set) int $level = 1;

    /**
     * Holds the key aliases.
     *
     * @var    array $aliases
     * @since  2.0
     */
    protected array $aliases = [];

    /**
     * @var StoreDefinitionInterface[]
     */
    protected array $storage = [];

    /**
     * Parent for hierarchical containers.
     *
     * @var  Container|null
     */
    public protected(set) ?Container $parent = null;

    /**
     * Property parameters.
     *
     * @var Parameters
     */
    public protected(set) Parameters $parameters;

    /**
     * @var ObjectBuilderDefinition[]
     */
    protected array $builders = [];

    protected DependencyResolver $dependencyResolver;

    public protected(set) AttributesResolver $attributesResolver;

    /**
     * @var callable[][]
     */
    protected array $extends = [];

    public static function define(string|callable $class, array $args): ObjectBuilderDefinition
    {
        $builder = new ObjectBuilderDefinition($class);
        $builder->addArguments($args);

        return $builder;
    }

    /**
     * Container constructor.
     *
     * @param  Container|null  $parent
     * @param  DIOptions|int   $options
     *
     * @throws DefinitionException
     */
    public function __construct(?Container $parent = null, DIOptions|int $options = new DIOptions())
    {
        $this->parent = $parent;
        $this->options = DIOptions::wrap($options);
        $this->parameters = new Parameters();
        $this->dependencyResolver = new DependencyResolver($this);

        if ($parent) {
            $this->level = $parent->level + 1;
            $this->aliases = $parent->aliases;
            $this->options = clone $parent->options;
            $params = $parent->getParameters()->createChild();
            $this->setParameters($params->reset());

            $this->setAttributesResolver(clone $parent->getAttributesResolver());
        } else {
            $this->attributesResolver = new AttributesResolver($this);
        }

        // Always set Container as self
        $this->share(static::class, $this);
    }

    public function registerDefaultAttributes(): void
    {
        $resolver = $this->getAttributesResolver();

        foreach (static::getDefaultAttributes() as $attr => $target) {
            $resolver->registerAttribute($attr, $target);
        }
    }

    public static function getDefaultAttributes(): array
    {
        return [
            Decorator::class => AttributeType::CLASSES,
            Autowire::class => AttributeType::CLASSES | AttributeType::CALLABLE | AttributeType::PARAMETERS,
            Inject::class => AttributeType::PROPERTIES | AttributeType::PARAMETERS,
            Setup::class => AttributeType::METHODS,
            Service::class => AttributeType::PROPERTIES | AttributeType::PARAMETERS,
            Lazy::class => AttributeType::PROPERTIES,
        ];
    }

    /**
     * @param  string                 $id
     * @param  mixed                  $value
     * @param  DIOptions|int          $options
     * @param  \UnitEnum|string|null  $tag
     *
     * @return StoreDefinitionInterface
     * @throws DefinitionException
     */
    public function set(
        string $id,
        mixed $value,
        DIOptions|int $options = new DIOptions(),
        \UnitEnum|string|null $tag = null,
    ): StoreDefinitionInterface {
        $options = DIOptions::wrap($options);

        $definition = $this->findDefinition($id, false, $tag);

        if ($definition && $definition->isProtected()) {
            throw new DefinitionException(
                sprintf(
                    'Container id: %s is protected.',
                    $id,
                ),
            );
        }

        if (!$value instanceof StoreDefinition) {
            $value = new StoreDefinition($id, $value, $options);
        } else {
            // Clone a new store to avoid side effect
            $value = clone $value;
            $value->id = $id;
            $value->tag = $tag;
        }

        $definition = $this->setDefinition($id, $value, $tag);

        $this->removeAlias($id);

        return $definition;
    }

    /**
     * share
     *
     * @param  string         $id
     * @param  mixed          $value
     * @param  DIOptions|int  $options
     *
     * @return StoreDefinitionInterface
     * @throws DefinitionException
     */
    public function share(string $id, mixed $value, DIOptions|int $options = new DIOptions()): StoreDefinitionInterface
    {
        $options = DIOptions::wrap($options);
        $options->shared = true;

        return $this->set($id, $value, $options);
    }

    /**
     * protect
     *
     * @param  string         $id
     * @param  mixed          $value
     * @param  DIOptions|int  $options
     *
     * @return StoreDefinitionInterface
     * @throws DefinitionException
     */
    public function protect(
        string $id,
        mixed $value,
        DIOptions|int $options = new DIOptions()
    ): StoreDefinitionInterface {
        $options = DIOptions::wrap($options);
        $options->protected = true;

        return $this->set($id, $value, $options);
    }

    /**
     * @param  string                    $id
     * @param  StoreDefinitionInterface  $value
     * @param  \UnitEnum|string|null     $tag
     *
     * @return  StoreDefinitionInterface
     */
    public function setDefinition(
        string $id,
        StoreDefinitionInterface $value,
        \UnitEnum|string|null $tag = null,
    ): StoreDefinitionInterface {
        $value->setContainer($this);

        $id = static::toTaggedId($id, $tag ?? $value->getTag());

        $this->storage[$id] = $value;

        return $value;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param  string                 $id        Identifier of the entry to look for.
     * @param  bool                   $forceNew  True to force creation and return of a new instance.
     * @param  \UnitEnum|string|null  $tag       The tag of service.
     *
     * @return mixed Entry.
     * @throws DefinitionNotFoundException
     * @throws DependencyResolutionException
     */
    public function get(string $id, bool $forceNew = false, \UnitEnum|string|null $tag = null): mixed
    {
        $definition = $this->getDefinition($id, $tag);

        if ($definition === null) {
            throw new DefinitionNotFoundException(
                sprintf('Key %s has not been registered with the container.', $id),
            );
        }

        if ($forceNew) {
            $definition->reset();
        }

        // Detect cache outside of definition to save some performance.
        $cache = $definition->getCache($tag);

        if ($cache !== null) {
            return $cache;
        }

        try {
            return $definition->resolve($this, tag: $tag);
        } catch (ContainerExceptionInterface $e) {
            throw new DependencyResolutionException(
                "Error when resolving $id: {$e->getMessage()}",
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * @param  string|callable|DefinitionInterface|ValueReference  $source
     * @param  array                                               $args
     * @param  DIOptions|int                                       $options
     * @param  \UnitEnum|string|null                               $tag
     *
     * @return  mixed|object|string
     * @throws DefinitionNotFoundException
     * @throws DefinitionResolveException
     * @throws DependencyResolutionException
     * @throws ReflectionException
     */
    public function resolve(
        mixed $source,
        array $args = [],
        DIOptions|int $options = new DIOptions(),
        \UnitEnum|string|null $tag = null
    ): mixed {
        $options = DIOptions::wrap($options);

        if ($source === null) {
            throw new InvalidArgumentException(
                sprintf(
                    '%s() Argument #1 (source) can not be NULL',
                    __METHOD__,
                ),
            );
        }

        if ($source instanceof RawWrapper) {
            return $source();
        }

        if ($source instanceof ValueReference) {
            // Use Container::getParam() to support get value from parent.
            $source = $this->getParam($source->getPath(), $source->getDelimiter() ?? '.');
        }

        if (is_string($source) && !class_exists($source)) {
            $value = $this->getParam($source);

            if ($value !== null) {
                $source = $value;
            }
        }

        if (is_callable($source) || (is_string($source) && !$this->has($source))) {
            return $this->newInstance($source, [...$args, 'tag' => $tag], $options);
        }

        if (is_string($source) && $this->has($source, $tag)) {
            $definition = $this->getDefinition($source, $tag);
        } else {
            $definition = $source;
        }

        if ($definition instanceof DefinitionInterface) {
            return $definition->resolve($this, $args, $tag);
        }

        return $this->get($source, tag: $tag);
    }

    public function resolveParam(string $param, array $args = [], DIOptions|int $options = new DIOptions()): mixed
    {
        return $this->resolve($param, $args, $options);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param  string                 $id   Identifier of the entry to look for.
     * @param  \UnitEnum|string|null  $tag  The tag of service.
     *
     * @return bool
     */
    public function has(string $id, \UnitEnum|string|null $tag = null): bool
    {
        return $this->getDefinition($id, $tag) !== null;
    }

    public function hasCached(string $id, \UnitEnum|string|null $tag = null): bool
    {
        return $this->findDefinition($id, false, $tag)?->getCache() !== null;
    }

    /**
     * Remove an item from container.
     *
     * @param  string  $id  Name of the dataStore key to get.
     *
     * @return  static  This object for chaining.
     *
     * @since   2.1
     */
    public function remove(string $id, \UnitEnum|string|null $tag = null): static
    {
        $id = $this->resolveAlias($id);

        $id = static::toTaggedId($id, $tag);

        unset($this->storage[$id]);

        return $this;
    }

    protected static function toTaggedId(string $id, \UnitEnum|string|null $tag = null): string
    {
        if ($tag instanceof \UnitEnum) {
            $tag = $tag::class . '::' . $tag->name;
        }

        if ($tag) {
            return $id . '@' . $tag;
        }

        return $id;
    }

    /**
     * Fork an instance to a new key.
     *
     * @param  string  $id        Origin key.
     * @param  string  $newId     New key.
     * @param  bool    $forceNew  Force new.
     *
     * @return  mixed  Forked instance.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @since  2.0.7
     */
    public function fork(string $id, string $newId, bool $forceNew = false, \UnitEnum|string|null $tag = null): mixed
    {
        $raw = clone $this->findDefinition($id, true, $tag);

        $taggedId = static::toTaggedId($newId, $tag);

        $this->storage[$taggedId] = $raw;

        return $this->get($newId, $forceNew, $tag);
    }

    /**
     * Get the raw data assigned to a key.
     *
     * @param  string                 $id   The key for which to get the stored item.
     * @param  \UnitEnum|string|null  $tag  The tag of service.
     *
     * @return  ?StoreDefinitionInterface
     *
     * @since   2.0
     */
    public function getDefinition(string $id, \UnitEnum|string|null $tag = null): ?StoreDefinitionInterface
    {
        return $this->findDefinition($id, true, $tag);
    }

    protected function findDefinition(
        string $id,
        bool $serviceAutowire = false,
        \UnitEnum|string|null $tag = null,
    ): ?StoreDefinitionInterface {
        $id = $this->resolveAlias($id);

        if ($tag) {
            $taggedId = static::toTaggedId($id, $tag);

            if ($this->storage[$taggedId] ?? null) {
                return $this->storage[$taggedId];
            }
        }

        if ($this->storage[$id] ?? null) {
            return $this->storage[$id];
        }

        // If has #[Service] attribute, we will register a new definition instantly.
        if (
            $serviceAutowire
            && class_exists($id)
            && $service = DependencyResolver::getAttributeFromReflection(new ReflectionClass($id), Service::class)
        ) {
            if (($service->tag || $tag) && $service->tag !== $tag) {
                // If tag is not matched, we will not register this service.
                return null;
            }

            $definition = new StoreDefinition($id, $this->newInstance($id));
            $definition->providedIn($service->providedIn);

            $this->setDefinition($id, $definition, $tag);

            return $definition;
        }

        // Find from parent
        if ($this->parent instanceof static) {
            $parentDefinition = $this->parent->findDefinition($id, $serviceAutowire, $tag);

            // Store parent definition as self
            if ($parentDefinition) {
                $parentDefinition = clone $parentDefinition;

                if ($parentDefinition->getOptions()->isolation) {
                    $parentDefinition->reset();
                }

                $this->setDefinition($id, $parentDefinition, $tag);

                return $parentDefinition;
            }
        }

        return null;
    }

    public function clear(): void
    {
        $this->storage = [];
        $this->builders = [];
        $this->aliases = [];
        $this->parameters->reset();
    }

    public function clearCache(?string $id = null, \UnitEnum|string|null $tag = null): void
    {
        if ($id !== null) {
            $this->findDefinition($id, false, $tag)?->reset();

            return;
        }

        foreach ($this->storage as $storage) {
            $storage->reset();
        }
    }

    /**
     * Bind a class or key to another instance, container will return instance if it has been set
     * or created, otherwise it will create new one.
     *
     * @param  string                 $id
     * @param  mixed                  $value
     * @param  DIOptions|int          $options
     * @param  \UnitEnum|string|null  $tag
     *
     * @return StoreDefinitionInterface
     *
     * @throws DefinitionException
     * @since   3.0
     */
    public function bind(
        string $id,
        mixed $value,
        DIOptions|int $options = new DIOptions(),
        \UnitEnum|string|null $tag = null,
    ): StoreDefinitionInterface {
        $options = DIOptions::wrap($options);

        $value = static fn(Container $container, \UnitEnum|string|null $tag = null) => $container->newInstance(
            $value,
            compact('tag'),
            $options
        );

        return $this->set($id, $value, $options, $tag);
    }

    /**
     * @param  string                 $id
     * @param  mixed                  $value
     * @param  DIOptions|int          $options
     * @param  \UnitEnum|string|null  $tag
     *
     * @return StoreDefinitionInterface
     *
     * @throws DefinitionException
     * @since   3.0
     */
    public function bindShared(
        string $id,
        mixed $value,
        DIOptions|int $options = new DIOptions(),
        \UnitEnum|string|null $tag = null,
    ): StoreDefinitionInterface {
        $options = DIOptions::wrap($options);
        $options->shared = true;

        return $this->bind($id, $value, $options, $tag);
    }

    /**
     * @param  string                 $class
     * @param  Closure|null           $extend
     * @param  int                    $options
     * @param  \UnitEnum|string|null  $tag
     *
     * @return StoreDefinitionInterface
     *
     * @throws DefinitionException
     * @since   3.0
     */
    public function prepareObject(
        string $class,
        ?Closure $extend = null,
        DIOptions|int $options = new DIOptions(),
        \UnitEnum|string|null $tag = null,
    ): StoreDefinitionInterface {
        $options = DIOptions::wrap($options);

        $handler = static fn(Container $container) => $container->newInstance($class, [], $options);

        $definition = $this->set($class, $handler, $options, $tag);

        if (is_callable($extend)) {
            $this->extend($class, $extend, $tag);
        }

        return $definition;
    }

    /**
     * @param  string                 $class
     * @param  Closure|null           $extend
     * @param  int                    $options
     * @param  \UnitEnum|string|null  $tag
     *
     * @return StoreDefinitionInterface
     *
     * @throws DefinitionException
     * @since   3.0
     */
    public function prepareSharedObject(
        string $class,
        ?Closure $extend = null,
        DIOptions|int $options = new DIOptions(),
        \UnitEnum|string|null $tag = null,
    ): StoreDefinitionInterface {
        $options = DIOptions::wrap($options);
        $options->shared = true;

        return $this->prepareObject($class, $extend, $options, $tag);
    }

    /**
     * Extend a defined service Closure by wrapping the existing one with a new Closure.  This
     * works very similar to a decorator pattern.  Note that this only works on service Closures
     * that have been defined in the current Provider, not parent providers.
     *
     * @param  string                 $id       The unique identifier for the Closure or property.
     * @param  Closure                $closure  A Closure to wrap the original service Closure.
     * @param  \UnitEnum|string|null  $tag
     *
     * @return  static
     *
     * @since   2.0
     */
    public function extend(string $id, Closure $closure, \UnitEnum|string|null $tag = null): static
    {
        $this->extends[$id] ??= [];
        $this->extends[$id][] = $closure;

        if ($this->hasCached($id, $tag)) {
            $this->modify($id, $closure, $tag);
        }

        return $this;
    }

    public function subscribe(
        string $id,
        object|array $subscriber,
        ?int $priority = null,
        \UnitEnum|string|null $tag = null
    ): static {
        return $this->extend(
            $id,
            function (object $instance) use ($priority, $subscriber) {
                if (
                    $instance instanceof SubscribableListenerProviderInterface
                    || $instance instanceof EventListenableInterface
                ) {
                    if (is_object($subscriber)) {
                        $instance->subscribe($subscriber, $priority);
                    } else {
                        if (array_is_list($subscriber)) {
                            $subscriber = [$subscriber[0] => $subscriber[1]];
                        }

                        foreach ($subscriber as $event => $handler) {
                            $instance->on($event, $handler, $priority);
                        }
                    }
                }

                return $instance;
            },
            $tag
        );
    }

    /**
     * @param  string  $id
     *
     * @return  array<callable>
     */
    public function findExtends(string $id): array
    {
        $nid = $id;

        $extends = [];

        if ($this->parent) {
            $extends[] = $this->parent->findExtends($id);
        }

        if (isset($this->extends[$id])) {
            $extends[] = $this->extends[$id];
        }

        while (isset($this->aliases[$nid])) {
            $nid = $this->aliases[$nid];

            if (isset($this->extends[$nid])) {
                $extends[] = $this->extends[$nid];
            }
        }

        return array_merge(...$extends);
    }

    public function modify(string $id, Closure $closure, \UnitEnum|string|null $tag = null): StoreDefinitionInterface
    {
        $definition = $this->getDefinition($id, $tag);

        if ($definition === null) {
            throw new UnexpectedValueException(
                sprintf('The requested id "%s" does not exist to modify.', static::toTaggedId($id, $tag)),
            );
        }

        $target = $definition->resolve($this, tag: $tag);

        $target = $closure($target, $this, $tag);

        $definition = new StoreDefinition(
            $id,
            $target,
            $definition->getOptions(),
            tag: $tag,
        );

        return $this->setDefinition($id, $definition, $tag);
    }

    /**
     * New instance and save it.
     *
     * @param  string                 $class
     * @param  array                  $args
     * @param  DIOptions|int          $options
     * @param  \UnitEnum|string|null  $tag
     *
     * @return mixed
     * @throws DefinitionException
     * @throws DefinitionNotFoundException
     * @throws DependencyResolutionException
     * @since   3.0
     */
    public function createObject(
        string $class,
        array $args = [],
        DIOptions|int $options = new DIOptions(),
        \UnitEnum|string|null $tag = null,
    ): mixed {
        $callback = fn(Container $container) => $container->newInstance($class, $args, $options);

        $this->set($class, $callback, $options, $tag);

        // Get again to ensure we run the extends.
        return $this->get($class, tag: $tag);
    }

    /**
     * @param  string                 $class
     * @param  array                  $args
     * @param  DIOptions|int          $options
     * @param  \UnitEnum|string|null  $tag
     *
     * @return mixed
     *
     * @throws DefinitionException
     * @throws DefinitionNotFoundException
     * @throws DependencyResolutionException
     * @since   3.0
     */
    public function createSharedObject(
        string $class,
        array $args = [],
        DIOptions|int $options = new DIOptions(),
        \UnitEnum|string|null $tag = null,
    ): mixed {
        $options = DIOptions::wrap($options);
        $options->shared = true;

        return $this->createObject($class, $args, $options, $tag);
    }

    /**
     * Execute a callable with dependencies.
     *
     * @param  mixed          $callable  Do not use callable hint, will check callable after context bounded.
     * @param  array          $args
     * @param  object|null    $context
     * @param  DIOptions|int  $options
     *
     * @return mixed
     *
     * @throws ContainerExceptionInterface
     * @throws DefinitionResolveException
     * @throws DependencyResolutionException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function call(
        mixed $callable,
        array $args = [],
        ?object $context = null,
        DIOptions|int $options = new DIOptions()
    ): mixed {
        $options = DIOptions::wrap($options);

        return $this->dependencyResolver->call($callable, $args, $context, $options);
    }

    /**
     * Execute a callable with dependencies.
     *
     * Can wrap callable with `\Windwalker\DI\callback($callable, $context, $options)`.
     *
     * @param  callable  $callable
     * @param  mixed     ...$args
     *
     * @return  mixed
     *
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     */
    public function execute(callable $callable, mixed ...$args): mixed
    {
        if ($callable instanceof CallbackWrapper) {
            $context = $callable->context;
            $options = $callable->options;
            $callable = $callable->callable;
        }

        return $this->call($callable, $args, $context ?? null, $options ?? 0);
    }

    /**
     * whenCreating
     *
     * @param  string  $class
     *
     * @return  ObjectBuilderDefinition
     */
    public function whenCreating(string $class): ObjectBuilderDefinition
    {
        $builder = $this->builders[$class] ??= new ObjectBuilderDefinition($class);

        // if (!$this->has($class)) {
        //     $this->setDefinition($class, new ObjectBuilderDefinition($builder));
        // }

        return $builder;
    }

    /**
     * @param  mixed          $class
     * @param  array          $args
     * @param  DIOptions|int  $options
     *
     * @return  mixed|object
     * @throws DefinitionResolveException
     * @throws ReflectionException
     */
    public function newInstance(mixed $class, array $args = [], DIOptions|int $options = new DIOptions()): mixed
    {
        if (is_string($class)) {
            $options = DIOptions::wrap($options);

            $class = $this->resolveAliasDeep($class);

            if (
                $this->dependencyResolver->canLazy($ref = new \ReflectionClass($class), $options)
                && !$this->dependencyResolver::isInternal($ref)
            ) {
                return $ref->newLazyProxy(
                    fn() => $this->dependencyResolver->newInstance($class, $args, $options)
                );
            }
        }

        return $this->dependencyResolver->newInstance($class, $args, $options);
    }

    /**
     * Register a service provider to the container.
     *
     * @param  ServiceProviderInterface  $provider  The service provider to register.w
     *
     * @return  static  This object for chaining.
     *
     * @since   2.0
     */
    public function registerServiceProvider(ServiceProviderInterface $provider): static
    {
        $provider->register($this);

        return $this;
    }

    /**
     * Create an alias for a given key for easy access.
     *
     * @param  string  $alias  The alias name
     * @param  string  $id     The key to alias
     *
     * @return  static  This object for chaining.
     *
     * @since   2.0
     */
    public function alias(string $alias, string $id): static
    {
        $this->aliases[$alias] = $id;

        return $this;
    }

    /**
     * Search the aliases property for a matching alias key.
     *
     * @param  string  $id  The key to search for.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function resolveAlias(string $id): string
    {
        while (isset($this->aliases[$id])) {
            $id = $this->aliases[$id];
        }

        return $id;
    }

    protected function resolveAliasDeep(string $id): string
    {
        $id = $this->resolveAlias($id);

        if ($this->parent) {
            $id = $this->parent->resolveAliasDeep($id);
        }

        return $id;
    }

    /**
     * Remove an alias.
     *
     * @param  string  $alias  The alias name to remove.
     *
     * @return  static Support chaining.
     *
     * @since  3.2
     */
    public function removeAlias(string $alias): static
    {
        unset($this->aliases[$alias]);

        return $this;
    }

    /**
     * Retrieve an external iterator
     *
     * @return Traversable An instance of an object implementing Iterator or Traversable
     *
     * @since   2.1
     */
    public function &getIterator(): Generator
    {
        foreach ($this->storage as $id => &$definition) {
            yield $id => $definition;
        }
    }

    /**
     * Create a child Container with a new property scope that
     * that has the ability to access the parent scope when resolving.
     *
     * @return  static  The new container object.
     *
     * @throws DefinitionException
     * @since   2.0
     */
    public function createChild(): static
    {
        return new static($this);
    }

    /**
     * getParents
     *
     * @return  Container[]
     */
    public function getParents(): array
    {
        $parents = [];

        $parent = $this->getParent();

        while ($parent) {
            $parents[] = $parent;
            $parent = $this->getParent();
        }

        return $parents;
    }

    /**
     * @return Container|null
     */
    public function getParent(): ?Container
    {
        return $this->parent;
    }

    /**
     * @param  Container|null  $parent
     *
     * @return  static  Return self to support chaining.
     */
    public function setParent(?Container $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Extract closure as pure value.
     *
     * @param  mixed  $value
     *
     * @return  mixed
     *
     * @throws ReflectionException
     */
    public function extractValue(mixed $value): mixed
    {
        if ($value instanceof Closure) {
            return $this->call($value);
        }

        return $value;
    }

    /**
     * getParam
     *
     * @param  string  $path
     * @param  string  $delimiter
     *
     * @return  mixed
     */
    public function getParam(string $path, string $delimiter = '.'): mixed
    {
        $value = $this->getParameters()->getDeep($path, $delimiter);

        if ($value === null && $this->parent) {
            $value = $this->parent->getParam($path, $delimiter);
        }

        return $value;
    }

    public function loadParameters(mixed $source, ?string $format = null, array $options = []): Parameters
    {
        $this->parameters = $this->parameters->load($source, $format, $options);

        return $this->parameters;
    }

    /**
     * @return Parameters
     */
    public function getParameters(): Parameters
    {
        return $this->parameters;
    }

    /**
     * @param  Parameters|array  $parameters
     *
     * @return  static  Return self to support chaining.
     */
    public function setParameters(Parameters|array $parameters): static
    {
        $this->parameters = Parameters::wrap($parameters);

        return $this;
    }

    public function mergeParameters(?string $path, array $data, MergeOptions|int $options = new MergeOptions()): void
    {
        $options = MergeOptions::wrap($options);

        $params = $path === null ? $this->getParameters() : $this->getParameters()->proxy($path);
        $override = $options->override;
        $recursive = $options->recursive;
        $merge = $recursive
            ? [Arr::class, 'mergeRecursive']
            : 'array_merge';

        // $params->merge();

        $params->transform(
            function ($storage) use ($override, $merge, $data) {
                if ($override) {
                    return $merge($storage, $data);
                }

                return $merge($data, $storage);
            },
        );
    }

    /**
     * Count elements of an object
     * @return int The custom count as an integer.
     * The return value is cast to an integer.
     */
    public function count(): int
    {
        return count($this->storage);
    }

    /**
     * @return DIOptions
     */
    public function getOptions(): DIOptions
    {
        return $this->options;
    }

    /**
     * @param  int|DIOptions  $options
     *
     * @return  static  Return self to support chaining.
     */
    public function setOptions(int|DIOptions $options): static
    {
        $this->options = DIOptions::wrap($options);

        return $this;
    }

    /**
     * @return AttributesResolver
     */
    public function getAttributesResolver(): AttributesResolver
    {
        return $this->attributesResolver;
    }

    /**
     * Returns whether the requested key exists
     *
     * @param  mixed  $key
     *
     * @return bool
     */
    public function offsetExists(mixed $key): bool
    {
        return $this->has($key);
    }

    /**
     * Returns the value at the specified key
     *
     * @param  mixed  $key
     *
     * @return mixed
     * @throws ContainerExceptionInterface
     */
    public function &offsetGet(mixed $key): mixed
    {
        $item = $this->get($key);

        return $item;
    }

    /**
     * Sets the value at the specified key to value
     *
     * @param  mixed  $key
     * @param  mixed  $value
     *
     * @return void
     * @throws DefinitionException
     */
    public function offsetSet(mixed $key, mixed $value): void
    {
        $this->set($key, $value);
    }

    /**
     * Unsets the value at the specified key
     *
     * @param  mixed  $key
     *
     * @return void
     */
    public function offsetUnset(mixed $key): void
    {
        $this->remove($key);
    }

    /**
     * @return DependencyResolver
     */
    public function getDependencyResolver(): DependencyResolver
    {
        return $this->dependencyResolver;
    }

    /**
     * @param  DependencyResolver  $dependencyResolver
     *
     * @return  static  Return self to support chaining.
     */
    public function setDependencyResolver(DependencyResolver $dependencyResolver): static
    {
        $this->dependencyResolver = $dependencyResolver;

        return $this;
    }

    /**
     * @param  AttributesResolver  $attributesResolver
     *
     * @return  static  Return self to support chaining.
     */
    public function setAttributesResolver(AttributesResolver $attributesResolver): static
    {
        $attributesResolver->setContainer($this);
        $this->attributesResolver = $attributesResolver;

        return $this;
    }

    public function dump(): array
    {
        return $this->storage;
    }

    public function dumpCached(): array
    {
        $cached = [];

        foreach ($this->storage as $key => $store) {
            if ($store->getCache()) {
                $cached[$key] = $store->getCache();
            }
        }

        return $cached;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    public function getAliases(): array
    {
        return $this->aliases;
    }
}
