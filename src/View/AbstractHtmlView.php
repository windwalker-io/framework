<?php
/**
 * Part of formosa project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\View;

/**
 * Class AbstractHtmlView
 *
 * @since 1.0
 */
class AbstractHtmlView extends AbstractView
{
	/**
	 * Property layout.
	 *
	 * @var  string
	 */
	protected $layout = null;

	/**
	 * Method to escape output.
	 *
	 * @param   string  $output  The output to escape.
	 *
	 * @return  string  The escaped output.
	 *
	 * @see     ViewInterface::escape()
	 * @since   1.0
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
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function render()
	{
		// Get the layout path.
		$layout = $this->getLayout();

		// Check if the layout path was found.
		if (!is_file($layout))
		{
			throw new \RuntimeException(sprintf('Layout: %s Not Found', $layout));
		}

		// Start an output buffer.
		ob_start();

		// Load the layout.
		include realpath($layout);

		// Get the layout contents.
		$output = ob_get_clean();

		return $output;
	}

	/**
	 * getLayout
	 *
	 * @return  string
	 */
	public function getLayout()
	{
		return $this->layout;
	}

	/**
	 * setLayout
	 *
	 * @param   string $layout
	 *
	 * @return  AbstractHtmlView  Return self to support chaining.
	 */
	public function setLayout($layout)
	{
		$this->layout = $layout;

		return $this;
	}
}
 