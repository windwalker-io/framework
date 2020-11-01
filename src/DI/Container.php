<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\DI;

use Doctrine\Common\Annotations\AnnotationException;
use PhpDocReader\PhpDocReader;
use Psr\Container\ContainerInterface;
use Windwalker\DI\Annotation\AnnotationRegistry;
use Windwalker\DI\Annotation\Inject;
use Windwalker\DI\Exception\DependencyResolutionException;
use Windwalker\Structure\Structure;
use Windwalker\Structure\ValueReference;

/**
 * The DI Container.
 *
 * @since 2.0
 */
class Container implements ContainerInterface, \ArrayAccess, \IteratorAggregate, \Countable
{
    public const FORCE_NEW = true;

    /**
     * Property children.
     *
     * @var  Container[]
     */
    protected $children = [];

    /**
     * Holds the key aliases.
     *
     * @var    array $aliases
     * @since  2.0
     */
    protected $aliases = [];

    /**
     * Holds the keys, their callbacks, and whether or not
     * the item is meant to be a shared resource.
     *
     * @var    DataStore[]
     * @since  2.0
     */
    protected $dataStore = [];

    /**
     * Parent for hierarchical containers.
     *
     * @var    Container
     * @since  2.0
     */
    protected $parent;

    /**
     * Property args.
     *
     * @var    array
     *
     * @since  3.0
     */
    protected $args = [];

    /**
     * Property AnnotationRegistry.
     *
     * @var AnnotationRegistry
     */
    protected $annotationRegistry;

    /**
     * Property docReader.
     *
     * @var PhpDocReader
     */
    protected $docReader;

    /**
     * Property parameters.
     *
     * @var  Structure
     */
    protected $parameters;

    /**
     * Create class meta.
     *
     * @param string|callable $class
     * @param array           $args
     *
     * @return  ClassMeta
     *
     * @since  3.5.1
     */
    public static function meta($class, array $args = []): ClassMeta
    {
        $meta = new ClassMeta($class);

        if ($args !== []) {
            $meta->setArguments($args);
        }

        return $meta;
    }

    /**
     * Wrap as raw.
     *
     * @param mixed $value
     *
     * @return  RawWrapper
     *
     * @since  3.5.1
     */
    public static function raw($value): RawWrapper
    {
        return new RawWrapper($value);
    }

    /**
     * Create parameter ref.
     *
     * @param string      $path
     * @param string|null $separator
     *
     * @return  ValueReference
     *
     * @since  3.5.1
     */
    public static function ref(string $path, string $separator = null): ValueReference
    {
        return new ValueReference($path, $separator);
    }

    /**
     * Constructor for the DI Container
     *
     * @param   Container   $parent   Parent for hierarchical containers.
     * @param   Container[] $children Children Containers.
     *
     * @since   2.0
     */
    public function __construct(Container $parent = null, array $children = [])
    {
        $this->parent   = $parent;
        $this->children = $children;
        $this->parameters = new Structure();

        // Load Inject Annotation first to make sure AnnotationReader can autoload it.
        new Inject();
    }

    /**
     * Create an alias for a given key for easy access.
     *
     * @param   string $alias The alias name
     * @param   string $key   The key to alias
     *
     * @return  static  This object for chaining.
     *
     * @since   2.0
     */
    public function alias($alias, $key)
    {
        $this->aliases[$alias] = $key;

        return $this;
    }

    /**
     * Remove an alias.
     *
     * @param string $alias The alias name to remove.
     *
     * @return  static Support chaining.
     *
     * @since  3.2
     */
    public function removeAlias($alias)
    {
        if (array_key_exists($alias, $this->aliases)) {
            unset($this->aliases[$alias]);
        }

        return $this;
    }

    /**
     * Search the aliases property for a matching alias key.
     *
     * @param   string $key The key to search for.
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function resolveAlias($key)
    {
        while (isset($this->aliases[$key])) {
            $key = $this->aliases[$key];
        }

        return $key;
    }

    /**
     * Bind a class or key to another instance, container will return instance if it has been set
     * or created, otherwise it will create new one.
     *
     * @param   string $name
     * @param   mixed  $value
     * @param   bool   $shared
     * @param   bool   $protected
     *
     * @return  static
     *
     * @since   3.0
     */
    public function bind($name, $value, $shared = false, $protected = false)
    {
        if (is_string($value)) {
            $value = function (Container $container) use ($value) {
                // We must check the keys exists or not, if exists, just get it instead new one.
                if ($container->exists($value)) {
                    return $container->get($value);
                }

                return $container->newInstance($value);
            };
        }

        return $this->set($name, $value, $shared, $protected);
    }

