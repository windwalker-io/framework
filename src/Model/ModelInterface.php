<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
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
