<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\DI;

use Windwalker\DI\Exception\DependencyResolutionException;

/**
 * The DI Container.
 *
 * @since 2.0
 */
class Container implements \ArrayAccess, \IteratorAggregate, \Countable
{
	const FORCE_NEW = true;

	/**
	 * Property children.
	 *
	 * @var  Container[]
	 */
	protected $children = array();

	/**
	 * Holds the key aliases.
	 *
	 * @var    array  $aliases
	 * @since  2.0
	 */
	protected $aliases = array();

	/**
	 * Holds the keys, their callbacks, and whether or not
	 * the item is meant to be a shared resource.
	 *
	 * @var    DataStore[]
	 * @since  2.0
	 */
	protected $dataStore = array();

	/**
	 * Parent for hierarchical containers.
	 *
	 * @var    Container
	 * @since  2.0
	 */
	protected $parent;

	/**
	 * Constructor for the DI Container
	 *
	 * @param   Container    $parent    Parent for hierarchical containers.
	 * @param   Container[]  $children  Children Containers.
	 *
	 * @since   2.0
	 */
	public function __construct(Container $parent = null, array $children = array())
	{
		$this->parent = $parent;
		$this->children = $children;
	}

	/**
	 * Create an alias for a given key for easy access.
	 *
	 * @param   string  $alias  The alias name
	 * @param   string  $key    The key to alias
	 *
	 * @return  Container  This object for chaining.
	 *
	 * @since   2.0
	 */
	public function alias($alias, $key)
	{
		$this->aliases[$alias] = $key;

		return $this;
	}

	/**
	 * Search the aliases property for a matching alias key.
	 *
	 * @param   string  $key  The key to search for.
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	protected function resolveAlias($key)
	{
		if (isset($this->aliases[$key]))
		{
			return $this->aliases[$key];
		}

		return $key;
	}

	/**
	 * Create an object of class $key;
	 *
	 * @param   string   $key     The class name to build.
	 * @param   boolean  $shared  True to create a shared resource.
	 *
	 * @return  mixed  Instance of class specified by $key with all dependencies injected.
	 *                 Returns an object if the class exists and false otherwise
	 *
	 * @since   2.0
	 */
	public function createObject($key, $shared = false)
	{
		try
		{
			$reflection = new \ReflectionClass($key);
		}
		catch (\ReflectionException $e)
		{
			return false;
		}

		$constructor = $reflection->getConstructor();

		// If there are no parameters, just return a new object.
		if (is_null($constructor))
		{
			$callback = function () use ($key)
			{
				return new $key;
			};
		}
		else
		{
			$newInstanceArgs = $this->getMethodArgs($constructor);

			// Create a callable for the dataStore
			$callback = function () use ($reflection, $newInstanceArgs)
			{
				return $reflection->newInstanceArgs($newInstanceArgs);
			};
		}

		return $this->set($key, $callback, $shared)->get($key);
	}

	/**
	 * Convenience method for creating a shared object.
	 *
	 * @param   string  $key  The class name to build.
	 *
	 * @return  object  Instance of class specified by $key with all dependencies injected.
	 *
	 * @since   2.0
	 */
	public function createSharedObject($key)
	{
		return $this->createObject($key, true);
	}

	/**
	 * Create a child Container with a new property scope that
	 * that has the ability to access the parent scope when resolving.
	 *
	 * @param   string  $name  The child name.
	 *
	 * @return  static  The new container object.
	 *
	 * @since   2.0
	 */
	public function createChild($name = null)
	{
		$name = $name ? : md5(uniqid());

		$this->children[$name] = new static($this);

		return $this->children[$name];
	}

	/**
	 * Extend a defined service Closure by wrapping the existing one with a new Closure.  This
	 * works very similar to a decorator pattern.  Note that this only works on service Closures
	 * that have been defined in the current Provider, not parent providers.
	 *
	 * @param   string    $key       The unique identifier for the Closure or property.
	 * @param   \Closure  $callable  A Closure to wrap the original service Closure.
	 *
	 * @return  Container
	 *
	 * @since   2.0
	 * @throws  \InvalidArgumentException
	 */
	public function extend($key, \Closure $callable)
	{
		$store = $this->getRaw($key);

		if (is_null($store))
		{
			throw new \UnexpectedValueException(sprintf('The requested key %s does not exist to extend.', $key));
		}

		$closure = function ($container) use($callable, $store)
		{
			return $callable($store->get($container), $container);
		};

		$this->set($key, $closure, $store->isShared());

		return $this;
	}

