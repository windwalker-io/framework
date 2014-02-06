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
	 */
	public function __construct(Container $container, IOInterface $io, Registry $config = null)
	{
		// Get item & list name
		$ctrl = $config['ctrl'] ? : $io->getArgument(1);

		$ctrl = explode('.', $ctrl);

		$inflector = \JStringInflector::getInstance();

		if (empty($ctrl[0]))
		{
			$ctrl[0] = 'item';
		}

		if (empty($ctrl[1]))
		{
			$ctrl[1] = $inflector->toPlural($ctrl[0]);
		}

		list($itemName, $listName) = $ctrl;

		$this->replace['extension.element.lower'] = strtolower($config['element']);
		$this->replace['extension.element.upper'] = strtoupper($config['element']);
		$this->replace['extension.element.cap']   = ucfirst($config['element']);

		$this->replace['extension.name.lower']    = strtolower($config['name']);
		$this->replace['extension.name.upper']    = strtoupper($config['name']);
		$this->replace['extension.name.cap']      = ucfirst($config['name']);

		$this->replace['controller.list.name.lower'] = strtolower($listName);
		$this->replace['controller.list.name.upper'] = strtoupper($listName);
		$this->replace['controller.list.name.cap']   = ucfirst($listName);

		$this->replace['controller.item.name.lower'] = strtolower($itemName);
		$this->replace['controller.item.name.upper'] = strtoupper($itemName);
		$this->replace['controller.item.name.cap']   = ucfirst($itemName);

		// Set replace to config.
		foreach ($this->replace as $key => $val)
		{
			$config->set('replace.' . $key, $val);
		}

		// Set copy dir.
		$config->set('dir.dest', PathHelper::get(strtolower($config['element']), $config['client']));

		$config->set('dir.tmpl', GENERATOR_BUNDLE_PATH . '/Template/' . $config['extension'] . '/' . $config['template']);

		$config->set('dir.src', $config->get('dir.tmpl') . '/' . $config['client']);

		parent::__construct($container, $io, $config);
	}
}
