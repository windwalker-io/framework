<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2015 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Database\Driver\Postgresql;

use Windwalker\Database\Driver\Pdo\PdoDriver;

/**
 * The PostgresqlDriver class.
 *
 * @since  {DEPLOY_VERSION}
 */
class PostgresqlDriver extends PdoDriver
{
	/**
	 * Property name.
	 *
	 * @var  string
	 */
	protected $name = 'postgresql';

	/**
	 * Constructor.
	 *
	 * @param   \PDO  $connection The pdo connection object.
	 * @param   array $options    List of options used to configure the connection
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function __construct(\PDO $connection = null, $options = array())
	{
		$options['driver'] = 'pgsql';
		$options['charset'] = (isset($options['charset'])) ? $options['charset'] : 'utf8';

		parent::__construct($connection, $options);
	}
}
