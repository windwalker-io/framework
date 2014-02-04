<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Prompter;

/**
 * Class ElementPrompter
 *
 * @since 1.0
 */
class ElementPrompter extends NotNullPrompter
{
	/**
	 * Returning message if valid fail.
	 *
	 * @var  string
	 *
	 * @since  1.0
	 */
	protected $noValidMessage = '  Not valid element name.';

	/**
	 * Returning message if valid fail and close.
	 *
	 * @var  string
	 *
	 * @since  1.0
	 */
	protected $closeMessage = '  Please enter valid element.';

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
	 * Get callable handler.
	 *
	 * @return  callable  The validate callback.
	 *
	 * @since   1.0
	 */
	public function getHandler()
	{
		$handler = parent::getHandler();

		return function($value) use ($handler)
		{
			if (!call_user_func($handler, $value))
			{
				return false;
			}

			$prefix = substr($value, 0, 4);

			return $this->validateExtType($prefix);
		};
	}

	/**
	 * getExtType
	 *
	 * @param string $prefix
	 *
	 * @return  mixed
	 */
	protected function validateExtType($prefix)
	{
		if (empty($this->extMapper[$prefix]))
		{
			return false;
		}

		return true;
	}
}
