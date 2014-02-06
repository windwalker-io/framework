<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Controller\Component;

use CodeGenerator\IO\IOInterface;
use GeneratorBundle\Controller\JoomlaExtensionController;

use Joomla\Console\Prompter\TextPrompter;
use Joomla\Registry\Registry;

use Windwalker\DI\Container;
use Windwalker\Helper\PathHelper;

/**
 * Class ComponentController
 *
 * @since 1.0
 */
abstract class ComponentController extends JoomlaExtensionController
{
	/**
	 * Constructor.
	 *
	 * @param Container   $container
	 * @param IOInterface $io
	 * @param Registry    $config
	 */
	public function __construct(Container $container, IOInterface $io, Registry $config = null)
	{
		parent::__construct($container, $io, $config);

		// Load config json
		$this->config->loadFile(__DIR__ . '/config.json');
	}

	/**
	 * Execute the controller.
	 *
	 * @return  boolean  True if controller finished execution, false if the controller did not
	 *                   finish execution. A controller might return false if some precondition for
	 *                   the controller to run has not been satisfied.
	 *
	 * @since   12.1
	 * @throws  \LogicException
	 * @throws  \RuntimeException
	 */
	public function execute()
	{
		$config = $this->config;

		if (!$config['client'])
		{
			$config['client'] = 'site';

			$this->configurePath()->doExecute();

			$config['client'] = 'administrator';

			$this->configurePath()->doExecute();
		}
		else
		{
			$config['client'] = ($config['client'] == 'site') ? $config['client'] : 'administrator';

			$this->configurePath()->doExecute();
		}

		return true;
	}

	/**
	 * Do Execute.
	 *
	 * @return  void
	 */
	abstract protected function doExecute();

	/**
	 * configurePath
	 *
	 * @return  $this
	 */
	protected function configurePath()
	{
		$config = $this->config;

		$config->set('dir.dest', PathHelper::get(strtolower($config['element']), $config['client']));

		$config->set('dir.tmpl', GENERATOR_BUNDLE_PATH . '/Template/' . $config['extension'] . '/' . $config['template']);

		$config->set('dir.src', $config->get('dir.tmpl') . '/' . $config['client']);

		return $this;
	}
}
