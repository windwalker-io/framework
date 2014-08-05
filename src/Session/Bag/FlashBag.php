<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Session\Bag;

/**
 * Class FlashBag
 *
 * @since {DEPLOY_VERSION}
 */
class FlashBag extends SessionBag implements FlashBagInterface
{
	/**
	 * add
	 *
	 * @param string $msg
	 * @param string $type
	 *
	 * @return  $this
	 */
	public function add($msg, $type = 'info')
	{
		if (!isset($this->data[$type]) || !is_array($this->data[$type]))
		{
			$this->data[$type] = array();
		}

		foreach ((array) $msg as $msg)
		{
			$this->data[$type][] = $msg;
		}

		return $this;
	}

	/**
	 * Take all and clean.
	 *
	 * @return  array
	 */
	public function takeAll()
	{
		$all = $this->all();

		$this->clean();

		return $all;
	}

	/**
	 * getType
	 *
	 * @param string $type
	 *
	 * @return  array
	 */
	public function getType($type)
	{
		return isset($this->data[$type]) ? $this->data[$type] : array();
	}
}

