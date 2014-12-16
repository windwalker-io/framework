<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Session\Bag;

/**
 * The ArrayBag class.
 * 
 * @since  2.0
 */
class ArrayBag extends SessionBag implements SessionBagInterface
{
	/**
	 * Property data.
	 *
	 * @var  array
	 */
	protected $data = array();

	/**
	 * setData
	 *
	 * @param array $data
	 *
	 * @return  void
	 */
	public function setData(array &$data)
	{
		$this->data = array();

		return;
	}
}