	/**
	 * Build an array of constructor parameters.
	 *
	 * @param   \ReflectionMethod  $method  Method for which to build the argument array.
	 *
	 * @return  array  Array of arguments to pass to the method.
	 *
	 * @since   2.0
	 * @throws  DependencyResolutionException
	 */
	protected function getMethodArgs(\ReflectionMethod $method)
	{
		$methodArgs = array();

		foreach ($method->getParameters() as $param)
		{
			$dependency = $param->getClass();
			$dependencyVarName = $param->getName();

			// If we have a dependency, that means it has been type-hinted.
			if (!is_null($dependency))
			{
				$dependencyClassName = $dependency->getName();

				// If the dependency class name is registered with this container or a parent, use it.
				if ($this->getRaw($dependencyClassName) !== null)
				{
					$depObject = $this->get($dependencyClassName);
				}
				else
				{
					$depObject = $this->createObject($dependencyClassName);
				}

				if ($depObject instanceof $dependencyClassName)
				{
					$methodArgs[] = $depObject;

					continue;
				}
			}

			// Finally, if there is a default parameter, use it.
			if ($param->isOptional())
			{
				if ($param->isDefaultValueAvailable())
				{
					$methodArgs[] = $param->getDefaultValue();
				}

				continue;
			}

			// Simple workaround for ArrayIterator::__construct() before PHP 7
			// @see  https://bugs.php.net/bug.php?id=70303
			if ($method->getDeclaringClass()->getName() == 'ArrayIterator' && version_compare(PHP_VERSION, '7', '<'))
			{
				continue;
			}

			// Couldn't resolve dependency, and no default was provided.
			throw new DependencyResolutionException(sprintf('Could not resolve dependency: %s', $dependencyVarName));
		}

		return $methodArgs;
	}

	/**
	 * Method to set the key and callback to the dataStore array.
	 *
	 * @param   string   $key        Name of dataStore key to set.
	 * @param   mixed    $value      Callable function to run or string to retrive when requesting the specified $key.
	 * @param   boolean  $shared     True to create and store a shared instance.
	 * @param   boolean  $protected  True to protect this item from being overwritten. Useful for services.
	 *
	 * @return  Container  This object for chaining.
	 *
	 * @throws  \OutOfBoundsException  Thrown if the provided key is already set and is protected.
	 *
	 * @since   2.0
	 */
	public function set($key, $value, $shared = false, $protected = false)
	{
		if (isset($this->dataStore[$key]) && $this->dataStore[$key]->isProtected())
		{
			throw new \OutOfBoundsException(sprintf('Key %s is protected and can\'t be overwritten.', $key));
		}

		$this->dataStore[$key] = new DataStore($value, $shared, $protected);

		return $this;
	}

	/**
	 * Convenience method for creating protected keys.
	 *
	 * @param   string    $key       Name of dataStore key to set.
	 * @param   callable  $callback  Callable function to run when requesting the specified $key.
	 * @param   bool      $shared    True to create and store a shared instance.
	 *
	 * @return  Container  This object for chaining.
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
	 * @param   string          $key        Name of dataStore key to set.
	 * @param   callable|mixed  $callback   Callable function to run when requesting the specified $key.
	 * @param   bool            $protected  True to create and store a shared instance.
	 *
	 * @return  Container  This object for chaining.
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

		if (is_null($store))
		{
			throw new \UnexpectedValueException(sprintf('Key %s has not been registered with the container.', $key));
		}

		return $store->get($this, $forceNew);
	}

	/**
	 * Remove an item from container.
	 *
	 * @param   string  $key  Name of the dataStore key to get.
	 *
	 * @return  static  This object for chaining.
	 *
	 * @since   2.1
	 */
	public function remove($key)
	{
		$key = $this->resolveAlias($key);

		if (isset($this->dataStore[$key]))
		{
			unset($this->dataStore[$key]);
		}

		return $this;
	}

	/**
	 * Method to check if specified dataStore key exists.
	 *
	 * @param   string  $key  Name of the dataStore key to check.
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
	 * @param   string  $key  The key for which to get the stored item.
	 *
	 * @return  DataStore
	 *
	 * @since   2.0
	 */
	protected function getRaw($key)
	{
		$key = $this->resolveAlias($key);

		if (isset($this->dataStore[$key]))
		{
			return $this->dataStore[$key];
		}
		elseif ($this->parent instanceof Container)
		{
			return $this->parent->getRaw($key);
		}

		return null;
	}

	/**
	 * Method to force the container to return a new instance
	 * of the results of the callback for requested $key.
	 *
	 * @param   string  $key  Name of the dataStore key to get.
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
	 * @param   string  $key       Origin key.
	 * @param   string  $newKey    New key.
	 * @param   bool    $forceNew  Force new.
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
	 * @param   ServiceProviderInterface  $provider  The service provider to register.w
	 *
	 * @return  Container  This object for chaining.
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
	 * @return  Container  Parent container.
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
	 * @param   Container $parent  Parent container.
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
	 * @param   string   $name
	 * @param   boolean  $forceNew
	 *
	 * @return  Container
	 *
	 * @since   2.1
	 */
	public function getChild($name, $forceNew = false)
	{
		if (!isset($this->children[$name]) || $forceNew)
		{
			return $this->createChild($name);
		}

		return $this->children[$name];
	}

	/**
	 * hasChild
	 *
	 * @param   string  $name
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
	 * @param   string  $name
	 *
	 * @return  static
	 *
	 * @since   2.1
	 */
	public function removeChild($name)
	{
		if (isset($this->children[$name]))
		{
			unset($this->children[$name]);
		}

		return $this;
	}

	/**
	 * Method to get property Children
	 *
	 * @return  Container[]
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
	 * @param   mixed   $offset   Offset key.
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
	 * @param   mixed   $offset   Offset key.
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
	 * @param   mixed  $offset  Offset key.
	 * @param   mixed  $value  The value to set.
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
	 * @param   mixed  $offset  Offset key to unset.
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
}
