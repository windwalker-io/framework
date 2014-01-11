<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Helper;

use JFactory;
use JProfiler;

/**
 * Class ProfilerHelper
 *
 * @since 1.0
 */
class ProfilerHelper
{
	/**
	 * Property profiler.
	 *
	 * @var  JProfiler[]
	 */
	protected static $profiler = array();

	/**
	 * Property state_buffer.
	 *
	 * @var mixed
	 */
	protected static $stateBuffer = array();

	/**
	 * A helper to add JProfiler log mark. Need to trun on the debug mode.
	 *
	 * @param   string $text      Log text.
	 * @param   string $namespace The JProfiler instance ID. Default is the core profiler "Application".
	 *
	 * @return  void
	 */
	public static function mark($text, $namespace = 'Windwalker')
	{
		$app = JFactory::getApplication();

		if ($namespace == 'core' || !$namespace)
		{
			$namespace = 'Application';
		}

		if (!(JDEBUG && $namespace == 'Application') && !AKDEBUG)
		{
			return;
		}

		if (!isset(self::$profiler[$namespace]))
		{
			self::$profiler[$namespace] = JProfiler::getInstance($namespace);

			// Get last page logs.
			self::$stateBuffer[$namespace] = $app->getUserState('windwalker.system.profiler.' . $namespace);
		}

		self::$profiler[$namespace]->mark($text);

		// Save in session
		$app->setUserState('windwalker.system.profiler.' . $namespace, self::$profiler[$namespace]->getBuffer());
	}

	/**
	 * Render the profiler log data, and echo it..
	 *
	 * @param   string   $namespace The JProfiler instance ID. Default is the core profiler "Application".
	 * @param   boolean  $asString  Return as string.
	 *
	 * @return  string
	 */
	public static function render($namespace = 'Windwalker', $asString = false)
	{
		$app = JFactory::getApplication();

		if ($namespace == 'core' || !$namespace)
		{
			$namespace = 'Application';
		}

		$buffer = 'No Profiler data.';

		if (isset(self::$profiler[$namespace]))
		{
			$_PROFILER = self::$profiler[$namespace];

			$buffer = $_PROFILER->getBuffer();
			$buffer = implode("\n<br />\n", $buffer);
		}
		else
		{
			$buffer = $app->getUserState('windwalker.system.profiler.' . $namespace);
			$buffer = $buffer ? implode("\n<br />\n", $buffer) : '';
		}

		$buffer = $buffer ? $buffer : 'No Profiler data.';

		// Get last page logs
		$state_buffer = \JArrayHelper::getValue(self::$stateBuffer, $namespace);

		if ($state_buffer)
		{
			$state_buffer = implode("\n<br />\n", $state_buffer);
			$buffer       = $state_buffer . "\n<br />---------<br />\n" . $buffer;
		}

		// Render
		$buffer = "<pre><h3>WindWalker Debug [namespace: {$namespace}]: </h3>" . $buffer . '</pre>';

		$app->setUserState('windwalker.system.profiler.' . $namespace, '');

		if ($asString)
		{
			return $buffer;
		}

		echo $buffer;
	}
}
