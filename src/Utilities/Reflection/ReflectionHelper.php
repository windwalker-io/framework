<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Utilities\Reflection;

/**
 * Reflection
 *
 * @method  static  mixed              getConstant ($object, $name)
 * @method  static  array              getConstants ($object)
 * @method  static  \ReflectionMethod  getConstructor ($object)
 * @method  static  array              getDefaultProperties ($object)
 * @method  static  string             getDocComment ($object)
 * @method  static  int                getEndLine ($object)
 * @method  static  \ReflectionExtension getExtension ($object)
 * @method  static  string             getExtensionName ($object)
 * @method  static  string             getFileName ($object)
 * @method  static  array              getInterfaceNames ($object)
 * @method  static  array              getInterfaces ($object)
 * @method  static  \ReflectionMethod  getMethod ($object, string $name)
 * @method  static  array              getMethods ($object, int $filter)
 * @method  static  int                getModifiers ($object)
 * @method  static  string             getName ($object)
 * @method  static  string             getNamespaceName ($object)
 * @method  static  object             getParentClass ($object)
 * @method  static  array              getProperties ($object, int $filter)
 * @method  static  \ReflectionProperty getProperty ($object, string $name)
 * @method  static  string             getShortName ($object)
 * @method  static  int                getStartLine ($object)
 * @method  static  array              getStaticProperties ($object)
 * @method  static  mixed              getStaticPropertyValue ($object, string $name, mixed &$def_value)
 * @method  static  array              getTraitAliases ($object)
 * @method  static  array              getTraitNames ($object)
 * @method  static  array              getTraits ($object)
 * @method  static  bool               hasConstant ($object, string $name )
 * @method  static  bool               hasMethod ($object, string $name )
 * @method  static  bool               hasProperty ($object, string $name )
 * @method  static  bool               implementsInterface ($object, string $interface)
 * @method  static  bool               inNamespace ($object)
 * @method  static  bool               isAbstract ($object)
 * @method  static  bool               isCloneable ($object)
 * @method  static  bool               isFinal ($object)
 * @method  static  bool               isInstance (object $object)
 * @method  static  bool               isInstantiable ($object)
 * @method  static  bool               isInterface ($object)
 * @method  static  bool               isInternal ($object)
 * @method  static  bool               isIterateable ($object)
 * @method  static  bool               isSubclassOf ($object, string $class)
 * @method  static  bool               isTrait ($object)
 * @method  static  bool               isUserDefined ($object)
 * @method  static  object             newInstance ($object, mixed $args, mixed $moreArgs)
 * @method  static  object             newInstanceArgs ($object, array $args)
 * @method  static  object             newInstanceWithoutConstructor ($object)
 * @method  static  string             __toString ($object)
 *
 * @since 2.0
 */
class ReflectionHelper
{
	const IS_IMPLICIT_ABSTRACT = 16;
	const IS_EXPLICIT_ABSTRACT = 32;
	const IS_FINAL = 64;

	/**
	 * Get a new ReflectionClass.
	 *
	 * @param string|object $class The class name.
	 *
	 * @return  \ReflectionClass Reflection instance.
	 */
	public static function get($class)
	{
		return static::getReflection($class);
	}

	/**
	 * getReflection
	 *
	 * @param string|object $class The class or object to get reflection.
	 *
	 * @return  \ReflectionClass Reflection instance.
	 *
	 * @throws \InvalidArgumentException
	 */
	protected static function getReflection($class)
	{
		return new \ReflectionClass($class);
	}

	/**
	 * Get path from reflection.
	 *
	 * @param string|object $class The class or object to get reflection.
	 *
	 * @return  string The class file path.
	 */
	public static function getPath($class)
	{
		$ref = static::getReflection($class);

		return $ref->getFileName();
	}

	/**
	 * getPackageNamespace
	 *
	 * @param string|object $class
	 * @param int           $backwards
	 *
	 * @return  string
	 */
	public static function getNamespaceBackwards($class, $backwards = 3)
	{
		if (!is_string($class))
		{
			$class = get_class($class);
		}

		$class = explode('\\', $class);

		foreach (range(1, $backwards) as $i)
		{
			array_pop($class);
		}

		return implode('\\', $class);
	}

	/**
	 * Call static magic method.
	 *
	 * @param string $name  The method name.
	 * @param array  $args  The arguments of this methods.
	 *
	 * @return  mixed  Return value from reflection class.
	 */
	public static function __callStatic($name, $args)
	{
		$class = array_shift($args);

		$ref = static::getReflection($class);

		return call_user_func_array(array($ref, $name), $args);
	}
}
