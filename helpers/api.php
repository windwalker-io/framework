<?php
/**
 * @package     Windwalker.Framework
 * @subpackage  Helpers
 * @author      Simon Asika <asika32764@gmail.com>
 * @copyright   Copyright (C) 2013 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * API System Helper
 *
 * @package     Windwalker.Framework
 * @subpackage  Helpers
 */
class AKHelperApi
{
	static protected $sdk_instance = array();

	/**
	 * init
	 */
	public static function initClient($id, $option = array())
	{
		$component = $option['component'];

		if (substr($component, 0, 4) == 'com_')
		{
			$component         = substr($component, 4);
			$compoenent_option = 'com_' . $component;
		}
		else
		{
			$compoenent_option = 'com_' . $component;
		}

		$option['component'] = $component;
		$option['option']    = $compoenent_option;

		// Include Request SDK
		include_once AKPATH_COMPONENT . '/api/models/request/item.php';
		include_once AKPATH_COMPONENT . '/api/models/request/list.php';
		include_once AKPATH_COMPONENT . '/api/tables/request.php';

		include_once AKPATH_COMPONENT . '/api/sdk.php';

		// Init SDK
		$params = JComponentHelper::getParams($compoenent_option);

		$host = $params->get('ApiSystem_Host');

		if ($host)
		{
			$option['host'] = $host;
		}

		self::getSDK($id, $option);
	}

	/**
	 * initServer
	 */
	public static function initServer()
	{
		include_once AKPATH_COMPONENT . '/api/models/response/item.php';
		include_once AKPATH_COMPONENT . '/api/models/response/list.php';

		include_once AKPATH_COMPONENT . '/api/models/response/user.php';
		include_once AKPATH_COMPONENT . '/api/models/response/users.php';
	}

	/**
	 * Get API SDK For AKApi System.
	 */
	public static function getSDK($id = null, $option = array())
	{
		if (!$id)
		{
			$id = AKHelper::_('path.getOption');
		}

		if (!empty(self::$sdk_instance[$id]))
		{
			return self::$sdk_instance[$id];
		}
		else
		{
			$component = $option['component'];

			self::$sdk_instance[$id] = $service = AKRequestSDK::getInstance($option);

			return $service;
		}
	}

	/**
	 * Update whole table.
	 */
	public static function update($name, $option = null)
	{
		$option = $option ? $option : AKHelper::_('path.getOption');

		if (substr($option, 0, 4) == 'com_')
		{
			$component = substr($option, 4);
		}

		$model = JModelLegacy::getInstance(ucfirst($name), ucfirst($component) . 'Model', array('ignore_request' => true));

		if (!($model instanceof AKRequestModelList))
		{
			JError::raiseWarning(500, ucfirst($component) . 'Model' . ucfirst($name) . " Not instance of AKRequestModelList.");

			return false;
		}

		if (!$model->update())
		{
			JError::raiseWarning(500, $model->getError());

			return false;
		}

		return true;
	}
}