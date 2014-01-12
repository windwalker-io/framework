<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Model\Filter;

/**
 * Class FilterHelper
 *
 * @since 1.0
 */
class FilterHelper extends AbstractFilterHelper
{
	/**
	 * execute
	 *
	 * @param \JDatabaseQuery $query
	 * @param array           $filters
	 *
	 * @return  \JDatabaseQuery
	 */
	public function execute(\JDatabaseQuery $query, $filters = array())
	{
		foreach ($filters as $field => $value)
		{
			// If handler is FALSE, means skip this field.
			if (array_key_exists($field, $this->handler) && $this->handler[$field] === static::SKIP)
			{
				continue;
			}

			if (!empty($this->handler[$field]) && is_callable($this->handler[$field]))
			{
				call_user_func_array($this->handler[$field], array($query, $field, $value));
			}
			else
			{
				$handler = $this->defaultHandler;

				/** @see FilterHelper::registerDefaultHandler() */
				$handler($query, $field, $value);
			}
		}

		return $query;
	}

	/**
	 * getDefaultHandler
	 *
	 * @return  callable
	 */
	protected function registerDefaultHandler()
	{
		/**
		 * Default handler closure.
		 *
		 * @param \JDatabaseQuery $query
		 * @param string          $field
		 * @param string          $value
		 *
		 * @return  \JDatabaseQuery
		 */
		return function(\JDatabaseQuery $query, $field, $value)
		{
			if ($value !== '' && $value !== null && $value !== false && $value != '*')
			{
				$query->where($query->quoteName($field) . ' = ' . $query->quote($value));
			}

			return $query;
		};
	}
}
