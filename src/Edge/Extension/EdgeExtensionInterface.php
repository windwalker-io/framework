<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Edge\Extension;

/**
 * The EdgeExtensionInterface class.
 *
 * @since  {DEPLOY_VERSION}
 */
interface EdgeExtensionInterface
{
	public function getName();

	public function getDirectives();
}
