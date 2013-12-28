<?php

namespace Windwalker\Model\Helper;

abstract class AdminListHelper
{
	/**
	 * populateFilter
	 *
	 * @param array            $filters
	 * @param \JRegistry       $state
	 * @param \JApplicationCms $app
	 *
	 * @return void
	 */
	public static function populateFilter($filters, \JRegistry $state, \JApplicationCms $app = null)
	{
		$filterValue = array();

		foreach ($filters as $name => $value)
		{
			$filterValue[$name] = $value;
		}

		$state->set('filter', $filterValue);
	}

	/**
	 * populateFullordering
	 *
	 * @param            $value
	 * @param \JRegistry $state
	 *
	 * @return void
	 */
	public static function populateFullordering($value, \JRegistry $state)
	{
		$orderingParts = explode(' ', $value);

		if (count($orderingParts) >= 2)
		{
			// Latest part will be considered the direction
			$fullDirection = end($orderingParts);

			if (in_array(strtoupper($fullDirection), array('ASC', 'DESC', '')))
			{
				$state->set('list.direction', $fullDirection);
			}

			unset($orderingParts[count($orderingParts) - 1]);

			// The rest will be the ordering
			$fullOrdering = implode(' ', $orderingParts);

			if (in_array($fullOrdering, $this->filterFields))
			{
				$state->set('list.ordering', $fullOrdering);
			}
		}
		else
		{
			$state->set('list.ordering', $ordering);
			$state->set('list.direction', $direction);
		}
	}
}
