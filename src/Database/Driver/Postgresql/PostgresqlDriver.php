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

	/**
	 * This function replaces a string identifier <var>$prefix</var> with the string held is the
	 * <var>tablePrefix</var> class variable.
	 *
	 * @param   string  $sql     The SQL statement to prepare.
	 * @param   string  $prefix  The common table prefix.
	 *
	 * @return  string  The processed SQL statement.
	 *
	 * @since   1.0
	 */
	public function replacePrefix($sql, $prefix = '#__')
	{
		$sql = trim($sql);

		if (strpos($sql, '\''))
		{
			// Sequence name quoted with ' ' but need to be replaced
			if (strpos($sql, 'currval'))
			{
				$sql = explode('currval', $sql);

				for ($nIndex = 1; $nIndex < count($sql); $nIndex = $nIndex + 2)
				{
					$sql[$nIndex] = str_replace($prefix, $this->tablePrefix, $sql[$nIndex]);
				}

				$sql = implode('currval', $sql);
			}

			// Sequence name quoted with ' ' but need to be replaced
			if (strpos($sql, 'nextval'))
			{
				$sql = explode('nextval', $sql);

				for ($nIndex = 1; $nIndex < count($sql); $nIndex = $nIndex + 2)
				{
					$sql[$nIndex] = str_replace($prefix, $this->tablePrefix, $sql[$nIndex]);
				}

				$sql = implode('nextval', $sql);
			}

			// Sequence name quoted with ' ' but need to be replaced
			if (strpos($sql, 'setval'))
			{
				$sql = explode('setval', $sql);

				for ($nIndex = 1; $nIndex < count($sql); $nIndex = $nIndex + 2)
				{
					$sql[$nIndex] = str_replace($prefix, $this->tablePrefix, $sql[$nIndex]);
				}

				$sql = implode('setval', $sql);
			}

			$explodedQuery = explode('\'', $sql);

			for ($nIndex = 0; $nIndex < count($explodedQuery); $nIndex = $nIndex + 2)
			{
				if (strpos($explodedQuery[$nIndex], $prefix))
				{
					$explodedQuery[$nIndex] = str_replace($prefix, $this->tablePrefix, $explodedQuery[$nIndex]);
				}
			}

			$replacedQuery = implode('\'', $explodedQuery);
		}
		else
		{
			$replacedQuery = str_replace($prefix, $this->tablePrefix, $sql);
		}

		return $replacedQuery;
	}
}
