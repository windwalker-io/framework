<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Command\Controller;

use Windwalker\Console\Controller\Controller as ConsoleController;

/**
 * Class GeneratorController
 *
 * @since 1.0
 */
class GeneratorController extends ConsoleController
{
	/**
	 * Property type.
	 *
	 * @var  string
	 */
	protected $type = null;

	/**
	 * Property name.
	 *
	 * @var  string
	 */
	protected $name = null;

	/**
	 * Property element.
	 *
	 * @var  string
	 */
	protected $element = null;

	/**
	 * Property group.
	 *
	 * @var  string
	 */
	protected $group = null;

	/**
	 * Property client.
	 *
	 * @var  string
	 */
	protected $client = null;

	/**
	 * Property template.
	 *
	 * @var  string
	 */
	protected $template = null;

	/**
	 * The mapper to find extension type.
	 *
	 * @var    array
	 */
	protected $extMapper = array(
		'com_' => 'component',
		'mod_' => 'module',
		'plg_' => 'plugin',
		// 'lib_' => 'library',
		// 'tpl_' => 'template'
	);

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
		$input = $this->input;

		// Prepare basic data.
		$element = $this->getOrClose(0, 'Please enter extension element.');

		list($extension, $name, $element, $group) = $this->extractElement($element);

		$this->element = $element;
		$this->type    = $extension;
		$this->name    = $name;
		$this->group   = $group;
		$this->client  = $this->command->getOption('c');

		if ($this->client == 'admin')
		{
			$this->client = 'administrator';
		}


	}

	/**
	 * Extract element.
	 *
	 * @param   string  $element  he extension element name, example: com_content or plg_group_name
	 *
	 * @return  array
	 *
	 * @throws  \InvalidArgumentException
	 */
	protected function extractElement($element)
	{
		$prefix = substr($element, 0, 4);

		$ext = static::getExtType($prefix);

		$group = '';
		$name = substr($element, 4);

		// Get group
		if ($ext == 'plugin')
		{
			$name  = explode('_', $name);

			$group = array_shift($name);

			$name  = implode('_', $name);

			if (!$name)
			{
				throw new \InvalidArgumentException(sprintf('Plugin name need group, eg: "plg_group_name", "%s" given.', $element));
			}
		}

		return array($ext, $name, $prefix . $name, $group);
	}

	/**
	 * getExtType
	 *
	 * @param string $prefix
	 *
	 * @return  mixed
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function getExtType($prefix)
	{
		if (empty($this->extMapper[$prefix]))
		{
			throw new \InvalidArgumentException(sprintf('Invalid extension prefix "%s".', $prefix));
		}

		return $this->extMapper[$prefix];
	}
}
