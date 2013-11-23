<?php
/**
 * @package     Windwalker.Framework
 * @subpackage  Component
 * @author      Simon Asika <asika32764@gmail.com>
 * @copyright   Copyright (C) 2013 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

include_once AKPATH_COMPONENT . '/modeladmin.php';

/**
 * API User handle model.
 */
class AKResponseModelUser extends AKModelAdmin
{
	public $default_method;

	/**
	 * Login a user by username & password.
	 */
	public function login()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;

		$username = $input->getString('username');
		$password = $input->getString('password');

		// Execute Login
		$login_result = $app->login(array('username' => $username, 'password' => $password), array('remember' => true));

		// Build Result
		$result = new JObject();

		if (!$login_result)
		{
			$result->success = false;
			$result->status  = 'Invaild username or password.';

			return $result;
		}

		// Get Session Key
		$session = JFactory::getSession();
		$user    = JFactory::getUser();

		$result->success     = true;
		$result->session_key = $session->getId();

		return $result;
	}

	/**
	 * Logout a user by session key
	 */
	public function logout()
	{
		$app    = JFactory::getApplication();
		$user   = JFactory::getUser();
		$result = new JObject();

		if (!$user->get('id'))
		{
			$result->success = false;
			$result->status  = 'No user information.';

			return $result;
		}

		$app->logout($user->get('id'));

		$result->success = true;

		return $result;
	}

	/**
	 * Get user information.
	 */
	public function getUser()
	{
		$id     = JRequest::getVar('id');
		$result = new JObject();
		$user   = JFactory::getUser($id);

		if ($user->get('guest'))
		{
			$result->user   = false;
			$result->status = 'No user information.';

			return $result;
		}

		unset($user->password);
		unset($user->password_clear);

		$result->user = $user;

		return $result;
	}
}