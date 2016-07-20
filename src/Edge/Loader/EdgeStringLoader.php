<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Edge\Loader;

/**
 * The EdgeFileLoader class.
 *
 * @since  3.0
 */
class EdgeStringLoader implements EdgeLoaderInterface
{
	/**
	 * Property content.
	 *
	 * @var  string
	 */
	protected $content;

	/**
	 * EdgeTextLoader constructor.
	 *
	 * @param string $content
	 */
	public function __construct($content = null)
	{
		$this->content = $content;
	}

	/**
	 * load
	 *
	 * @param   string $key
	 *
	 * @return  string
	 */
	public function find($key)
	{
		return $key;
	}

	/**
	 * loadFile
	 *
	 * @param   string  $path
	 *
	 * @return  string
	 */
	public function load($path)
	{
		return $path ? : $this->content;
	}

	/**
	 * Method to get property Content
	 *
	 * @return  string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Method to set property content
	 *
	 * @param   string $content
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setContent($content)
	{
		$this->content = $content;

		return $this;
	}
}
