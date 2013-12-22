<?php
/**
 * Part of Windwalker RAD framework package.
 *
 * @author     Simon Asika <asika32764@gmail.com>
 * @copyright  Copyright (C) 2014 Asikart. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Windwalker\Controller;

use JInput;
use JApplicationBase;

/**
 * Class Controller
 *
 * @since 2.0
 */
class Controller extends \JControllerBase
{
	/**
	 * Prefix for the view and model classes
	 *
	 * @var  string
	 */
	protected $prefix = '';

	/**
	 * Permission needed for the action. Defaults to most restrictive
	 *
	 * @var  string
	 */
	protected $permission = 'core.admin';

	/**
	 * Property componentPath.
	 *
	 * @var string
	 */
	protected $componentPath = '';

	/**
	 * Property reflection.
	 *
	 * @var \ReflectionClass
	 */
	protected $reflection;

	/**
	 * Property name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Instantiate the controller.
	 *
	 * @param   JInput            $input  The input object.
	 * @param   JApplicationBase  $app    The application object.
	 *
	 * @since  12.1
	 */
	public function __construct(JInput $input = null, JApplicationBase $app = null)
	{
		parent::__construct($input, $app);
	}

	/**
	 * execute
	 *
	 * @return $this|bool
	 */
	public function execute()
	{
	}

	/**
	 * getComponentPath
	 *
	 * @return string
	 */
	public function getComponentPath()
	{
		return $this->componentPath;
	}

	/**
	 * setComponentPath
	 *
	 * @param string $componentPath
	 *
	 * @return $this
	 */
	public function setComponentPath($componentPath)
	{
		$this->componentPath = $componentPath;

		return $this;
	}

	/**
	 * getReflection
	 *
	 * @return \ReflectionClass
	 */
	public function getReflection()
	{
		if ($this->reflection)
		{
			return $this->reflection;
		}

		$this->reflection = new \ReflectionClass($this);

		return $this->reflection;
	}

	/**
	 * getPrefix
	 *
	 * @return string
	 */
	public function getPrefix()
	{
		return $this->prefix;
	}

	/**
	 * setPrefix
	 *
	 * @param string $prefix
	 *
	 * @return $this
	 */
	public function setPrefix($prefix)
	{
		$this->prefix = $prefix;

		return $this;
	}

	/**
	 * checkToken
	 *
	 * @return void
	 */
	protected function checkToken()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JInvalid_Token'));
	}
}
