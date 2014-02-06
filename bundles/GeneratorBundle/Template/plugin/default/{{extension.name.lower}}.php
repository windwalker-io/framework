<?php
/**
 * @package        Asikart.Plugin
 * @subpackage     {{plugin.group.lower}}.plg_{{extension.name.lower}}
 * @copyright      Copyright (C) 2012 Asikart.com, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * {{extension.name.cap}} {{plugin.group.cap}} Plugin
 *
 * @package        Joomla.Plugin
 * @subpackage     {{plugin.group.cap}}.{{extension.name.lower}}
 * @since          1.0
 */
class Plg{{plugin.group.cap}}{{extension.name.cap}} extends JPlugin
{
	/**
	 * Property self.
	 *
	 * @var  Plg{{plugin.group.cap}}{{extension.name.cap}}
	 */
	public static $self;

	/**
	 * Constructor
	 *
	 * @param  object  $subject The object to observe
	 * @param  array   $config  An array that holds the plugin configuration
	 */
	public function __construct($subject, $config)
	{
		parent::__construct($subject, $config);

		$this->loadLanguage();
		$this->app = JFactory::getApplication();

		self::$self = $this;
	}

	/**
	 * Get self object.
	 *
	 * @return  mixed
	 */
	public static function getInstance()
	{
		return self::$self;
	}

	// {{plugin.group.cap}} Events
	// ======================================================================================

	/**
	 * onAfterInitialise
	 *
	 * @return  void
	 */
	public function onAfterInitialise()
	{
	}

	/**
	 * onAfterRoute
	 *
	 * @return  void
	 */
	public function onAfterRoute()
	{
	}

	/**
	 * onAfterDispatch
	 *
	 * @return  void
	 */
	public function onAfterDispatch()
	{
	}

	/**
	 * onAfterRender
	 *
	 * @return  void
	 */
	public function onAfterRender()
	{
	}

	// Content Events
	// ======================================================================================

	/**
	 * {{extension.name.cap}} prepare content method
	 * Method is called by the view
	 *
	 * @param   string $context The context of the content being passed to the plugin.
	 * @param   object $article The content object.  Note $article->text is also available.
	 * @param   object $params  The content params.
	 * @param   int    $page    The 'page' number.
	 *
	 * @return  void
	 */
	public function onContentPrepare($context, $article, $params, $page = 0)
	{
		$app = JFactory::getApplication();

		// Do some stuff

		if ($path = $this->includeEvent(__FUNCTION__))
		{
			@include $this->includeEvent(__FUNCTION__);
		}
	}

	/**
	 * {{extension.name.cap}} after display title method
	 * Method is called by the view and the results are imploded and displayed in a placeholder
	 *
	 * @param   string $context The context of the content being passed to the plugin.
	 * @param   object $article The content object.  Note $article->text is also available.
	 * @param   object $params  The content params.
	 * @param   int    $page    The 'page' number.
	 *
	 * @return  string
	 */
	public function onContentAfterTitle($context, $article, $params, $page = 0)
	{
		$app    = JFactory::getApplication();
		$result = null;

		// Do some stuff

		if ($path = $this->includeEvent(__FUNCTION__))
		{
			@include $this->includeEvent(__FUNCTION__);
		}

		return $result;
	}

	/**
	 * {{extension.name.cap}} before display content method
	 * Method is called by the view and the results are imploded and displayed in a placeholder
	 *
	 * @param   string $context The context of the content being passed to the plugin.
	 * @param   object $article The content object.  Note $article->text is also available.
	 * @param   object $params  The content params.
	 * @param   int    $page    The 'page' number.
	 *
	 * @return  string
	 */
	public function onContentBeforeDisplay($context, $article, $params, $page = 0)
	{
		$app    = JFactory::getApplication();
		$result = null;

		// Do some stuff

		if ($path = $this->includeEvent(__FUNCTION__))
		{
			@include $this->includeEvent(__FUNCTION__);
		}

		return $result;
	}

	/**
	 * {{extension.name.cap}} after display content method
	 * Method is called by the view and the results are imploded and displayed in a placeholder
	 *
	 * @param   string $context The context of the content being passed to the plugin.
	 * @param   object $article The content object.  Note $article->text is also available.
	 * @param   object $params  The content params.
	 * @param   int    $page    The 'page' number.
	 *
	 * @return  string
	 */
	public function onContentAfterDisplay($context, $article, $params, $page = 0)
	{
		$app    = JFactory::getApplication();
		$result = null;

		// Do some stuff

		if ($path = $this->includeEvent(__FUNCTION__))
		{
			@include $this->includeEvent(__FUNCTION__);
		}

		return $result;
	}

