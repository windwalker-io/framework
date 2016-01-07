<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\DataMapper\Test;

use Windwalker\Database\Test\AbstractDatabaseTestCase;
use Windwalker\DataMapper\Adapter\AbstractDatabaseAdapter;
use Windwalker\DataMapper\Adapter\WindwalkerAdapter;

/**
 * The DatabaseTest class.
 * 
 * @since  2.0
 */
abstract class DatabaseTest extends AbstractDatabaseTestCase
{
	/**
	 * Property driver.
	 *
	 * @var  string
	 */
	protected static $driver = 'mysql';

	/**
	 * setUpBeforeClass
	 *
	 * @return  void
	 */
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		if (static::$dbo)
		{
			AbstractDatabaseAdapter::setInstance(new WindwalkerAdapter(static::$dbo));
		}
	}

	/**
	 * loadToDataset
	 *
	 * @param mixed  $query
	 * @param string $class
	 * @param string $dataClass
	 *
	 * @return  mixed
	 */
	protected function loadToDataset($query, $class = 'Windwalker\\Data\\DataSet', $dataClass = 'Windwalker\\Data\\Data')
	{
		$dataset = $this->db->setQuery($query)->loadAll(null, $dataClass);

		return new $class($dataset);
	}

	/**
	 * loadToData
	 *
	 * @param mixed  $query
	 * @param string $dataClass
	 *
	 * @return  mixed
	 */
	protected function loadToData($query, $dataClass = 'Windwalker\\Data\\Data')
	{
		$data = $this->db->setQuery($query)->loadOne($dataClass);

		return $data;
	}

	/**
	 * show
	 *
	 * @return  void
	 */
	protected function show()
	{
		foreach (func_get_args() as $key => $arg)
		{
			echo sprintf("\n[Value %d]\n", $key + 1);
			print_r($arg);
		}
	}
}
