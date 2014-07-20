<?php
/**
 * Part of formosa project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Model;

use Windwalker\Registry\Registry;

/**
 * Interface ModelInterface
 */
interface ModelInterface
{
	/**
	 * Get the model state.
	 *
	 * @return  Registry  The state object.
	 */
	public function getState();

	/**
	 * Set the model state.
	 *
	 * @param   Registry  $state  The state object.
	 *
	 * @return  void
	 */
	public function setState(Registry $state);
}
 