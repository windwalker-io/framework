<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Controller\Module;

use CodeGenerator\IO\IOInterface;
use GeneratorBundle\Controller\JoomlaExtensionController;
use Joomla\Registry\Registry;
use Windwalker\DI\Container;

/**
 * Class PluginController
 *
 * @since 1.0
 */
abstract class ModuleController extends JoomlaExtensionController
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
		$config['client'] = $config['client'] ? : 'site';

		$this->replace['module.client'] = 'client="' . $config['client'] . '"';

		parent::__construct($container, $io, $config);

		// Set copy dir.
		$config->set('dir.src', $config->get('dir.tmpl'));
	}
}
