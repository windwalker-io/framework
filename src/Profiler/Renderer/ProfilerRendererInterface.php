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
 * Interface ProfilerRendererInterface.
 */
interface ProfilerRendererInterface
{
    /**
     * Render the profiler.
     *
     * @param ProfilerInterface $profiler The profiler to render.
     *
     * @return string The rendered profiler.
     */
    public function render(ProfilerInterface $profiler);
}
