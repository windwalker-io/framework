<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Http\Response;

/**
 * Interface OutputInterface
 */
interface OutputResponseInterface
{
	/**
	 * Method to send the application response to the client.  All headers will be sent prior to the main
	 * application output data.
	 *
	 * @param   boolean $returnBody  Return output body or not.
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	public function respond($returnBody = false);
}
