<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Data;

/**
 * Class DataSet
 *
 * @since 1.0
 */
class DataSet extends \ArrayObject implements DatasetInterface
{
	/**
	 * bind
	 *
	 * @param array $dataset
	 *
	 * @throws \InvalidArgumentException
	 * @return  mixed
	 */
	public function bind($dataset)
	{
		if ($dataset instanceof \Traversable)
		{
			$dataset = iterator_to_array($dataset);
		}
		elseif (is_object($dataset))
		{
			$dataset = array($dataset);
		}
		elseif (!is_array($dataset))
		{
			throw new \InvalidArgumentException('Need an array or object');
		}

		foreach ($dataset as $data)
		{
			if (!($data instanceof Data))
			{
				$data = new Data($data);
			}

			$this[] = $data;
		}

		return $this;
	}
}
