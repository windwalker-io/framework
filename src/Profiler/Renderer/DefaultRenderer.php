<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Profiler\Renderer;

use Windwalker\Profiler\ProfilerInterface;

/**
 * Class DefaultRenderer
 *
 * @since 1.0
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
		$render = '';

		/** @var \Windwalker\Profiler\Item\ProfilerItemInterface $lastItem **/
		$lastItem = null;

		$items = $profiler->getItems();

		foreach ($items as $item)
		{
			$previousTime = $lastItem ? $lastItem->getTiming() : 0.0;
			$previousMem = $lastItem ? $lastItem->getMemory(true) : 0;

			$render .= sprintf(
				'<code>%s %.3f seconds (+%.3f); %0.2f MB (%s%0.3f) - %s</code>',
				$profiler->getName(),
				$item->getTiming(),
				$item->getTiming() - $previousTime,
				$item->getMemory(true),
				($item->getMemory(true) > $previousMem) ? '+' : '',
				$item->getMemory(true) - $previousMem,
				$item->getName()
			);

			$render .= '<br />';

			$lastItem = $item;
		}

		return $render;
	}
}

