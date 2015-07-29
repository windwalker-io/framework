<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\View;

/**
 * Class HtmlView
 *
 * @since 2.0
 */
class SimpleHtmlView extends AbstractView
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
	 * @since   2.0
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
	 * @since   2.0
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

		$data = $this->data;

		// Start an output buffer.
		ob_start();

		// Load the layout.
		include $layout;

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
	 * @return  static  Return self to support chaining.
	 */
	public function setLayout($layout)
	{
		$this->layout = $layout;

		return $this;
	}
}