    /**
     * bindShared
     *
     * @param string $name
     * @param mixed  $value
     * @param bool   $protected
     *
     * @return  static
     *
     * @since   3.0
     */
    public function bindShared($name, $value, $protected = false)
    {
        return $this->bind($name, $value, true, $protected);
    }

    /**
     * prepareObject
     *
     * @param string   $class
     * @param callable $extend
     * @param bool     $shared
     * @param bool     $protected
     *
     * @return static
     *
     * @since   3.0
     */
    public function prepareObject($class, $extend = null, $shared = false, $protected = false)
    {
        $handler = function (Container $container) use ($class) {
            return $container->newInstance($class);
        };

        $this->set($class, $handler, $shared, $protected);

        if (is_callable($extend)) {
            $this->extend($class, $extend);
        }

        return $this;
    }

    /**
     * prepareSharedObject
     *
     * @param string   $class
     * @param callable $extend
     * @param bool     $protected
     *
     * @return  static
     *
     * @since   3.0
     */
    public function prepareSharedObject($class, $extend = null, $protected = false)
    {
        return $this->prepareObject($class, $extend, true, $protected);
    }

    /**
     * createObject
     *
     * @param string $class
     * @param array  $args
     * @param bool   $shared
     * @param bool   $protected
     *
     * @return  mixed
     * @since   3.0
     */
    public function createObject($class, array $args = [], $shared = false, $protected = false)
    {
        $callback = function (Container $container) use ($class, $args) {
            return $container->newInstance($class, $args);
        };

        return $this->set($class, $callback, $shared, $protected)->get($class);
    }

    /**
     * createSharedObject
     *
     * @param string $class
     * @param array  $args
     * @param bool   $protected
     *
     * @return  mixed
     *
     * @since   3.0
     */
    public function createSharedObject($class, array $args = [], $protected = false)
    {
        return $this->createObject($class, $args, true, $protected);
    }

    /**
     * Create an object of class $key;
     *
     * @param   string|ClassMeta|callable $class The class name to build.
     * @param   array                     $args  The default args if no class hint provided.
     *
     * @return mixed  Instance of class specified by $key with all dependencies injected.
     *                 Returns an object if the class exists and false otherwise
     *
     * @throws DependencyResolutionException
     * @throws \ReflectionException
     * @since   3.0
     */
    public function newInstance($class, array $args = [])
    {
        if ($class instanceof ClassMeta) {
            $class = function (self $container, array $args) use ($class) {
                return $class->setContainer($container)->newInstance($args);
            };
        }

        if (is_string($class)) {
            try {
                $reflection = new \ReflectionClass($class);
            } catch (\ReflectionException $e) {
                return false;
            }

            $constructor = $reflection->getConstructor();

            // If there are no parameters, just return a new object.
            if (null === $constructor) {
                $instance = new $class();
            } else {
                try {
                    $args = array_merge($this->whenCreating($class)->getArguments(), $args);

                    $newInstanceArgs = $this->getMethodArgs($constructor, $args);
                } catch (DependencyResolutionException $e) {
                    throw new DependencyResolutionException(
                        $e->getMessage() . ' / Target class: ' . $class,
                        $e->getCode(),
                        $e
                    );
                }

                // Create a callable for the dataStore
                $instance = $reflection->newInstanceArgs($newInstanceArgs);
            }
        } elseif (is_callable($class)) {
            $instance = $class($this, $args);

            $reflection = new \ReflectionClass($instance);
        } else {
            throw new \InvalidArgumentException(
                'New instance must get first argument as class name, callable or ClassMeta object.'
            );
        }

        $annotationRegistry = $this->getAnnotationRegistry();

        if (!$annotationRegistry::isSupported()) {
            return $instance;
        }

        $instance = $annotationRegistry->resolveClass($this, $instance);
        $instance = $annotationRegistry->resolveProperties($this, $instance);

        return $instance;
    }

