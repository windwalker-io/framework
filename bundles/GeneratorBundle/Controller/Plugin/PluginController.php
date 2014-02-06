<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Controller\Plugin;

use CodeGenerator\IO\IOInterface;
use GeneratorBundle\Controller\JoomlaExtensionController;
use Joomla\Registry\Registry;
use Windwalker\DI\Container;
use Windwalker\Helper\PathHelper;

/**
 * Class PluginController
 *
 * @since 1.0
 */
abstract class PluginController extends JoomlaExtensionController
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
		// Reset element back to plg_group_name
		$config['element'] = 'plg_' . strtolower($config['group']) . '_' . strtolower($config['name']);
		$config['client']  = 'site';

		$this->replace['plugin.group.lower'] = strtolower($config['group']);
		$this->replace['plugin.group.upper'] = strtoupper($config['group']);
		$this->replace['plugin.group.cap']   = ucfirst($config['group']);

		parent::__construct($container, $io, $config);

		// Set copy dir.
		$config->set('dir.src', $config->get('dir.tmpl'));
	}
}
