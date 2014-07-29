<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Query;

/**
 * Class QueryBuilder
 *
 * @since 1.0
 */
abstract class AbstractQueryBuilder
{
	/**
	 * Property query.
	 *
	 * @var  null|Query
	 */
	protected $query = null;

	/**
	 * Class init.
	 *
	 * @param Query $query
	 */
	public function __construct(Query $query)
	{
		$this->query = $query;
	}
}
 