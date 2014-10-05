<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\View;

use Windwalker\Registry\Registry;

/**
 * The JsonView class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class JsonView extends AbstractView
{
	/**
	 * Property data.
	 *
	 * @var  array|Registry
	 */
	protected $data = array();

	/**
	 * Property options.
	 *
	 * @var integer
	 */
	protected $options;

	/**
	 * Property depth.
	 *
	 * @var  integer
	 */
	protected $depth = 512;

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @throws  \RuntimeException
	 */
	public function render()
	{
		if ($this->data instanceof Registry)
		{
			return $this->data->toString('json', array('options' => $this->options, 'depth' => $this->depth));
		}

		if (version_compare(PHP_VERSION, '5.5', '<'))
		{
			return json_encode($this->data, $this->options);
		}
		else
		{
			return json_encode($this->data, $this->options, $this->depth);
		}
	}

	/**
	 * Method to get property Options
	 *
	 * @return  int
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * Method to set property options
	 *
	 * @param   int $options
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setOptions($options)
	{
		$this->options = $options;

		return $this;
	}

	/**
	 * Method to get property Depth
	 *
	 * @return  int
	 */
	public function getDepth()
	{
		return $this->depth;
	}

	/**
	 * Method to set property depth
	 *
	 * @param   int $depth
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setDepth($depth)
	{
		$this->depth = $depth;

		return $this;
	}
}
