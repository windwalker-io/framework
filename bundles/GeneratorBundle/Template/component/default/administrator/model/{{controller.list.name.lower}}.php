<?php

use Windwalker\DI\Container;
use Windwalker\Model\Filter\FilterHelper;
use Windwalker\Model\ListModel;

/**
 * Class {{extension.name.cap}}Model{{controller.list.name.cap}}
 *
 * @since 1.0
 */
class {{extension.name.cap}}Model{{controller.list.name.cap}} extends ListModel
{
	/**
	 * configureTables
	 *
	 * @return  void
	 */
	protected function configureTables()
	{
		$queryHelper = $this->getContainer()->get('model.{{controller.list.name.lower}}.helper.query', Container::FORCE_NEW);

		$queryHelper->addTable('{{controller.item.name.lower}}', '#__{{extension.name.lower}}_{{controller.list.name.lower}}')
			->addTable('category',  '#__categories', '{{controller.item.name.lower}}.catid      = category.id')
			->addTable('user',      '#__users',      '{{controller.item.name.lower}}.created_by = user.id')
			->addTable('viewlevel', '#__viewlevels', '{{controller.item.name.lower}}.access     = viewlevel.id')
			->addTable('lang',      '#__languages',  '{{controller.item.name.lower}}.language   = lang.lang_code');

		$this->filterFields = array_merge($this->filterFields, $queryHelper->getFilterFields());
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
		// Build ordering prefix
		if (!$ordering)
		{
			$table = $this->getTable('{{controller.item.name.cap}}');

			$ordering = property_exists($table, 'ordering') ? '{{controller.item.name.lower}}.ordering' : '{{controller.item.name.lower}}.id';

			$ordering = property_exists($table, 'catid') ? '{{controller.item.name.lower}}.catid, ' . $ordering : $ordering;
		}

		parent::populateState($ordering, 'ASC');
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
		// If no state filter, set published >= 0
		if (!isset($filters['{{controller.item.name.lower}}.published']) && property_exists($this->getTable(), 'published'))
		{
			$query->where($query->quoteName('{{controller.item.name.lower}}.published') . ' >= 0');
		}

		return parent::processFilters($query, $filters);
	}

	/**
	 * configureFilters
	 *
	 * @param FilterHelper $filterHelper
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