    /**
     * Build an array of constructor parameters.
     *
     * @param   \ReflectionMethod $method Method for which to build the argument array.
     * @param   array             $args   The default args if class hint not provided.
     *
     * @return array Array of arguments to pass to the method.
     *
     * @throws DependencyResolutionException
     * @throws \ReflectionException
     * @since   2.0
     */
    protected function getMethodArgs(\ReflectionMethod $method, array $args = [])
    {
        $methodArgs = [];

        foreach ($method->getParameters() as $i => $param) {
            $dependency        = $param->getType();
            $dependencyVarName = $param->getName();

            // Prior (1): Handler ...$args
            if ($param->isVariadic()) {
                $trailing = [];

                foreach ($args as $key => $value) {
                    if (is_numeric($key)) {
                        $trailing[] = $this->resolveArgumentValue($value);
                    }
                }

                $trailing   = array_slice($trailing, $i);
                $methodArgs = array_merge($methodArgs, $trailing);
                continue;
            }

            // Prior (2): Argument with numeric keys.
            if (array_key_exists($i, $args)) {
                $methodArgs[] = $this->resolveArgumentValue($args[$i]);
                continue;
            }

            // Prior (3): Argument with named keys.
            if (array_key_exists($dependencyVarName, $args)) {
                $methodArgs[] = $this->resolveArgumentValue($args[$dependencyVarName]);

                continue;
            }

            // // Prior (4): Argument with numeric keys.
            $value = $this->resolveArgumentValue(
                $this->resolveParameterDependency($param, $args)
            );

            if ($value !== null) {
                $methodArgs[] = $value;
                continue;
            }

            if ($param->isOptional()) {
                // Finally, if there is a default parameter, use it.
                if ($param->isDefaultValueAvailable()) {
                    $methodArgs[] = $param->getDefaultValue();
                }

                continue;
            }

            // Couldn't resolve dependency, and no default was provided.
            throw new DependencyResolutionException(sprintf('Could not resolve dependency: $%s', $dependencyVarName));
        }

        return $methodArgs;
    }

    /**
     * resolveParameterDependency
     *
     * @param \ReflectionParameter $param
     * @param array                $args
     *
     * @return  mixed
     *
     * @throws DependencyResolutionException
     * @throws \ReflectionException
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function &resolveParameterDependency(\ReflectionParameter $param, array $args = [])
    {
        $nope = null;

        $type = $param->getType();
        $dependencyVarName = $param->getName();

        if (!$type) {
            return $nope;
        }

        if ($type instanceof \ReflectionUnionType) {
            $dependencies = $type->getTypes();
        } else {
            $dependencies = [$type];
        }

        foreach ($dependencies as $type) {
            $depObject           = null;
            $dependencyClassName = $type->getName();

            if (!class_exists($dependencyClassName) && !interface_exists($dependencyClassName)) {
                // Next dependency
                continue;
            }

            $dependency = new \ReflectionClass($dependencyClassName);

            // If the dependency class name is registered with this container or a parent, use it.
            if ($this->has($dependencyClassName)) {
                $depObject = $this->get($dependencyClassName);
            } elseif (array_key_exists($dependencyVarName, $args)) {
                // If an arg provided, use it.
                return $args[$dependencyVarName];
            } elseif (!$dependency->isAbstract()
                && !$dependency->isInterface()
                && !$dependency->isTrait()
            ) {
                // Otherwise we create this object recursive

                // Find child args if set
                if (isset($args[$dependencyClassName]) && is_array($args[$dependencyClassName])) {
                    $childArgs = $args[$dependencyClassName];
                } else {
                    $childArgs = [];
                }

                $depObject = $this->newInstance($dependencyClassName, $childArgs);
            }

            if ($depObject instanceof $dependencyClassName) {
                return $depObject;
            }
        }

        return $nope;
    }

    /**
     * resolveArgumentValue
     *
     * @param mixed $value
     *
     * @return  mixed
     *
     * @since  3.5.1
     */
    protected function resolveArgumentValue($value)
    {
        if ($value instanceof ClassMeta) {
            $value = $value->setContainer($this)->newInstance();
        } elseif ($value instanceof ValueReference) {
            $v = $value->get($this->getParameters());

            if ($v === null && $this->parent instanceof Container) {
                $v = $value->get($this->parent->getParameters());
            }

            $value = $v;
        } elseif ($value instanceof RawWrapper) {
            $value = $value->get();
        }

        return $value;
    }

