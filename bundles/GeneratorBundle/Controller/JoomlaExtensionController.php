<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Controller;

use CodeGenerator\Controller\TaskController;
use CodeGenerator\IO\IOInterface;
use Joomla\Registry\Registry;
use Windwalker\Console\Command\Command;
use Windwalker\DI\Container;
use Windwalker\Helper\PathHelper;

/**
 * Class JoomlaExtensionController
 *
 * @since 1.0
 */
abstract class JoomlaExtensionController extends TaskController
{
	/**
	 * Constructor.
	 *
	 * @param   \Windwalker\DI\Container      $container
	 * @param   \CodeGenerator\IO\IOInterface $io
	 * @param   Registry                      $config
	 *
	 * @internal param \Windwalker\Console\Command\Command $command
	 */
	public function __construct(Container $container, IOInterface $io, Registry $config = null)
	{
		$this->replace['extension.element.lower'] = strtolower($config['element']);
		$this->replace['extension.element.upper'] = strtoupper($config['element']);
		$this->replace['extension.element.cap']   = ucfirst($config['element']);

		$this->replace['extension.name.lower']    = strtolower($config['name']);
		$this->replace['extension.name.upper']    = strtoupper($config['name']);
		$this->replace['extension.name.cap']      = ucfirst($config['name']);

		foreach ($this->replace as $key => $val)
		{
			$config->set('replace.' . $key, $val);
		}

		// Set copy dir.
		$config->set('dir.dest', PathHelper::get(strtolower($config['element']), $config['client']));

		$config->set('dir.tmpl', dirname(__DIR__) . '/Template/' . $config['extension'] . '/' . $config['template']);

		$config->set('dir.src', $config->get('dir.tmpl') . '/' . $config['client']);

		parent::__construct($container, $io, $config);
	}
}
