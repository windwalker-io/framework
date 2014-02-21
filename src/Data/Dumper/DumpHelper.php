<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Data\Dumper;

/**
 * Class DumpHelper
 */
abstract class DumpHelper
{
	/**
	 * dump
	 *
	 * @param mixed             $data
	 * @param int               $depth
	 * @param \SplObjectStorage $stroage
	 *
	 * @return  mixed
	 */
	public static function dump($data, $depth = 0, \SplObjectStorage $stroage = null)
	{
		if (!$stroage)
		{
			$stroage = new \SplObjectStorage;
		}

		// Add this data to the dumped stack.
		$stroage->attach($data);

		// Setup a container.
		$dump = new \stdClass;

		if ($depth >= 0)
		{
			// Check if the object is also an dumpable object.
			if ($data instanceof DumpableInterface)
			{
				// Do not dump the property if it has already been dumped.
				if (!$stroage->contains($data))
				{
					$value = $data->dump($depth - 1, $stroage);
				}
			}

			// Check if the object is a date.
			if ($data instanceof \DateTime)
			{
				$data = $data->format('Y-m-d H:i:s');
			}
			elseif ($value instanceof Registry)
				// Check if the object is a registry.
			{
				$value = $value->toObject();
			}
		}
	}

	protected static function dumpOne($data, $deepth = 0, \SplObjectStorage $stroage = null)
	{

	}
}
 