	/**
	 * {{extension.name.cap}} before save content method
	 * Method is called right before content is saved into the database.
	 * Article object is passed by reference, so any changes will be saved!
	 * NOTE:  Returning false will abort the save with an error.
	 *You can set the error by calling $article->setError($message)
	 *
	 * @param   string $context The context of the content being passed to the plugin.
	 * @param   object $article A JTableContent object.
	 * @param   bool   $isNew   If the content is just about to be created.
	 *
	 * @return  bool  If false, abort the save.
	 */
	public function onContentBeforeSave($context, $article, $isNew)
	{
		$app    = JFactory::getApplication();
		$result = array();

		// Do some stuff

		if ($path = $this->includeEvent(__FUNCTION__))
		{
			@include $this->includeEvent(__FUNCTION__);
		}

		return $this->resultBool($result);
	}

	/**
	 * {{extension.name.cap}} after save content method
	 * Article is passed by reference, but after the save, so no changes will be saved.
	 * Method is called right after the content is saved
	 *
	 * @param   string  $context The context of the content being passed to the plugin.
	 * @param   object  $article A JTableContent object.
	 * @param   boolean $isNew   If the content is just about to be created.
	 *
	 * @return  boolean
	 */
	public function onContentAfterSave($context, $article, $isNew)
	{
		$app  = JFactory::getApplication();

		// Do some stuff

		if ($path = $this->includeEvent(__FUNCTION__))
		{
			@include $this->includeEvent(__FUNCTION__);
		}

		return true;
	}

	/**
	 * {{extension.name.cap}} before delete method.
	 *
	 * @param   string $context The context for the content passed to the plugin.
	 * @param   object $data    The data relating to the content that is to be deleted.
	 *
	 * @return  boolean  False to abort.
	 */
	public function onContentBeforeDelete($context, $data)
	{
		$result = array();

		// Do some stuff

		if ($path = $this->includeEvent(__FUNCTION__))
		{
			@include $this->includeEvent(__FUNCTION__);
		}

		return $this->resultBool($result);
	}

	/**
	 * {{extension.name.cap}} after delete method.
	 *
	 * @param   string $context The context for the content passed to the plugin.
	 * @param   object $data    The data relating to the content that is to be deleted.
	 *
	 * @return  boolean
	 */
	public function onContentAfterDelete($context, $data)
	{
		$result = array();

		// Do some stuff

		if ($path = $this->includeEvent(__FUNCTION__))
		{
			@include $this->includeEvent(__FUNCTION__);
		}

		return $this->resultBool($result);
	}

	/**
	 * {{extension.name.cap}} on change state method.
	 *
	 * @param   string $context The context for the content passed to the plugin.
	 * @param   array  $pks     A list of primary key ids of the content that has changed state.
	 * @param   int    $value   The value of the state that the content has been changed to.
	 *
	 * @return  boolean
	 */
	public function onContentChangeState($context, $pks, $value)
	{
		$result = array();

		// Do some stuff

		if ($path = $this->includeEvent(__FUNCTION__))
		{
			@include $this->includeEvent(__FUNCTION__);
		}

		return $this->resultBool($result);
	}



	// Form Events
	// ====================================================================================

	/**
	 * Pre process form hook.
	 *
	 * @param   JForm $form The form to be altered.
	 * @param   array $data The associated data for the form.
	 *
	 * @return  boolean
	 */
	public function onContentPrepareForm($form, $data)
	{
		$app    = JFactory::getApplication();
		$result = null;

		// Do some stuff

		if ($path = $this->includeEvent(__FUNCTION__))
		{
			@include $this->includeEvent(__FUNCTION__);
		}

		return $result;
	}

	// User Events
	// ====================================================================================

	/**
	 * Utility method to act on a user after it has been saved.
	 *
	 * @param   array   $user    Holds the new user data.
	 * @param   boolean $isNew   True if a new user is stored.
	 * @param   boolean $success True if user was succesfully stored in the database.
	 * @param   string  $msg     Message.
	 *
	 * @return  boolean
	 */
	public function onUserBeforeSave($user, $isNew, $success, $msg)
	{
		$result = array();

		// Do some stuff

		if ($path = $this->includeEvent(__FUNCTION__))
		{
			@include $this->includeEvent(__FUNCTION__);
		}

		return $this->resultBool($result);
	}

	/**
	 * Utility method to act on a user after it has been saved.
	 *
	 * @param   array   $user    Holds the new user data.
	 * @param   boolean $isNew   True if a new user is stored.
	 * @param   boolean $success True if user was succesfully stored in the database.
	 * @param   string  $msg     Message.
	 *
	 * @return    boolean
	 */
	public function onUserAfterSave($user, $isNew, $success, $msg)
	{
		$result = array();

		// Do some stuff

		if ($path = $this->includeEvent(__FUNCTION__))
		{
			@include $this->includeEvent(__FUNCTION__);
		}

		return $this->resultBool($result);
	}

