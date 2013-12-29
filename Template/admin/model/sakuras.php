<?php

use Windwalker\Model\ListModel;

/**
 * Class FlowerModelSakuras
 *
 * @since 1.0
 */
class FlowerModelSakuras extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param    array  $config  An optional associative array of configuration settings.
	 *
	 * @see      JController
	 * @since    1.6
	 */
	public function __construct($config = array())
	{
		// Set query tables
		// ========================================================================
		$config['tables'] = array(
			'sakura'    => '#__flower_sakuras',
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

			$config['filter_fields'] = AKHelper::_('db.mergeFilterFields', $config['filter_fields'], $config['tables']);
		}

		$this->config = $config;

		parent::__construct($config);
	}

	/**
	 * getListQuery
	 *
	 * @return \JDatabaseQuery
	 */
	public function getListQuery()
	{
		$query = parent::getListQuery();

		$ordering  = $this->state->get('list.ordering', 'sakura.ordering');
		$direction = $this->state->get('list.direction', 'ASC');

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
		if (empty($filters['sakura.published']))
		{
			$query->where($query->quoteName('sakura.published') . ' >= 0');
		}

		// Build query
		// ========================================================================

		// Get select columns
		$select = AKHelper::_('db.getSelectList', $this->config['tables']);

		// Build query
		$query->select($select)
			->from('#__flower_sakuras AS sakura')
			->leftJoin('#__categories  AS category  ON sakura.catid      = category.id')
			->leftJoin('#__users       AS user      ON sakura.created_by = user.id')
			->leftJoin('#__viewlevels  AS viewlevel ON sakura.access     = viewlevel.id')
			->leftJoin('#__languages   AS lang      ON sakura.language   = lang.lang_code')
			// ->where("")
			->order($query->quoteName($ordering) . ' ' . $direction)
			;

		// Debug here
		// \AK::show((string) $query);

		return $query;
	}
}
