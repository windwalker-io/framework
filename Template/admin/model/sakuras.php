<?php

use Joomla\DI\Container;
use Windwalker\Helper\QueryHelper;
use Windwalker\Model\ListModel;

/**
 * Class FlowerModelSakuras
 *
 * @since 1.0
 */
class FlowerModelSakuras extends ListModel
{
	/**
	 * Constructor
	 *
	 * @param   array              $config    An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @param   Container          $container Service container.
	 * @param   \JRegistry         $state     The model state.
	 * @param   \JDatabaseDriver   $db        The database adpater.
	 */
	public function __construct($config = array(), Container $container = null, \JRegistry $state = null, \JDatabaseDriver $db = null)
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

			$config['filter_fields'] = QueryHelper::mergeFilterFields(null, $config['filter_fields'], $config['tables']);
		}

		$this->config = $config;

		parent::__construct($config, $container, $state, $db);
	}

	/**
	 * getListQuery
	 *
	 * @return \JDatabaseQuery
	 */
	public function getListQuery()
	{
		$query = parent::getListQuery();

		$ordering    = $this->state->get('list.ordering',    'sakura.ordering');
		$direction   = $this->state->get('list.direction',   'ASC');
		$orderCol    = $this->state->get('list.orderCol',    $this->orderCol);

		$filters  = $this->state->get('filter', array());
		$searches = $this->state->get('search', array());

		// Build filter query
		$this->processFilters($query);

		// Build search query
		$this->processSearches($query);

		// Published
		if (!isset($filters['sakura.published']))
		{
			$query->where($query->quoteName('sakura.published') . ' >= 0');
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
			->from('#__flower_sakuras AS sakura')
			->leftJoin('#__categories  AS category  ON sakura.catid      = category.id')
			->leftJoin('#__users       AS user      ON sakura.created_by = user.id')
			->leftJoin('#__viewlevels  AS viewlevel ON sakura.access     = viewlevel.id')
			->leftJoin('#__languages   AS lang      ON sakura.language   = lang.lang_code')
			// ->where("")
			->order($ordering . ' ' . $direction)
			;

		// Debug here
		\AK::show((string) $query);

		return $query;
	}

	/**
	 * configureFilters
	 *
	 * @param \Windwalker\Model\Filter\FilterHelper $filterHelper
	 *
	 * @return  void
	 */
	protected function configureFilters($filterHelper)
	{
	}

	/**
	 * configureSearches
	 *
	 * @param \Windwalker\Model\Filter\SearchHelper $searchHelper
	 *
	 * @return  void
	 */
	protected function configureSearches($searchHelper)
	{
		$searchHelper->setHandler(
			'sakura.title',
			function($query, $field, $value)
			{
				return $query->quoteName($field) . ' LIKE ' . $query->quote($value . '%');
			}
		);
	}

}