	/**
	 * This method should handle any login logic and report back to the subject
	 *
	 * @param   array $user    Holds the user data
	 * @param   array $options Array holding options (remember, autoregister, group)
	 *
	 * @return  boolean  True on success
	 */
	public function onUserLogin($user, $options = array())
	{
		$result = array();

		// Do some stuff

		if ($path = $this->includeEvent(__FUNCTION__))
		{
			@include $this->includeEvent(__FUNCTION__);
		}

		return $this->resultBool($result);
	}

	/**
	 * This method should handle any logout logic and report back to the subject
	 *
	 * @param   array $user    Holds the user data.
	 * @param   array $options Array holding options (client, ...).
	 *
	 * @return  object  True on success
	 */
	public function onUserLogout($user, $options = array())
	{
		$result = array();

		// Do some stuff

		if ($path = $this->includeEvent(__FUNCTION__))
		{
			@include $this->includeEvent(__FUNCTION__);
		}

		return $this->resultBool($result);
	}

	/**
	 * Utility method to act on a user before it has been saved.
	 *
	 * @param   array   $user    Holds the new user data.
	 * @param   boolean $isnew   True if a new user is stored.
	 * @param   boolean $success True if user was succesfully stored in the database.
	 * @param   string  $msg     Message.
	 *
	 * @return  boolean
	 */
	public function onUserBeforeDelete($user, $isnew, $success, $msg)
	{
		$result = array();

		// Do some stuff

		if ($path = $this->includeEvent(__FUNCTION__))
		{
			@include $this->includeEvent(__FUNCTION__);
		}

		return $this->resultBool($result);
	}

	/**
	 * Remove all sessions for the user name
	 *
	 * @param   array   $user    Holds the user data
	 * @param   boolean $success True if user was succesfully stored in the database
	 * @param   string  $msg     Message
	 *
	 * @return  boolean
	 */
	public function onUserAfterDelete($user, $success, $msg)
	{
		$result = array();

		// Do some stuff

		if ($path = $this->includeEvent(__FUNCTION__))
		{
			@include $this->includeEvent(__FUNCTION__);
		}

		return $this->resultBool($result);
	}

	/**
	 * Prepare content data.
	 *
	 * @param   string $context The context for the data
	 * @param   int    $data    The user id
	 *
	 * @return  boolean
	 */
	public function onContentPrepareData($context, $data)
	{
		$result = array();

		// Do some stuff

		if ($path = $this->includeEvent(__FUNCTION__))
		{
			@include $this->includeEvent(__FUNCTION__);
		}

		return $this->resultBool($result);
	}

	// Utility Functions
	// ====================================================================================

	/**
	 * Function call.
	 *
	 * A proxy to call class and functions
	 * Example: $this->call('folder1.folder2.function', $args) ; OR $this->call('folder1.folder2.Class::function', $args)
	 *
	 * @param   string $uri The class or function file path.
	 *
	 * @return  mixed
	 */
	public function call($uri)
	{
		// Split paths
		$path = explode('.', $uri);
		$func = array_pop($path);
		$func = explode('::', $func);

		// Set class name of function name.
		if (isset($func[1]))
		{
			$class_name = $func[0];
			$func_name  = $func[1];
			$file_name  = $class_name;
		}
		else
		{
			$func_name = $func[0];
			$file_name = $func_name;
		}

		$func_path    = implode('/', $path) . '/' . $file_name;
		$include_path = JPATH_ROOT . '/' . $this->params->get('include_path', 'easyset');

		// Include file.
		if (!function_exists($func_name) && !class_exists($class_name))
		{
			$file = trim($include_path, '/') . '/' . $func_path . '.php';

			if (!file_exists($file))
			{
				$file = dirname(__FILE__) . '/lib/' . $func_path . '.php';
			}

			if (file_exists($file))
			{
				include_once $file;
			}
		}

		// Handle args
		$args = func_get_args();
		array_shift($args);

		// Call Function
		if (isset($class_name) && method_exists($class_name, $func_name))
		{
			return call_user_func_array(array($class_name, $func_name), $args);
		}
		elseif (function_exists($func_name))
		{
			return call_user_func_array($func_name, $args);
		}

	}

	/**
	 * includeEvent
	 *
	 * @param   string  $func  Function event name.
	 *
	 * @return  string
	 */
	public function includeEvent($func)
	{
		$include_path = JPATH_ROOT . '/' . $this->params->get('include_path', 'easyset');
		$event        = trim($include_path, '/') . '/' . 'events/' . $func . '.php';

		if (file_exists($event))
		{
			return $event;
		}
	}

	/**
	 * resultBool
	 *
	 * @param array $result
	 *
	 * @return  bool
	 */
	public function resultBool($result = array())
	{
		foreach ($result as $result)
		{
			if (!$result)
			{
				return false;
			}
		}

		return true;
	}
}
