<?php

declare(strict_types=1);

namespace Windwalker\DI;

use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;
use TypeError;
use UnexpectedValueException;
use Windwalker\DI\Attributes\Lazy;
use Windwalker\DI\Attributes\Factory;
use Windwalker\DI\Attributes\Service;
use Windwalker\DI\Definition\DefinitionInterface;
use Windwalker\DI\Definition\ObjectBuilderDefinition;
use Windwalker\DI\Exception\DefinitionResolveException;
use Windwalker\DI\Exception\DependencyResolutionException;
use Windwalker\Utilities\Reflection\ReflectionCallable;
use Windwalker\Utilities\Wrapper\RawWrapper;
use Windwalker\Utilities\Wrapper\ValueReference;

use function Windwalker\collect;
use function Windwalker\depth;

/**
 * The ObjectFactory class.
 */
class DependencyResolver
{
    protected Container $container;

    /**
     * DependencyResolver constructor.
     *
     * @param  Container  $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @throws DefinitionResolveException
     * @throws ReflectionException
     */
    public function newInstance(mixed $class, array $args = [], DIOptions|int $options = new DIOptions()): object
    {
        if ($class instanceof DefinitionInterface) {
            return $this->container->resolve($class);
        }

        $options = $this->mergeOptionsDefaults($options);

        if (is_string($class)) {
            /** @var DIOptions $options */
            $builder = fn(array $args, DIOptions $options) => $this->resolveMembersAttributes(
                $this->newInstanceByClassName($class, $args, $options),
                $options
            );

            if (!$options->ignoreAttributes) {
                $builder = $this->container->getAttributesResolver()
                    ->resolveClassCreate($class, $builder);
            }

            try {
                $instance = $builder($args, $options);
            } catch (ContainerExceptionInterface $e) {
                throw new DefinitionResolveException(
                    sprintf(
                        'Error when creating object %s: %s',
                        $class,
                        $e->getMessage(),
                    ),
                    $e->getCode(),
                    $e
                );
            }
        } elseif (is_callable($class)) {
            $instance = $this->container->call($class, $args, null, $options);

            if (
                $instance instanceof \Closure
                && new \ReflectionFunction($instance)->getAttributes(Factory::class) !== []
            ) {
                return $this->container->resolve($instance, $args, $options);
            }

            if (!is_object($instance)) {
                throw new UnexpectedValueException(
                    sprintf(
                        'Thr callback for creating instance must return an object, got %s.',
                        get_debug_type($instance)
                    )
                );
            }

            $instance = $this->resolveMembersAttributes($instance, $options);

            if (!$options->ignoreAttributes) {
                $instance = $this->container->getAttributesResolver()
                    ->decorateObject($instance);
            }

            // If is definition object, means this callable is a factory, let's resolve definition.
            if ($instance instanceof DefinitionInterface) {
                $instance = $this->container->resolve($instance, $args, $options);
            }

            return $instance;
        } else {
            throw new InvalidArgumentException(
                'New instance must get first argument as a class name, a callable or a DefinitionInterface object.'
            );
        }

        return $instance;
    }

    protected function resolveMembersAttributes(object $instance, DIOptions $options): object
    {
        if (!$options->ignoreAttributes) {
            $instance = $this->container->getAttributesResolver()
                ->resolveObjectMembers($instance);
        }

        return $instance;
    }

