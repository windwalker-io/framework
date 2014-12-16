<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Dom;

use Windwalker\Dom\Builder\DomBuilder;

/**
 * Class XmlElement
 *
 * @since 2.0
 */
class DomElement implements \ArrayAccess
{
	/**
	 * Element tag name.
	 *
	 * @var  string
	 */
	protected $name;

	/**
	 * Element attributes.
	 *
	 * @var  array
	 */
	protected $attribs;

	/**
	 * Element content.
	 *
	 * @var  mixed
	 */
	protected $content;

	/**
	 * Constructor
	 *
	 * @param string $name    Element tag name.
	 * @param mixed  $content Element content.
	 * @param array  $attribs Element attributes.
	 */
	public function __construct($name, $content = null, $attribs = array())
	{
		if (is_array($content))
		{
			$content = new DomElements($content);
		}

		$this->name    = $name;
		$this->attribs = $attribs;
		$this->content = $content;
	}

	/**
	 * toString
	 *
	 * @param boolean $forcePair
	 *
	 * @return  string
	 */
	public function toString($forcePair = false)
	{
		return DomBuilder::create($this->name, $this->content, $this->attribs, $forcePair);
	}

	/**
	 * Convert this object to string.
	 *
	 * @return  string
	 */
	public function __toString()
	{
		try
		{
			return $this->toString();
		}
		catch (\Exception $e)
		{
			return (string) $e;
		}
	}

	/**
	 * Get content.
	 *
	 * @return  mixed
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Set content.
	 *
	 * @param   mixed $content Element content.
	 *
	 * @return  HtmlElement  Return self to support chaining.
	 */
	public function setContent($content)
	{
		$this->content = $content;

		return $this;
	}

	/**
	 * Get attributes.
	 *
	 * @param string $name    Attribute name.
	 * @param mixed  $default Default value.
	 *
	 * @return  string The attribute value.
	 */
	public function getAttribute($name, $default = null)
	{
		if (empty($this->attribs[$name]))
		{
			return $default;
		}

		return $this->attribs[$name];
	}

	/**
	 * Set attribute value.
	 *
	 * @param string $name  Attribute name.
	 * @param string $value The value to set into attribute.
	 *
	 * @return  HtmlElement  Return self to support chaining.
	 */
	public function setAttribute($name, $value)
	{
		$this->attribs[$name] = (string) $value;

		return $this;
	}

	/**
	 * Get all attributes.
	 *
	 * @return  array All attributes.
	 */
	public function getAttributes()
	{
		return $this->attribs;
	}

	/**
	 * Set all attributes.
	 *
	 * @param   array $attribs All attributes.
	 *
	 * @return  HtmlElement  Return self to support chaining.
	 */
	public function setAttributes($attribs)
	{
		$this->attribs = $attribs;

		return $this;
	}

	/**
	 * Get element tag name.
	 *
	 * @return  string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set element tag name.
	 *
	 * @param   string $name Set element tag name.
	 *
	 * @return  HtmlElement  Return self to support chaining.
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * Whether a offset exists
	 *
	 * @param mixed $offset An offset to check for.
	 *
	 * @return boolean True on success or false on failure.
	 *                 The return value will be casted to boolean if non-boolean was returned.
	 */
	public function offsetExists($offset)
	{
		return isset($this->attribs[$offset]);
	}

	/**
	 * Offset to retrieve
	 *
	 * @param mixed $offset The offset to retrieve.
	 *
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset)
	{
		if (!$this->offsetExists($offset))
		{
			return null;
		}

		return $this->attribs[$offset];
	}

	/**
	 * Offset to set
	 *
	 * @param mixed $offset The offset to assign the value to.
	 * @param mixed $value  The value to set.
	 *
	 * @return void
	 */
	public function offsetSet($offset, $value)
	{
		$this->attribs[$offset] = $value;
	}

	/**
	 * Offset to unset
	 *
	 * @param mixed $offset The offset to unset.
	 *
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		unset($this->attribs[$offset]);
	}
}

