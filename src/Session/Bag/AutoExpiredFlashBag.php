<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Session\Bag;

/**
 * Class FlashBag
 *
 * @since 2.0
 */
class AutoExpiredFlashBag extends FlashBag
{
	/**
	 * Property data.
	 *
	 * @var  array
	 */
	protected $data = array(
		'last' => array(),
		'current' => array()
	);

	/**
	 * setData
	 *
	 * @param array $data
	 *
	 * @return  void
	 */
	public function setData(array &$data)
	{
		$this->data = &$data;

		if (!isset($this->data['current']))
		{
			$data['current'] = array();
		}

		$this->data['last'] = isset($this->data['current']) ? $this->data['current'] : array();

		$this->data['current'] = array();
	}

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
		if (!isset($this->data['current'][$type]) || !is_array($this->data['current'][$type]))
		{
			$this->data['current'][$type] = array();
		}

		foreach ((array) $msg as $msg)
		{
			$this->data['current'][$type][] = $msg;
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
	 * all
	 *
	 * @return  array
	 */
	public function all()
	{
		return $this->data['last'];
	}

	/**
	 * clean
	 *
	 * @return  $this
	 */
	public function clean()
	{
		$this->data['last'] = array();

		return $this;
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
		return isset($this->data['last'][$type]) ? $this->data['last'][$type] : array();
	}
}

