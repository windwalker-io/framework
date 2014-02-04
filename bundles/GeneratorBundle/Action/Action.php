<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Action;

use GeneratorBundle\Controller\TaskController;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Windwalker\DI\Container;

/**
 * Class Action
 *
 * @since 1.0
 */
abstract class Action
{
	/**
	 * Property container.
	 *
	 * @var  \Windwalker\DI\Container
	 */
	protected $container;

	/**
	 * Property app.
	 *
	 * @var  \Windwalker\Console\Application\Console
	 */
	protected $app;

	/**
	 * Contructor.
	 *
	 * @param Container $container
	 */
	public function __construct(Container $container = null)
	{
		$this->container = $container ? : Container::getInstance();

		$this->app = $this->container->get('app');
	}

	/**
	 * execute
	 *
	 * @param TaskController $controller
	 * @param array          $replace
	 *
	 * @return  void
	 */
	abstract public function execute(TaskController $controller, $replace = array());

	/**
	 * copy
	 *
	 * @param   string $src
	 * @param   string $dest
	 *
	 * @throws \RuntimeException
	 * @return  void
	 */
	public function copy($src, $dest)
	{
		if (is_file($src))
		{
			File::copy($src, $dest);

			$this->out('File created: ' . $dest);
		}
		elseif (is_dir($src))
		{
			Folder::copy($src, $dest);
		}
		else
		{
			throw new \RuntimeException('No such dir or file in : ' . $src);
		}
	}

	/**
	 * out
	 *
	 * @param string $msg
	 * @param bool   $nl
	 *
	 * @return  void
	 */
	protected function out($msg = null, $nl = true)
	{
		if ($msg)
		{
			$this->app->out($msg, $nl);
		}
	}
}
