<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Profiler;

/**
 * The Benchmark class.
 *
 * @since  {DEPLOY_VERSION}
 */
class Benchmark
{
	const SECOND = 1;
	const MILLISECOND = 1000;
	const MICRO_SECOND = 1000000;

	/**
	 * Property profiler.
	 *
	 * @var  Profiler
	 */
	protected $profiler = null;

	/**
	 * Property name.
	 *
	 * @var  string
	 */
	protected $name;

	/**
	 * Property times.
	 *
	 * @var  int
	 */
	protected $times = 100;

	/**
	 * Property tasks.
	 *
	 * @var  array
	 */
	protected $tasks = array();

	/**
	 * Property results.
	 *
	 * @var  array
	 */
	protected $results = array();

	/**
	 * Property timeFormat.
	 *
	 * @var integer
	 */
	protected $fold = 1;

	/**
	 * Class init.
	 *
	 * @param string   $name
	 * @param Profiler $profiler
	 * @param int      $times
	 */
	public function __construct($name = null, Profiler $profiler = null, $times = 100)
	{
		$name = $name ? : 'benchmark-' . uniqid();

		$this->profiler = $profiler ? : new Profiler($name);
		$this->name = $name;
		$this->times = $times;
	}

	/**
	 * setTimeFormat
	 *
	 * @param int $fold
	 *
	 * @return  $this
	 */
	public function setTimeFold($fold = self::SECOND)
	{
		$this->fold = $fold;

		return $this;
	}

	/**
	 * addTask
	 *
	 * @param string   $name
	 * @param callable $callback
	 *
	 * @throws \InvalidArgumentException
	 * @return  static
	 */
	public function addTask($name, $callback)
	{
		if (!is_callable($callback))
		{
			throw new \InvalidArgumentException('Task should be a callback.');
		}

		$this->tasks[$name] = $callback;

		return $this;
	}

	/**
	 * run
	 *
	 * @param integer  $times
	 *
	 * @return  $this
	 */
	public function run($times = null)
	{
		$times = $times ? : $this->times;

		foreach ($this->tasks as $name => $task)
		{
			$this->runTask($name, $task, $times);
		}

		return $this;
	}

	/**
	 * runTask
	 *
	 * @param string   $name
	 * @param callable $callback
	 * @param integer  $times
	 *
	 * @return  $this
	 */
	protected function runTask($name, $callback, $times)
	{
		$this->profiler->mark($name . '-start');

		foreach (range(1, $times) as $row)
		{
			$callback();
		}

		$this->profiler->mark($name . '-end');

		$time = $this->profiler->getTimeBetween($name . '-start', $name . '-end');

		$time = $time * $this->fold;

		$this->results[$name] = $time;

		return $this;
	}

	/**
	 * Method to get property Results
	 *
	 * @param string $sort
	 *
	 * @return  array
	 */
	public function getResults($sort = null)
	{
		$results = $this->results;

		if ($sort)
		{
			(strtolower($sort) == 'desc') ? arsort($results) : asort($results);
		}

		return $results;
	}

	/**
	 * Method to get property Results
	 *
	 * @param string $name
	 *
	 * @return  integer
	 */
	public function getResult($name)
	{
		if (!empty($this->results[$name]))
		{
			return $this->results[$name];
		}

		return null;
	}

	/**
	 * renderResult
	 *
	 * @param string $name
	 * @param bool   $round
	 *
	 * @return  string
	 */
	public function renderResult($name, $round = false)
	{
		$result = $this->getResult($name);

		if ($round !== false)
		{
			$result = round($result, $round);
		}

		switch ($this->fold)
		{
			case static::MILLISECOND :
				$unit = 'ms';
				break;

			case static::MICRO_SECOND :
				$unit = 'micro seconds';
				break;

			case static::SECOND :
			default :
				$unit = 'sec';
				break;
		}

		return $name . ' => ' . $result . ' ' . $unit;
	}

	/**
	 * renderResult
	 *
	 * @param bool     $round
	 * @param string   $sort
	 * @param bool     $html
	 *
	 * @return  string
	 */
	public function renderResults($round = false, $sort = null, $html = false)
	{
		$output = array();

		foreach ($this->getResults($sort) as $name => $result)
		{
			$output[] = $this->renderResult($name, $round);
		}

		$separator = $html ? "<br />\n" : "\n";

		return implode($output, $separator);
	}
}
 