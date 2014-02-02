<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\View\Json;

use Joomla\DI\Container;
use Windwalker\Model\Model;
use Joomla\Registry\Registry;
use Windwalker\View\AbstractView;

defined('JPATH_PLATFORM') or die;

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
	 * @var Registry
	 */
	protected $data;

	/**
	 * Method to instantiate the view.
	 *
	 * @param   \JModel $model  The model object.
	 *
	 * @since   12.1
	 */
	public function __construct(Model $model = null, Container $container = null, $config = array())
	{
		parent::__construct($model, $container, $config);

		$this->data = new Registry;
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
	public function doRender()
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
			$this->data = new Registry;
		}

		return $this->data;
	}
}
