<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
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
	const MILLI_SECOND = 1000;
	const MICRO_SECOND = 1000000;

	const SORT_ASC = 'asc';
	const SORT_DESC = 'desc';

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
	protected $format = 1;

	/**
	 * Property renderHandler.
	 *
	 * @var \Closure
	 */
	protected $renderOneHandler;

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
	 * @param int $format
	 *
	 * @return  $this
	 */
	public function setTimeFormat($format = self::SECOND)
	{
		$this->format = $format;

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
	public function execute($times = null)
	{
		$times = $times ? : $this->times;

		foreach ($this->tasks as $name => $task)
		{
			$this->run($name, $task, $times);
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
	protected function run($name, $callback, $times)
	{
		$this->profiler->mark($name . '-start');

		foreach (range(1, $times) as $row)
		{
			call_user_func($callback);
		}

		$this->profiler->mark($name . '-end');

		$time = $this->profiler->getTimeBetween($name . '-start', $name . '-end');

		$time = $time * $this->format;

		$this->results[$name] = $time;

		return $this;
	}

	/**
	 * Method to get property Results
	 *
	 * @param string $sort Null, desc or asc.
	 *
	 * @return  array
	 */
	public function getResults($sort = null)
	{
		$results = $this->results;

		if ($sort)
		{
			(strtolower($sort) == static::SORT_DESC) ? arsort($results) : asort($results);
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
	public function renderOne($name, $round = false)
	{
		$result = $this->getResult($name);

		if ($this->renderOneHandler instanceof \Closure)
		{
			$closure = $this->renderOneHandler;

			return $closure($name, $result, $round, $this->format);
		}

		if ($round !== false)
		{
			$result = round($result, $round);
		}

		switch ($this->format)
		{
			case static::MILLI_SECOND :
				$unit = 'ms';
				break;

			case static::MICRO_SECOND :
				$unit = 'μs';
				break;

			case static::SECOND :
			default :
				$unit = 's';
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
	public function render($round = false, $sort = null, $html = false)
	{
		$output = array();

		foreach ($this->getResults($sort) as $name => $result)
		{
			$output[] = $this->renderOne($name, $round);
		}

		$separator = $html ? "<br />\n" : "\n";

		return implode($output, $separator);
	}

	/**
	 * Method to get property Times
	 *
	 * @return  int
	 */
	public function getTimes()
	{
		return $this->times;
	}

	/**
	 * Method to set property times
	 *
	 * @param   int $times
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setTimes($times)
	{
		$this->times = $times;

		return $this;
	}

	/**
	 * Method to get property RenderHandler
	 *
	 * @return  \Closure
	 */
	public function getRenderOneHandler()
	{
		return $this->renderOneHandler;
	}

	/**
	 * Method to set property renderHandler
	 *
	 * @param   \Closure $renderOneHandler
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setRenderOneHandler(\Closure $renderOneHandler)
	{
		$this->renderOneHandler = $renderOneHandler;

		return $this;
	}
}
