<?php

namespace Windwalker\Object;

/**
 * Class NullObject
 *
 * @since 1.0
 */
class NullObject extends \JObject implements NullObjectInterface
{
	/**
	 * isNull
	 *
	 * @return boolean
	 */
	public function isNull()
	{
		return true;
	}

	/**
	 * Magic method to convert the object to a string gracefully.
	 *
	 * @return  string  The classname.
	 *
	 * @since   11.1
	 * @deprecated 12.3  Classes should provide their own __toString() implementation.
	 */
	public function __toString()
	{
		return '';
	}

	/**
	 * Sets a default value if not alreay assigned
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $default   The default value.
	 *
	 * @return  mixed
	 *
	 * @since   11.1
	 */
	public function def($property, $default = null)
	{
	}

	/**
	 * Returns a property of the object or the default value if the property is not set.
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $default   The default value.
	 *
	 * @return  mixed    The value of the property.
	 *
	 * @since   11.1
	 *
	 * @see     JObject::getProperties()
	 */
	public function get($property, $default = null)
	{
		return $default;
	}

	/**
	 * Returns an associative array of object properties.
	 *
	 * @param   boolean  $public  If true, returns only the public properties.
	 *
	 * @return  array
	 *
	 * @since   11.1
	 *
	 * @see     JObject::get()
	 */
	public function getProperties($public = true)
	{
		return array();
	}

	/**
	 * Modifies a property of the object, creating it if it does not already exist.
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $value     The value of the property to set.
	 *
	 * @return  mixed  Previous value of the property.
	 *
	 * @since   11.1
	 */
	public function set($property, $value = null)
	{
	}

	/**
	 * Set the object properties based on a named array/hash.
	 *
	 * @param   mixed  $properties  Either an associative array or another object.
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 *
	 * @see     JObject::set()
	 */
	public function setProperties($properties)
	{
		return false;
	}

	/**
	 * __call
	 *
	 * @return  void
	 */
	public function __call()
	{
		return;
	}

	/**
	 * __get
	 *
	 * @return  null
	 */
	public function __get()
	{
		return null;
	}
}
