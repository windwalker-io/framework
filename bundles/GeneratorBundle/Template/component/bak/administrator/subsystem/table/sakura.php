<?php
/**
 * Part of joomla321 project. 
 *
 * @copyright  Copyright (C) 2011 - 2013 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

use Windwalker\Table\Table;

class FlowerTableSakura extends Table
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct('#__flower_sakuras');
	}
}
