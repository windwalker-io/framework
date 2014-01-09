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
class HtmlElement
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
}
