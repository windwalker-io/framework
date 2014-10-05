<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Html;

use Windwalker\Dom\HtmlElement;

/**
 * The HtmlOption class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class Option extends HtmlElement
{
	/**
	 * Property value.
	 *
	 * @var  string
	 */
	protected $value = '';

	/**
	 * Property attributes.
	 *
	 * @var  string
	 */
	protected $attributes = array();

	/**
	 * @param $text
	 * @param $value
	 * @param $attributes
	 */
	public function __construct($text = null, $value = null, $attributes = array())
	{
		$this->value = $value;

		$attributes['value'] = $value;

		parent::__construct('option', $text, $attributes);
	}

	/**
	 * Method to get property Value
	 *
	 * @return  string
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Method to set property value
	 *
	 * @param   string $value
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setValue($value)
	{
		$this->value = $value;

		$this['value'] = $value;

		return $this;
	}
}
