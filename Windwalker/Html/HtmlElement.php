<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Html;

/**
 * Class HtmlElement
 *
 * @since 1.0
 */
class HtmlElement implements \ArrayAccess
{
	/**
	 * @var  string  Property name.
	 */
	protected $name;

	/**
	 * @var  array  Property attribs.
	 */
	protected $attribs;

	/**
	 * @var  mixed  Property content.
	 */
	protected $content;

	/**
	 * Constructor
	 *
	 * @param string $name
	 * @param null   $content
	 * @param array  $attribs
	 */
	public function __construct($name, $content = null, $attribs = array())
	{
		$this->name    = $name;
		$this->attribs = $attribs;
		$this->content = $content;
	}

	/**
	 * __toString
	 *
	 * @return  string
	 */
	public function __toString()
	{
		return HtmlBuilder::create($this->name, $this->content, $this->attribs);
	}

	/**
	 * getContent
	 *
	 * @return  mixed
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * setContent
	 *
	 * @param   mixed $content
	 *
	 * @return  HtmlElement  Return self to support chaining.
	 */
	public function setContent($content)
	{
		$this->content = $content;

		return $this;
	}

	/**
	 * getAttribs
	 *
	 * @param string $name
	 * @param mixed  $default
	 *
	 * @return  array
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
	 * setAttribs
	 *
	 * @param string $name
	 * @param string $value
	 *
	 * @return  HtmlElement  Return self to support chaining.
	 */
	public function setAttribute($name, $value)
	{
		$this->attribs[$name] = (string) $value;

		return $this;
	}

	/**
	 * getAttribs
	 *
	 * @return  array
	 */
	public function getAttributes()
	{
		return $this->attribs;
	}

	/**
	 * setAttribs
	 *
	 * @param   array $attribs
	 *
	 * @return  HtmlElement  Return self to support chaining.
	 */
	public function setAttributes($attribs)
	{
		$this->attribs = $attribs;

		return $this;
	}

	/**
	 * getName
	 *
	 * @return  string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * setName
	 *
	 * @param   string $name
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
