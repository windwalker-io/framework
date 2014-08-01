<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Form\Field\Type;

use Windwalker\Html\Option;

/**
 * The TimezoneField class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class TimezoneField extends ListField
{
	/**
	 * prepareOptions
	 *
	 * @return  array
	 */
	protected function prepareOptions()
	{
		$zones = array();

		foreach (\DateTimeZone::listIdentifiers() as $zone)
		{
			$zones[] = new Option($zone, $zone);
		}

		return $zones;
	}
}
 