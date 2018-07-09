<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2015 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Profiler;

/**
 * The ProfilerAwareInterface interface.
 *
 * @since  2.1.1
 */
interface ProfilerAwareInterface
{
    /**
     * Get profiler.
     *
     * If profiler not exists, a NullProfiler will instead.
     *
     * @return ProfilerInterface
     *
     * @since   2.1.1
     */
    public function getProfiler();

    /**
     * Set Profiler.
     *
     * @param ProfilerInterface $profiler $ths profiler to set into this object.
     *
     * @return static Return self to support chaining.
     *
     * @since   2.1.1
     */
    public function setProfiler(ProfilerInterface $profiler);
}
