<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Profiler\Renderer;

use Windwalker\Profiler\ProfilerInterface;

/**
 * Interface ProfilerRendererInterface
 */
interface ProfilerRendererInterface
{
	/**
	 * Render the profiler.
	 *
	 * @param   ProfilerInterface  $profiler  The profiler to render.
	 *
	 * @return  string  The rendered profiler.
	 */
	public function render(ProfilerInterface $profiler);
}
