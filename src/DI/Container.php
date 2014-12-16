<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\DI;

use Windwalker\DI\Exception\DependencyResolutionException;

/**
 * The DI Container.
 *
 * @note This class is based on Joomla Container.
 *
 * @since 2.0
 */
class Container
{
	const FORCE_NEW = true;

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
	 * @param   Container  $parent  Parent for hierarchical containers.
	 *
	 * @since   2.0
	 */
	public function __construct(Container $parent = null)
	{
		$this->parent = $parent;
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
	 * @return  Container  This object for chaining.
	 *
	 * @since   2.0
	 */
	public function createChild()
	{
		return new static($this);
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
			throw new \InvalidArgumentException(sprintf('The requested key %s does not exist to extend.', $key));
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
				$methodArgs[] = $param->getDefaultValue();

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
	 * @param   string    $key        Name of dataStore key to set.
	 * @param   callable  $callback   Callable function to run when requesting the specified $key.
	 * @param   bool      $protected  True to create and store a shared instance.
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
	 * @param   string   $key       Name of the dataStore key to get.
	 * @param   boolean  $forceNew  True to force creation and return of a new instance.
	 *
	 * @return  mixed   Results of running the $callback for the specified $key.
	 *
	 * @since   2.0
	 * @throws  \InvalidArgumentException
	 */
	public function get($key, $forceNew = false)
	{
		$store = $this->getRaw($key);

		if (is_null($store))
		{
			throw new \InvalidArgumentException(sprintf('Key %s has not been registered with the container.', $key));
		}

		return $store->get($this, $forceNew);
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
}