    /**
     * @throws ReflectionException
     * @throws DependencyResolutionException
     */
    public function newInstanceByClassName(
        string $class,
        array $args = [],
        DIOptions|int $options = new DIOptions()
    ): object {
        $options = $this->mergeOptionsDefaults($options);

        $reflection = new ReflectionClass($class);

        $constructor = $reflection->getConstructor();

        // If there are no parameters, just return a new object.
        if (null === $constructor) {
            return new $class();
        }

        // $parameters = $constructor->getParameters();
        //
        // if (array_diff(array_column($parameters, 'name'), array_keys($args)) === []) {
        //     return new $class(...$args);
        // }

        try {
            $args = array_merge($this->container->whenCreating($class)->resolveArguments($this->container), $args);

            $args = $this->getMethodArgs($constructor, $args, $options);
        } catch (ContainerExceptionInterface $e) {
            throw new DependencyResolutionException(
                $e->getMessage() . ' - Target class: ' . $class,
                $e->getCode(),
                $e
            );
        }

        try {
            // Create a callable for the dataStore
            return $reflection->newInstanceArgs($args);
        } catch (TypeError $e) {
            throw new DependencyResolutionException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Build an array of constructor parameters.
     *
     * @param  ReflectionFunctionAbstract  $method  Method for which to build the argument array.
     * @param  array                       $args    The default args if class hint not provided.
     * @param  DIOptions|int               $options
     *
     * @return array Array of arguments to pass to the method.
     *
     * @throws ContainerExceptionInterface
     * @throws DefinitionResolveException
     * @throws DependencyResolutionException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     * @since   2.0
     */
    protected function getMethodArgs(
        ReflectionFunctionAbstract $method,
        array $args = [],
        DIOptions|int $options = new DIOptions()
    ): array {
        $options = $this->mergeOptionsDefaults($options);

        $methodArgs = [];

        foreach ($method->getParameters() as $i => $param) {
            $dependencyVarName = $param->getName();

            // Prior (1): Handler ...$args
            if ($param->isVariadic()) {
                if ($param->getPosition() === 0) {
                    return $args;
                }

                $trailing = [];

                foreach ($args as $key => $v) {
                    if (is_numeric($key)) {
                        $trailing[] = &$this->resolveParameterValue(
                            $this->resolveParameterAttributes($v, $param),
                        );
                    }

                    unset($v);
                }

                $trailing = array_slice($trailing, $i);
                $methodArgs = static::merge($methodArgs, $trailing);
                continue;
            }

            // Prior (2): Argument with numeric keys.
            if (array_key_exists($i, $args)) {
                $methodArgs[$dependencyVarName] = &$this->resolveParameterValue(
                    $this->resolveParameterAttributes($args[$i], $param),
                );
                continue;
            }

            // Prior (3): Argument with named keys.
            if (array_key_exists($dependencyVarName, $args)) {
                $methodArgs[$dependencyVarName] = &$this->resolveParameterValue(
                    $this->resolveParameterAttributes($args[$dependencyVarName], $param),
                );
                continue;
            }

            // Prior (4): Argument with class type hints.
            $value = null;
            $value = &$this->resolveParameterAttributes($value, $param);

            if ($value !== null) {
                $value = &$this->resolveParameterValue($value);
            } else {
                // resolveParameterValue() will be called in resolveParameterDependency()
                $value = &$this->resolveParameterDependency($param, $args, $options);
            }

            if ($value !== null || $this->shouldSetNull($param)) {
                $methodArgs[$dependencyVarName] = &$value;

                unset($value);
                continue;
            }

            unset($value);

            if ($param->isOptional()) {
                // Finally, if there is a default parameter, use it.
                if ($param->isDefaultValueAvailable()) {
                    $methodArgs[$dependencyVarName] = $param->getDefaultValue();
                }

                continue;
            }

            throw new DependencyResolutionException(
                sprintf(
                    'Cannot resolve argument %s: $%s for %s%s()',
                    $i + 1,
                    $dependencyVarName,
                    $method instanceof ReflectionMethod ? $method->getDeclaringClass()->getName() . '::' : '',
                    $method->getShortName()
                )
            );
            // $methodArgs[$i] = null;
        }

        return $methodArgs;
    }

    protected function shouldSetNull(ReflectionParameter $param): bool
    {
        if (!$param->allowsNull()) {
            return false;
        }

        // If allow NULL but has default value, only set NULL when default is NULL.
        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue() === null;
        }

        return true;
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws DefinitionResolveException
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function &resolveParameterDependency(
        ReflectionParameter $param,
        array $args = [],
        DIOptions|int $options = new DIOptions()
    ): mixed {
        $options = $this->mergeOptionsDefaults($options);

        $autowire = $options->autowire;
        $nope = null;

        $type = $param->getType();
        // $dependencyVarName = $param->getName();

        // Currently we don't support IntersectionType
        if (!$type || $type instanceof \ReflectionIntersectionType) {
            return $nope;
        }

        if ($type instanceof ReflectionUnionType) {
            $dependencies = $type->getTypes();
        } else {
            /** @var array<\ReflectionNamedType> $dependencies */
            $dependencies = [$type];
        }

        foreach ($dependencies as $type) {
            // Currently we don't support IntersectionType
            if ($type instanceof \ReflectionIntersectionType) {
                continue;
            }

            $depObject = null;
            $dependencyClassName = $type->getName();

            if (
                !class_exists($dependencyClassName)
                && !interface_exists($dependencyClassName)
                && !enum_exists($dependencyClassName)
            ) {
                // Next dependency
                continue;
            }

            $dependency = new ReflectionClass($dependencyClassName);

            if (array_key_exists($dependencyClassName, $args)) {
                // If an arg provided, use it.
                return $args[$dependencyClassName];
            }

            $create = function &() use (
                $options,
                $param,
                $dependency,
                $autowire,
                $dependencyClassName
            ) {
                $depObject = null;

                if ($this->container->has($dependencyClassName)) {
                    // If the dependency class name is registered with this container or a parent, use it.
                    $depObject = $this->container->get($dependencyClassName);
                } elseif (
                    $autowire
                    && !$dependency->isAbstract()
                    && !$dependency->isInterface()
                    && !$dependency->isTrait()
                    && $dependency->isInstantiable()
                    && !$param->allowsNull()
                ) {
                    // Otherwise we create this object recursive

                    // Find child args if set
                    if (isset($args[$dependencyClassName]) && is_array($args[$dependencyClassName])) {
                        $childArgs = $args[$dependencyClassName];
                    } else {
                        $childArgs = [];
                    }

                    $dependencyClassAlias = $this->container->resolveAlias($dependencyClassName);

                    $depObject = $this->newInstance($dependencyClassAlias, $childArgs, $options);
                }

                return $depObject;
            };

            $ref = new ReflectionClass($dependencyClassName);

            if (
                ($this->canLazy($ref, $options) || $this->canLazy($param, $options))
                && !static::isInternal($ref)
            ) {
                $depObject = $ref->newLazyProxy(
                    function () use ($create) {
                        $value = $create();

                        return $this->resolveParameterValue($value);
                    }
                );

                return $depObject;
            }

            $depObject = &$create();

            if ($depObject instanceof $dependencyClassName) {
                return $this->resolveParameterValue($depObject);
            }
        }

        if (isset($depObject)) {
            $types = collect($dependencies)
                ->map(fn(ReflectionType $dep) => $dep->getName())
                ->implode('|');

            throw new DefinitionResolveException(
                sprintf(
                    'Unable to resolve %s argument #%s type: %s with %s given',
                    $param->getDeclaringClass()?->getName() ?? '*UnknownClass*',
                    $param->getPosition(),
                    (string) $types,
                    get_debug_type($depObject)
                )
            );
        }

        return $nope;
    }

    /**
     * Extract wrapper, resolve reference or create object by builder definitions.
     *
     * @param  mixed  $value
     *
     * @return mixed
     *
     * @since  3.5.1
     */
    public function &resolveParameterValue(mixed &$value): mixed
    {
        if ($value instanceof RawWrapper) {
            $value = $value();
        } else {
            if ($value instanceof ValueReference) {
                $v = $value($this->container->getParameters());

                if ($v === null && $this->container->getParent() instanceof Container) {
                    $v = $value($this->container->getParent()->getParameters());
                }

                $value = $v;
            }

            if ($value instanceof ObjectBuilderDefinition) {
                $value = $this->container->resolve($value);
            }
        }

        return $value;
    }

    public function &resolveParameterAttributes(
        mixed &$value,
        ReflectionParameter $param,
        DIOptions|int $options = new DIOptions()
    ): mixed {
        $options = $this->mergeOptionsDefaults($options);

        if (!$options->ignoreAttributes && $param->getAttributes()) {
            $value = &$this->container->getAttributesResolver()
                ->resolveParameter($value, $param);
        }

        return $value;
    }

    /**
     * Execute a callable with dependencies.
     *
     * @param  mixed        $callable  Do not use callable hint, will check callable after context bounded.
     * @param  array        $args
     * @param  object|null  $context
     * @param  int          $options
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
        $ref = new ReflectionCallable($callable);

        $options = $this->mergeOptionsDefaults($options);

        $closure = function (array $args, DIOptions $options) use ($context, $callable, $ref) {
            $args = $this->getMethodArgs($ref->getReflector(), $args, $options);

            if (!$options->ignoreAttributes) {
                $callable = $this->container->getAttributesResolver()
                    ->resolveCallable($callable, $context);
            }

            return $callable(...$args);
        };

        return $closure($args, $options);
    }

    public static function merge(array ...$args): array
    {
        return array_merge(...$args);
    }

    /**
     * @param  ReflectionClass  $dependency
     *
     * @return  bool
     */
    protected static function isService(ReflectionClass $dependency): bool
    {
        return $dependency->getAttributes(Service::class, \ReflectionAttribute::IS_INSTANCEOF) !== [];
    }

    public function mergeOptionsDefaults(int|DIOptions $options): DIOptions
    {
        return DIOptions::wrap($options)->withDefaults($this->container->getOptions());
    }

    public function canLazy(
        string|ReflectionClass|ReflectionParameter|\ReflectionProperty $classOrRef,
        DIOptions $options
    ): bool {
        $ref = is_string($classOrRef) ? new \ReflectionClass($classOrRef) : $classOrRef;
        $lazy = $options->lazy ?? $this->container->options->lazy;

        return ($lazy || static::getAttributeFromReflection($ref, Lazy::class));
    }

    public static function isInternal(string|\Reflector $class): bool
    {
        $ref = is_string($class) ? new \ReflectionClass($class) : $class;

        if (!$ref instanceof ReflectionClass) {
            return false;
        }

        while (!$ref->isInternal()) {
            $ref = $ref->getParentClass();

            if (!$ref) {
                return false;
            }

            if ($ref->isInternal()) {
                return true;
            }
        }

        return true;
    }

    /**
     * @template T
     *
     * @param  ReflectionClass|ReflectionParameter|\ReflectionProperty  $ref
     * @param  class-string<T>                                          $attr
     *
     * @return object|null
     */
    public static function getAttributeFromReflection(
        ReflectionClass|ReflectionParameter|\ReflectionProperty $ref,
        string $attr
    ): ?object {
        $attrs = $ref->getAttributes($attr, \ReflectionAttribute::IS_INSTANCEOF);

        if ($attrs === []) {
            return null;
        }

        return $attrs[0]->newInstance();
    }
}
