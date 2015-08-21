<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2015 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Profiler;

/**
 * The ProfilerAwareInterface interface.
 * 
 * @since  {DEPLOY_VERSION}
 */
interface ProfilerAwareInterface
{
	/**
	 * Get profiler.
	 *
	 * If profiler not exists, a NullProfiler will instead.
	 *
	 * @return  ProfilerInterface
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function getProfiler();

	/**
	 * Set Profiler.
	 *
	 * @param   ProfilerInterface  $profiler  $ths profiler to set into this object.
	 *
	 * @return  static  Return self to support chaining.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function setProfiler(ProfilerInterface $profiler);
}