    /**
     * Execute a callable with dependencies.
     *
     * @param callable $callable
     * @param array    $args
     * @param object   $context
     *
     * @return  mixed
     *
     * @throws DependencyResolutionException
     * @throws \ReflectionException
     * @throws AnnotationException
     */
    public function execute($callable, array $args = [], $context = null)
    {
        $object = null;
        $method = null;

        if ($callable instanceof \Closure) {
            $ref = new \ReflectionObject($callable);

            $args = $this->getMethodArgs($ref->getMethod('__invoke'), $args);
        } else {
            if (is_string($callable)) {
                $callable = explode('::', $callable);
            }

            [$object, $method] = $callable;

            $ref = new \ReflectionClass($object);

            $args = $this->getMethodArgs($ref->getMethod($method), $args);
        }

        $closure = function () use ($args, $callable) {
            switch (count($args)) {
                case 0:
                    return $callable();
                case 1:
                    return $callable($args[0]);
                case 2:
                    return $callable($args[0], $args[1]);
                case 3:
                    return $callable($args[0], $args[1], $args[2]);
                case 4:
                    return $callable($args[0], $args[1], $args[2], $args[3]);
                default:
                    return call_user_func_array($callable, $args);
            }
        };

        if ($context) {
            $closure = $closure->bindTo($context, $context);
        }

        if (AnnotationRegistry::isSupported() && $object !== null && is_object($object)) {
            $closure = $this->getAnnotationRegistry()->resolveMethod($this, $object, $method, $closure);
        }

        return $closure();
    }

    /**
     * Alias of execute().
     *
     * @param callable $callable
     * @param array    $args
     * @param object   $context
     *
     * @return  mixed
     * @throws DependencyResolutionException
     * @throws \ReflectionException
     */
    public function call($callable, array $args = [], $context = null)
    {
        return $this->execute($callable, $args, $context);
    }

    /**
     * whenCreating
     *
     * @param   string $class
     *
     * @return  ClassMeta
     */
    public function whenCreating($class)
    {
        if (!isset($this->args[$class])) {
            $this->args[$class] = new ClassMeta($class, $this);
        }

        return $this->args[$class];
    }

    /**
     * Create a child Container with a new property scope that
     * that has the ability to access the parent scope when resolving.
     *
     * @param   string $name The child name.
     *
     * @return  static  The new container object.
     *
     * @since   2.0
     */
    public function createChild($name = null)
    {
        $name = $name ?: md5(uniqid('windwalker-di', true));

        $this->children[$name] = $child = new static($this);

        $child->setAnnotationRegistry($this->getAnnotationRegistry());

        return $this->children[$name];
    }

    /**
     * Extend a defined service Closure by wrapping the existing one with a new Closure.  This
     * works very similar to a decorator pattern.  Note that this only works on service Closures
     * that have been defined in the current Provider, not parent providers.
     *
     * @param   string   $key      The unique identifier for the Closure or property.
     * @param   \Closure $callable A Closure to wrap the original service Closure.
     *
     * @return  static
     *
     * @since   2.0
     * @throws  \InvalidArgumentException
     */
    public function extend($key, $callable)
    {
        $store = $this->getRaw($key);

        if ($store === null) {
            throw new \UnexpectedValueException(sprintf('The requested key %s does not exist to extend.', $key));
        }

        if (!is_callable($callable)) {
            throw new \InvalidArgumentException('Argument 2 should be an callable.');
        }

        $closure = function ($container) use ($callable, $store) {
            return call_user_func($callable, $store->get($container), $container);
        };

        $this->set($key, $closure, $store->isShared());

        return $this;
    }

    /**
     * Method to set the key and callback to the dataStore array.
     *
     * @param   string  $key       Name of dataStore key to set.
     * @param   mixed   $value     Callable function to run or string to retrieve when requesting the specified $key.
     * @param   boolean $shared    True to create and store a shared instance.
     * @param   boolean $protected True to protect this item from being overwritten. Useful for services.
     *
     * @return  static  This object for chaining.
     *
     * @throws  \OutOfBoundsException  Thrown if the provided key is already set and is protected.
     *
     * @since   2.0
     */
    public function set($key, $value, $shared = false, $protected = false)
    {
        if (isset($this->dataStore[$key]) && $this->dataStore[$key]->isProtected()) {
            throw new \OutOfBoundsException(sprintf('Key %s is protected and can\'t be overwritten.', $key));
        }

        $this->dataStore[$key] = new DataStore($value, $shared, $protected);

        // 3.2 Remove alias
        $this->removeAlias($key);

        return $this;
    }

