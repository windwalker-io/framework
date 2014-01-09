<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\View\Json;

use Windwalker\View\AbstractView;

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.path');

/**
 * Class AbstractHtmlView
 *
 * @since 1.0
 */
abstract class AbstractJsonView extends AbstractView
{
	/**
	 * Property data.
	 *
	 * @var \JRegistry
	 */
	protected $data;

	/**
	 * Method to instantiate the view.
	 *
	 * @param   \JModel $model  The model object.
	 *
	 * @since   12.1
	 */
	public function __construct(\JModel $model = null)
	{
		parent::__construct($model);

		$this->data = new \JRegistry;
	}

	/**
	 * Magic toString method that is a proxy for the render method.
	 *
	 * @return  string
	 *
	 * @since   12.1
	 */
	public function __toString()
	{
		return $this->render();
	}

	/**
	 * Method to escape output.
	 *
	 * @param   string  $output  The output to escape.
	 *
	 * @return  string  The escaped output.
	 *
	 * @see     JView::escape()
	 * @since   12.1
	 */
	public function escape($output)
	{
		// Escape the output.
		return htmlspecialchars($output, ENT_COMPAT, 'UTF-8');
	}

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @since   12.1
	 * @throws  \RuntimeException
	 */
	public function render()
	{
		return $this->data->toString('json');
	}

	/**
	 * getData
	 *
	 * @return \JData
	 */
	public function getData()
	{
		if (!$this->data)
		{
			$this->data = new \JRegistry;
		}

		return $this->data;
	}

	/**
	 * setData
	 *
	 * @param $data
	 *
	 * @return $this
	 */
	public function setData($data)
	{
		$this->data = $data;

		return $this;
	}
}
