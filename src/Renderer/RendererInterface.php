<?php
/**
 * Part of formosa project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Renderer;

/**
 * Interface RendererInterface
 */
interface RendererInterface
{
	/**
	 * render
	 *
	 * @param string $file
	 * @param array  $data
	 *
	 * @return  string
	 */
	public function render($file, $data = array());

	/**
	 * Method to escape output.
	 *
	 * @param   string  $output  The output to escape.
	 *
	 * @return  string  The escaped output.
	 */
	public function escape($output);
}
 