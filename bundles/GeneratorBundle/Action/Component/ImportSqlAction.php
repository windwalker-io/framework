<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Action\Component;

use GeneratorBundle\Action\AbstractAction;
use CodeGenerator\Controller\TaskController;
use Windwalker\DI\Container;
use Windwalker\String\String;

/**
 * Class ImportSqlAction
 *
 * @since 1.0
 */
class ImportSqlAction extends AbstractAction
{
	/**
	 * doExecute
	 *
	 * @return  mixed
	 */
	public function doExecute()
	{
		// Load SQL file.
		@$installFile   = file_get_contents($this->config['dir.src'] . '/sql/install.sql');
		@$uninstallFile = file_get_contents($this->config['dir.src'] . '/sql/uninstall.sql');

		$installSql   = String::parseVariable($installFile, $this->config['replace']);
		$uninstallSql = String::parseVariable($uninstallFile, $this->config['replace']);

		// Prevent import twice
		$table = '#__' . $this->config['name'] . '_' . $this->config['replace.controller.list.name.lower'];

		$db = Container::getInstance()->get('db');

		try
		{
			$db->getTableColumns($table);
		}
		catch (\RuntimeException $e)
		{
			// Import sql
			$this->controller->out('Importing SQL to table: ' . $table);
			$this->executeSql($installSql);
			$this->controller->out('Imported');
		}

		if (!strpos($installSql, $table))
		{
			// Write SQL file to project.
			@$fp = fopen($this->config['dir.dest'] . '/sql/install.sql', 'a+');
			@fputs($fp, "\n\n\n" . $installSql);
			@fclose($fp);

			@$fp = fopen($this->config['dir.dest'] . '/sql/uninstall.sql', 'a+');
			@fputs($fp, "\n\n" . $uninstallSql);
			@fclose($fp);
		}
	}

	/**
	 * executeSql
	 *
	 * @param string $sql
	 *
	 * @return  void
	 */
	protected function executeSql($sql)
	{
		$db = Container::getInstance()->get('db');

		$queries = $db->splitSql($sql);

		foreach ($queries as $query)
		{
			if (!trim($query))
			{
				continue;
			}

			$db->setQuery($query)->execute();
		}
	}
}
