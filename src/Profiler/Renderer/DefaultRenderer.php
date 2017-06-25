<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Profiler\Renderer;

use Windwalker\Profiler\ProfilerInterface;

/**
 * Class DefaultRenderer
 *
 * @since 2.0
 */
class DefaultRenderer implements ProfilerRendererInterface
{
	/**
	 * Render the profiler.
	 *
	 * @param   ProfilerInterface  $profiler  The profiler to render.
	 *
	 * @return  string  The rendered profiler.
	 */
	public function render(ProfilerInterface $profiler)
	{
		$render = [];

		/** @var \Windwalker\Profiler\Point\ProfilerPointInterface $lastPoint **/
		$lastPoint = null;

		$points = $profiler->getPoints();

		foreach ($points as $point)
		{
			$previousTime = $lastPoint ? $lastPoint->getTime() : 0.0;
			$previousMem = $lastPoint ? $lastPoint->getMemory(true) : 0;

			$tmpl = '%s %.3f seconds (+%.3f); %0.2f MB (%s%0.3f) - %s';

			if (PHP_SAPI !== 'cli')
			{
				$tmpl = '<code>' . $tmpl . '</code>';
			}

			$render[] = sprintf(
				$tmpl,
				$profiler->getName(),
				$point->getTime(),
				$point->getTime() - $previousTime,
				$point->getMemory(true),
				($point->getMemory(true) > $previousMem) ? '+' : '',
				$point->getMemory(true) - $previousMem,
				$point->getName()
			);

			$lastPoint = $point;
		}

		$glue = (PHP_SAPI === 'cli') ? "\n" : '<br />';

		$render = implode($glue, $render);

		return $render;
	}
}