    /**
     * Convenience method for creating protected keys.
     *
     * @param   string   $key      Name of dataStore key to set.
     * @param   callable $callback Callable function to run when requesting the specified $key.
     * @param   bool     $shared   True to create and store a shared instance.
     *
     * @return  static  This object for chaining.
     *
     * @since   2.0
     */
    public function protect($key, $callback, $shared = false)
    {
        return $this->set($key, $callback, $shared, true);
    }

    /**
     * Convenience method for creating shared keys.
     *
     * @param   string         $key       Name of dataStore key to set.
     * @param   callable|mixed $callback  Callable function to run when requesting the specified $key.
     * @param   bool           $protected True to create and store a shared instance.
     *
     * @return  static  This object for chaining.
     *
     * @since   2.0
     */
    public function share($key, $callback, $protected = false)
    {
        return $this->set($key, $callback, true, $protected);
    }

    /**
     * Method to retrieve the results of running the $callback for the specified $key;
     *
     * @param   string  $key      Name of the dataStore key to get.
     * @param   boolean $forceNew True to force creation and return of a new instance.
     *
     * @return  mixed   Results of running the $callback for the specified $key.
     * @throws \UnexpectedValueException
     *
     * @since   2.0
     */
    public function get($key, $forceNew = false)
    {
        $store = $this->getRaw($key);

        if ($store === null) {
            throw new \UnexpectedValueException(sprintf('Key %s has not been registered with the container.', $key));
        }

        return $store->get($this, $forceNew);
    }

    /**
     * Remove an item from container.
     *
     * @param   string $key Name of the dataStore key to get.
     *
     * @return  static  This object for chaining.
     *
     * @since   2.1
     */
    public function remove($key)
    {
        $key = $this->resolveAlias($key);

        if (isset($this->dataStore[$key])) {
            unset($this->dataStore[$key]);
        }

        return $this;
    }

    /**
     * Method to check if specified dataStore key exists.
     *
     * @param   string $key Name of the dataStore key to check.
     *
     * @return  boolean  True for success
     *
     * @since   2.0
     */
    public function exists($key)
    {
        return (bool) $this->getRaw($key);
    }

    /**
     * Get the raw data assigned to a key.
     *
     * @param   string $key The key for which to get the stored item.
     *
     * @return  DataStore
     *
     * @since   2.0
     */
    protected function getRaw($key)
    {
        $key = $this->resolveAlias($key);

        if (isset($this->dataStore[$key])) {
            return $this->dataStore[$key];
        }

        if ($this->parent instanceof Container) {
            return $this->parent->getRaw($key);
        }

        return null;
    }

    /**
     * Method to force the container to return a new instance
     * of the results of the callback for requested $key.
     *
     * @param   string $key Name of the dataStore key to get.
     *
     * @return  mixed   Results of running the $callback for the specified $key.
     *
     * @since   2.0
     */
    public function getNewInstance($key)
    {
        return $this->get($key, true);
    }

    /**
     * Fork an instance to a new key.
     *
     * @param   string $key      Origin key.
     * @param   string $newKey   New key.
     * @param   bool   $forceNew Force new.
     *
     * @return  mixed  Forked instance.
     *
     * @since   2.0.7
     */
    public function fork($key, $newKey, $forceNew = false)
    {
        $raw = clone $this->getRaw($key);

        $this->dataStore[$newKey] = $raw;

        return $this->get($newKey, $forceNew);
    }

    /**
     * Register a service provider to the container.
     *
     * @param   ServiceProviderInterface $provider The service provider to register.w
     *
     * @return  static  This object for chaining.
     *
     * @since   2.0
     */
    public function registerServiceProvider(ServiceProviderInterface $provider)
    {
        $provider->register($this);

        return $this;
    }

