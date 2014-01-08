<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Helper\Html;

use Windwalker\Helper\HtmlHelper;

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
		return HtmlHelper::buildTag($this->name, $this->content, $this->attribs);
	}
}
