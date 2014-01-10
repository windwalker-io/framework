<?php

namespace Windwalker\Model\Helper;

abstract class AdminListHelper
{
	/**
	 * handleFilters
	 *
	 * @param array  $filters
	 * @param array  $filterFields
	 *
	 * @return array
	 */
	public static function handleFilters($filters, array $filterFields = array())
	{
		$filterValue = array();

		foreach ($filters as $name => $value)
		{
			if (in_array($name, $filterFields) && $value !== '')
			{
				$filterValue[$name] = $value;
			}
		}

		return $filterValue;
	}

	/**
	 * handleFilters
	 *
	 * @param array $searches
	 * @param array $filterFields
	 * @param array $searchFields
	 *
	 * @return array
	 */
	public static function handleSearches($searches, array $filterFields = array(), $searchFields = array())
	{
		// Convert search field to array
		if (!empty($searches['field']) && !empty($searches['index']))
		{
			// If field is '*', we copy index value to all fields.
			if ($searches['field'] == '*')
			{
				foreach ($searchFields as $field)
				{
					$searches[$field] = $searches['index'];
				}
			}

			// If field not '*', just set one field.
			else
			{
				$searches[$searches['field']] = $searches['index'];
			}
		}

		// Unset field and index but keep other fields.
		unset($searches['field']);
		unset($searches['index']);

		$searchValue = array();

		// Let's build search array.
		foreach ($searches as $name => $value)
		{
			if (in_array($name, $filterFields)  && $value)
			{
				$searchValue[$name] = $value;
			}
		}

		return $searchValue;
	}

	/**
	 * handleFilters
	 *
	 * @param array $value
	 * @param array $orderConfig
	 * @param array $filterFields
	 *
	 * @return array
	 */
	public static function handleFullordering($value, $orderConfig, array $filterFields = array())
	{
		if (!$orderConfig)
		{
			$orderConfig = array(
				'ordering'  => null,
				'direction' => null
			);
		}

		$orderingParts = explode(',', $value);

		$ordering = array();

		foreach ($orderingParts as $order)
		{
			$order = explode(' ', trim($order));

			if (count($order) == 2)
			{
				list($col, $dir) = $order;
			}
			else
			{
				$col = $order[0];
				$dir = '';
			}

			if (in_array($col, $filterFields))
			{
				$ordering[] = $dir ? $col . ' ' . strtoupper($dir) : $col;
			}
		}

		if (!count($ordering))
		{
			return $orderConfig;
		}

		$last = array_pop($ordering);

		$last = explode(' ', $last);

		if (isset($last[1]) && in_array(strtoupper($last[1]), array('ASC', 'DESC')))
		{
			$orderConfig['direction'] = $last[1];
		}

		$ordering[] = $last[0];

		$orderConfig['ordering'] = implode(', ', $ordering);

		return $orderConfig;
	}
}
