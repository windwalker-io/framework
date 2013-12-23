<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2013 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Controller\Admin;

/**
 * Class FormController
 *
 * @since 1.0
 */
class FormController extends AdminController
{
	/**
	 * Property allowUrlParams.
	 *
	 * @var array
	 */
	protected $allowUrlParams = array(
		'tmpl',
		'layout',
		'return'
	);

	/**
	 * Instantiate the controller.
	 *
	 * @param   \JInput           $input  The input object.
	 * @param   \JApplicationCms  $app    The application object.
	 *
	 * @since  12.1
	 */
	public function __construct(\JInput $input = null, \JApplicationCms $app = null, $config = array())
	{
		parent::__construct($input, $app);

		if (!empty($config['allow_url_params']) && is_array($config['allow_url_params']))
		{
			array_merge($this->allowUrlParams, $config['allow_url_params']);
		}
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   12.2
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$input  = $this->input;
		$params = array();

		array_map(
			function($param) use($input, &$params)
			{
				$value = $input->getString($param);

				if ($value)
				{
					$params[$param] = $value;
				}
			},
			$this->allowUrlParams
		);

		if ($recordId)
		{
			$params[$urlVar] = $recordId;
		}

		return '&' . http_build_query($params);
	}

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   12.2
	 */
	protected function getRedirectToListAppend()
	{
		$input  = $this->input;
		$params = array();

		array_map(
			function($param) use($input, &$params)
			{
				$value = $input->getString($param);

				if ($value)
				{
					$params[$param] = $value;
				}
			},
			$this->allowUrlParams
		);

		return '&' . http_build_query($params);
	}
}
