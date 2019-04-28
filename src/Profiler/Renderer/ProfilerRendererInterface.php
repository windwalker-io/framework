<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
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
     * @param   ProfilerInterface $profiler The profiler to render.
     *
     * @return  string  The rendered profiler.
     */
    public function render(ProfilerInterface $profiler);
}