    /**
     * Method to get property Parent
     *
     * @return  static  Parent container.
     *
     * @since  2.0.4
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Method to set property parent
     *
     * @param   Container $parent Parent container.
     *
     * @return  static  Return self to support chaining.
     *
     * @since  2.0.4
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * addChild
     *
     * @param string    $name
     * @param Container $container
     *
     * @return  static
     *
     * @since   2.1
     */
    public function addChild($name, Container $container)
    {
        $container->setParent($this);

        $this->children[$name] = $container;

        return $this;
    }

    /**
     * getChild
     *
     * @param   string  $name
     * @param   boolean $forceNew
     *
     * @return  static
     *
     * @since   2.1
     */
    public function getChild($name, $forceNew = false)
    {
        if (!isset($this->children[$name]) || $forceNew) {
            return $this->createChild($name);
        }

        return $this->children[$name];
    }

    /**
     * hasChild
     *
     * @param   string $name
     *
     * @return  boolean
     */
    public function hasChild($name)
    {
        return isset($this->children[$name]);
    }

    /**
     * removeChild
     *
     * @param   string $name
     *
     * @return  static
     *
     * @since   2.1
     */
    public function removeChild($name)
    {
        if (isset($this->children[$name])) {
            unset($this->children[$name]);
        }

        return $this;
    }

    /**
     * Method to get property Children
     *
     * @return  static[]
     *
     * @since   2.1
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Method to set property children
     *
     * @param   Container[] $children
     *
     * @return  static  Return self to support chaining.
     *
     * @since   2.1
     */
    public function setChildren(array $children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * Retrieve an external iterator
     *
     * @return \Traversable An instance of an object implementing Iterator or Traversable
     *
     * @since   2.1
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->dataStore);
    }

    /**
     * Is a property exists or not.
     *
     * @param   mixed $offset Offset key.
     *
     * @return  boolean
     *
     * @since   2.1
     */
    public function offsetExists($offset)
    {
        return $this->exists($offset);
    }

    /**
     * Get a property.
     *
     * @param   mixed $offset Offset key.
     *
     * @return  mixed  The value to return.
     *
     * @since   2.1
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Set a value to property.
     *
     * @param   mixed $offset Offset key.
     * @param   mixed $value  The value to set.
     *
     * @return  void
     *
     * @since   2.1
     */
    public function offsetSet($offset, $value)
    {
        $this->share($offset, $value);
    }

    /**
     * Unset a property.
     *
     * @param   mixed $offset Offset key to unset.
     *
     * @return  void
     *
     * @since   2.1
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * Count this object.
     *
     * @return  integer
     *
     * @since   2.1
     */
    public function count()
    {
        return count($this->dataStore);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($id)
    {
        return $this->exists($id);
    }

    /**
     * Method to get property AnnotationRegistry
     *
     * @return  AnnotationRegistry
     *
     * @since  3.5.19
     */
    public function getAnnotationRegistry(): AnnotationRegistry
    {
        if (!$this->annotationRegistry) {
            $this->annotationRegistry = new AnnotationRegistry();
        }

        return $this->annotationRegistry;
    }

    /**
     * Method to get property DocReader
     *
     * @return  PhpDocReader
     *
     * @since  3.4.4
     */
    public function getDocReader()
    {
        if (!$this->docReader) {
            $this->docReader = new PhpDocReader();
        }

        return $this->docReader;
    }

    /**
     * getParameter
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return  mixed
     *
     * @since  3.5.1
     */
    public function getParameter(string $key, $default = null)
    {
        return $this->parameters->get($key, $default);
    }

    /**
     * setParameter
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return  static
     *
     * @since  3.5.1
     */
    public function setParameter(string $key, $value): self
    {
        $this->parameters->set($key, $value);

        return $this;
    }

    /**
     * Method to get property Parameters
     *
     * @return  Structure
     *
     * @since  3.5.1
     */
    public function getParameters(): Structure
    {
        return $this->parameters;
    }

    /**
     * Method to set property parameters
     *
     * @param   Structure $parameters
     *
     * @return  static  Return self to support chaining.
     *
     * @since  3.5.1
     */
    public function setParameters(Structure $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Method to set property annotationRegistry
     *
     * @param AnnotationRegistry $annotationRegistry
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setAnnotationRegistry($annotationRegistry)
    {
        $this->annotationRegistry = $annotationRegistry;

        return $this;
    }
}
