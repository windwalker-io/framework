<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Database\Middleware;

use Windwalker\Database\Driver\AbstractDatabaseDriver;
use Windwalker\Middleware\AbstractMiddleware;

/**
 * The ProfilerMiddleware class.
 *
 * @since  3.0-beta2
 */
class DbProfilerMiddleware extends AbstractMiddleware
{
	/**
	 * Property before.
	 *
	 * @var  callable
	 */
	protected $before;

	/**
	 * Property after.
	 *
	 * @var  callable
	 */
	protected $after;

	/**
	 * ProfilerMiddleware constructor.
	 *
	 * @param callable $before
	 * @param callable $after
	 */
	public function __construct($before = null, $after = null)
	{
		$this->setBefore($before)->setAfter($after);
	}

	/**
	 * Call next middleware.
	 *
	 * @param  mixed $data
	 *
	 * @return mixed
	 */
	public function execute($data = null)
	{
		if (!isset($data->db) || !$data->db instanceof AbstractDatabaseDriver)
		{
			return $this->next->execute($data);
		}

		/** @var AbstractDatabaseDriver $db */
		$db = $data->db;

		call_user_func($this->getBefore(), $db, $data);

		$result = $this->next->execute($data);

		call_user_func($this->getAfter(), $db, $data);

		return $result;
	}

	/**
	 * Method to get property Before
	 *
	 * @return  callable
	 */
	public function getBefore()
	{
		return $this->before ? : function () {};
	}

	/**
	 * Method to set property before
	 *
	 * @param   callable $before
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setBefore($before)
	{
		if (!is_callable($before))
		{
			throw new \InvalidArgumentException('Profiler before handler should be callable.');
		}

		$this->before = $before;

		return $this;
	}

	/**
	 * Method to get property After
	 *
	 * @return  callable
	 */
	public function getAfter()
	{
		return $this->after ? : function () {};
	}

	/**
	 * Method to set property after
	 *
	 * @param   callable $after
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setAfter($after)
	{
		if (!is_callable($after))
		{
			throw new \InvalidArgumentException('Profiler after handler should be callable.');
		}

		$this->after = $after;

		return $this;
	}
}
