<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Session\Bag;

/**
 * Interface FlasgBagInterface
 */
interface FlashBagInterface extends SessionBagInterface
{
	/**
	 * add
	 *
	 * @param string $msg
	 * @param string $type
	 *
	 * @return  $this
	 */
	public function add($msg, $type = 'info');

	/**
	 * Take all and clean.
	 *
	 * @return  array
	 */
	public function takeAll();

	/**
	 * getType
	 *
	 * @param string $type
	 *
	 * @return  array
	 */
	public function getType($type);
}

