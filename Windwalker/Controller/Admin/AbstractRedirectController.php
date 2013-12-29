<?php

namespace Windwalker\Controller\Admin;

use Windwalker\Controller\Controller;

/**
 * Class AbstractRedirectController
 *
 * @since 1.0
 */
abstract class AbstractRedirectController extends Controller
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
	 * Property viewItem.
	 *
	 * @var
	 */
	protected $viewItem;

	/**
	 * Property viewList.
	 *
	 * @var
	 */
	protected $viewList;

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
		parent::__construct($input, $app, $config);

		if (!empty($config['allow_url_params']) && is_array($config['allow_url_params']))
		{
			array_merge($this->allowUrlParams, $config['allow_url_params']);
		}
	}

	/**
	 * prepareExecute
	 *
	 * @return void
	 */
	protected function prepareExecute()
	{
		parent::prepareExecute();
	}

	/**
	 * redirectToItem
	 *
	 * @param null   $recordId
	 * @param string $urlVar
	 * @param null   $msg
	 * @param null   $type
	 *
	 * @return void
	 */
	public function redirectToItem($recordId = null, $urlVar = 'id', $msg = null, $type = null)
	{
		$this->app->redirect(\JRoute::_($this->getRedirectItemUrl($recordId, $urlVar), false), $msg, $type);
	}

	/**
	 * redirectToList
	 *
	 * @param null $msg
	 * @param null $type
	 *
	 * @return void
	 */
	public function redirectToList($msg = null, $type = null)
	{
		$this->input->set('layout', null);

		$this->app->redirect(\JRoute::_($this->getRedirectListUrl(), false), $msg, $type);
	}

	/**
	 * Set a URL for browser redirection.
	 *
	 * @param   string $url  URL to redirect to.
	 * @param   string $msg  Message to display on redirect. Optional, defaults to value set internally by controller, if any.
	 * @param   string $type Message type. Optional, defaults to 'message' or the type set by a previous call to setMessage.
	 *
	 * @return  \JController  This object to support chaining.
	 */
	public function redirect($url, $msg = null, $type = null)
	{
		$this->app->redirect($url, $msg, $type);
	}

	/**
	 * getRedirectItemUrl
	 *
	 * @param null   $recordId
	 * @param string $urlVar
	 *
	 * @return string
	 */
	protected function getRedirectItemUrl($recordId = null, $urlVar = 'id')
	{
		return 'index.php?option=' . $this->option . '&view=' . strtolower($this->getName())
			. $this->getRedirectItemAppend($recordId, $urlVar);
	}

	/**
	 * getRedirectListUrl
	 *
	 * @return string
	 */
	protected function getRedirectListUrl()
	{
		return 'index.php?option=' . $this->option . '&view=' . strtolower($this->viewList)
			. $this->getRedirectListAppend();
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
	protected function getRedirectItemAppend($recordId = null, $urlVar = 'id')
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

		return (count($params)) ? '&' . http_build_query($params) : '';
	}

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   12.2
	 */
	protected function getRedirectListAppend()
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

		return (count($params)) ? '&' . http_build_query($params) : '';
	}
}
