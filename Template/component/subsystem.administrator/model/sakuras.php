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
	 * populateState
	 *
	 * @param null $ordering
	 * @param null $direction
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState('sakura.catid, sakura.ordering', 'ASC');
	}

	/**
	 * getListQuery
	 *
	 * @return \JDatabaseQuery
	 */
	public function getListQuery()
	{
		$query = parent::getListQuery();

		// Build filter query
		$this->processFilters($query);

		// Build search query
		$this->processSearches($query);

		// Ordering
		$this->processOrdering($query);

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
			;

		// Debug here
		\AK::show((string) $query);

		return $query;
	}

	/**
	 * processFilters
	 *
	 * @param JDatabaseQuery $query
	 * @param array          $filters
	 *
	 * @return  JDatabaseQuery
	 */
	protected function processFilters(\JDatabaseQuery $query, $filters = array())
	{
		// Published
		if (!isset($filters['sakura.published']))
		{
			$query->where($query->quoteName('sakura.published') . ' >= 0');
		}

		return parent::processFilters($query, $filters);
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
	}
}
