<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\View\Engine;

/**
 * Class EngineInterface
 *
 * @since 1.0
 */
interface EngineInterface
{
	/**
	 * Method to get the layout path.
	 *
	 * @param   string  $layout  The layout name.
	 *
	 * @return  mixed  The layout file name if found, false otherwise.
	 */
	public function getPath($layout);

	/**
	 * Method to get the view paths.
	 *
	 * @return  \SplPriorityQueue  The paths queue.
	 */
	public function getPaths();

	/**
	 * Method to set the view paths.
	 *
	 * @param   \SplPriorityQueue  $paths  The paths queue.
	 *
	 * @return  EngineInterface  Method supports chaining.
	 */
	public function setPaths(\SplPriorityQueue $paths);

	/**
	 * render
	 *
	 * @param string $layout
	 * @param array  $data
	 *
	 * @return  mixed
	 */
	public function render($layout, $data = array());

	/**
	 * Method to escape output.
	 *
	 * @param   string  $output  The output to escape.
	 *
	 * @return  string  The escaped output.
	 */
	public function escape($output);
}
