<?php

use Windwalker\Helper\QueryHelper;
use Windwalker\Model\ListModel;

/**
 * Class {{extension.name.cap}}Model{{controller.list.name.cap}}
 *
 * @since 1.0
 */
class {{extension.name.cap}}Model{{controller.list.name.cap}} extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param    array  $config  An optional associative array of configuration settings.
	 *
	 * @see      JController
	 * @since    1.6
	 */
	public function __construct($config = array(), \JRegistry $state = null, \JDatabaseDriver $db = null)
	{
		// Set query tables
		// ========================================================================
		$config['tables'] = array(
			'{{controller.item.name.lower}}'    => '#__{{extension.name.lower}}_{{controller.list.name.lower}}',
			'category'  => '#__categories',
			'user'      => '#__users',
			'viewlevel' => '#__viewlevels',
			'lang'      => '#__languages'
		);

		// Set filter fields
		// ========================================================================
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'filter_order_Dir', 'filter_order', '*'
			);

			$config['filter_fields'] = QueryHelper::mergeFilterFields(null, $config['filter_fields'], $config['tables']);
		}

		$this->config = $config;

		parent::__construct($config, $state, $db);
	}

	/**
	 * getListQuery
	 *
	 * @return \JDatabaseQuery
	 */
	public function getListQuery()
	{
		$query = parent::getListQuery();

		$ordering    = $this->state->get('list.ordering',    '{{controller.item.name.lower}}.ordering');
		$direction   = $this->state->get('list.direction',   'ASC');
		$orderCol    = $this->state->get('list.orderCol',    $this->orderCol);

		$filters  = $this->state->get('filter', array());
		$searches = $this->state->get('search', array());

		// Build filter query
		foreach ($filters as $name => $value)
		{
			if ($value !== '' && $value != '*')
			{
				$query->where($query->quoteName($name) . ' = ' . $query->quote($value));
			}
		}

		// Build search query
		$searchValue = array();

		array_walk(
			$searches,
			function($value, $key) use (&$searchValue, $query)
			{
				if ($value && $key != '*')
				{
					$searchValue[] = $query->quoteName($key) . ' LIKE ' . $query->quote('%' . $value . '%');
				}
			}
		);

		if (count($searchValue))
		{
			$query->where(new JDatabaseQueryElement('()', $searchValue, " \nOR "));
		}

		// Published
		if (empty($filters['{{controller.item.name.lower}}.published']))
		{
			$query->where($query->quoteName('{{controller.item.name.lower}}.published') . ' >= 0');
		}

		// Ordering
		$ordering = explode(',', $ordering);

		$ordering = array_map(
			function($value) use($query)
			{
				$value = explode(' ', trim($value));

				if (isset($value[1]))
				{
					return $query->qn($value[0]) . ' ' . $value[1];
				}

				return $query->qn($value[0]);
			},
			$ordering
		);

		$ordering = implode(', ', $ordering);

		// Build query
		// ========================================================================

		// Get select columns
		$select = QueryHelper::getSelectList($this->db, $this->config['tables'], true);

		// Build query
		$query->select($select)
			->from('#__{{extension.name.lower}}_{{controller.list.name.lower}} AS {{controller.item.name.lower}}')
			->leftJoin('#__categories  AS category  ON {{controller.item.name.lower}}.catid      = category.id')
			->leftJoin('#__users       AS user      ON {{controller.item.name.lower}}.created_by = user.id')
			->leftJoin('#__viewlevels  AS viewlevel ON {{controller.item.name.lower}}.access     = viewlevel.id')
			->leftJoin('#__languages   AS lang      ON {{controller.item.name.lower}}.language   = lang.lang_code')
			// ->where("")
			->order($ordering . ' ' . $direction)
			;

		// Debug here
		// \AK::show((string) $query);

		return $query;
	}
}
