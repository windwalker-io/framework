<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Controller\Component;

use GeneratorBundle\Controller\JoomlaExtensionController;
use Joomla\Console\Prompter\TextPrompter;
use Joomla\Registry\Registry;
use Windwalker\Console\Command\Command;
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
	 * @param   Command   $command
	 * @param   Registry  $config
	 */
	public function __construct(Command $command, Registry $config = null)
	{
		$ctrl = $command->getArgument(1);

		$ctrl = explode('.', $ctrl);

		$prompter = new TextPrompter;

		if (empty($ctrl[0]))
		{
			$ctrl[0] = $prompter->ask('Please enter controller item name: ', 'item');
		}

		if (empty($ctrl[1]))
		{
			$ctrl[1] = $prompter->ask('Please enter controller list name: ', 'items');
		}

		list($itemName, $listName) = $ctrl;

		$this->replace['controller.list.name.lower'] = strtolower($listName);
		$this->replace['controller.list.name.upper'] = strtoupper($listName);
		$this->replace['controller.list.name.cap']   = ucfirst($listName);

		$this->replace['controller.item.name.lower'] = strtolower($itemName);
		$this->replace['controller.item.name.upper'] = strtoupper($itemName);
		$this->replace['controller.item.name.cap']   = ucfirst($itemName);

		parent::__construct($command, $config);
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
