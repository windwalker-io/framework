<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Test;

/**
 * Static helper methods to assist unit testing PHP code.
 *
 * @note  This class is based on Joomla Test package.
 *
 * @since  2.0
 */
class TestHelper
{
	/**
	 * Assigns mock callbacks to methods.
	 *
	 * This method assumes that the mock callback is named {mock}{method name}.
	 *
	 * @param   \PHPUnit_Framework_MockObject_MockObject  $mockObject  The mock object that the callbacks are being assigned to.
	 * @param   \PHPUnit_Framework_TestCase               $test        The test.
	 * @param   array                                     $array       An array of methods names to mock with callbacks.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public static function assignMockCallbacks(\PHPUnit_Framework_MockObject_MockObject $mockObject, \PHPUnit_Framework_TestCase $test, $array)
	{
		foreach ($array as $index => $method)
		{
			if (is_array($method))
			{
				$methodName = $index;
				$callback = $method;
			}
			else
			{
				$methodName = $method;
				$callback = array(get_called_class(), 'mock' . $method);
			}

			$mockObject->expects($test->any())
				->method($methodName)
				->will($test->returnCallback($callback));
		}
	}

	/**
	 * Assigns mock values to methods.
	 *
	 * @param   \PHPUnit_Framework_MockObject_MockObject  $mockObject  The mock object.
	 * @param   \PHPUnit_Framework_TestCase               $test        The test.
	 * @param   array                                     $array       An associative array of methods to mock with return values:<br />
	 *                                                                 string (method name) => mixed (return value)
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public static function assignMockReturns(\PHPUnit_Framework_MockObject_MockObject $mockObject, \PHPUnit_Framework_TestCase $test, $array)
	{
		foreach ($array as $method => $return)
		{
			$mockObject->expects($test->any())
				->method($method)
				->will($test->returnValue($return));
		}
	}

	/**
	 * Helper method that gets a protected or private property in a class by relfection.
	 *
	 * @param   object  $object        The object from which to return the property value.
	 * @param   string  $propertyName  The name of the property to return.
	 *
	 * @return  mixed  The value of the property.
	 *
	 * @since   2.0
	 * @throws  \InvalidArgumentException if property not available.
	 */
	public static function getValue($object, $propertyName)
	{
		$refl = new \ReflectionClass($object);

		// First check if the property is easily accessible.
		if ($refl->hasProperty($propertyName))
		{
			$property = $refl->getProperty($propertyName);
			$property->setAccessible(true);

			return $property->getValue($object);
		}

		// Hrm, maybe dealing with a private property in the parent class.
		if (get_parent_class($object))
		{
			$property = new \ReflectionProperty(get_parent_class($object), $propertyName);
			$property->setAccessible(true);

			return $property->getValue($object);
		}

		throw new \InvalidArgumentException(sprintf('Invalid property [%s] for class [%s]', $propertyName, get_class($object)));
	}

	/**
	 * Helper method that invokes a protected or private method in a class by reflection.
	 *
	 * Example usage:
	 *
	 * $this->asserTrue(TestCase::invoke('methodName', $this->object, 123));
	 *
	 * @param   object  $object      The object on which to invoke the method.
	 * @param   string  $methodName  The name of the method to invoke.
	 *
	 * @return  mixed
	 *
	 * @since   2.0
	 */
	public static function invoke($object, $methodName)
	{
		// Get the full argument list for the method.
		$args = func_get_args();

		// Remove the method name from the argument list.
		array_shift($args);
		array_shift($args);

		$method = new \ReflectionMethod($object, $methodName);
		$method->setAccessible(true);

		$result = $method->invokeArgs(is_object($object) ? $object : null, $args);

		return $result;
	}

	/**
	 * Helper method that sets a protected or private property in a class by relfection.
	 *
	 * @param   object  $object        The object for which to set the property.
	 * @param   string  $propertyName  The name of the property to set.
	 * @param   mixed   $value         The value to set for the property.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public static function setValue($object, $propertyName, $value)
	{
		$refl = new \ReflectionClass($object);

		// First check if the property is easily accessible.
		if ($refl->hasProperty($propertyName))
		{
			$property = $refl->getProperty($propertyName);
			$property->setAccessible(true);

			$property->setValue($object, $value);
		}
		elseif (get_parent_class($object))
		// Hrm, maybe dealing with a private property in the parent class.
		{
			$property = new \ReflectionProperty(get_parent_class($object), $propertyName);
			$property->setAccessible(true);

			$property->setValue($object, $value);
		}
	}
}
