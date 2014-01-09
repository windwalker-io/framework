<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\View\Html;

use Windwalker\DI\Container;
use Windwalker\Model\Model;
use Windwalker\View\AbstractView;

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.path');

/**
 * Class AbstractHtmlView
 *
 * @since 1.0
 */
abstract class AbstractHtmlView extends AbstractView
{
	/**
	 * The view layout.
	 *
	 * @var    string
	 * @since  12.1
	 */
	protected $layout = 'default';

	/**
	 * Layout extension
	 *
	 * @var    string
	 */
	protected $layoutExt = 'php';

	/**
	 * @var  string  Property layoutTemplate.
	 */
	protected $layoutTemplate;

	/**
	 * The paths queue.
	 *
	 * @var    \SplPriorityQueue
	 * @since  12.1
	 */
	protected $paths;

	/**
	 * The name of the default template source file.
	 *
	 * @var string
	 */
	protected $template = null;

	/**
	 * @var  array  Property templatePrepared.
	 */
	protected $templatePrepared = array();

	/**
	 * @var  string  Property viewList.
	 */
	protected $viewList = null;

	/**
	 * @var  string  Property viewItem.
	 */
	protected $viewItem = null;

	/**
	 * Method to instantiate the view.
	 *
	 * @param   Model            $model  The model object.
	 * @param   \SplPriorityQueue  $paths  The paths queue.
	 *
	 * @since   12.1
	 */
	public function __construct(Model $model = null, Container $container = null, $config = array(), \SplPriorityQueue $paths = null)
	{
		parent::__construct($model);

		// Setup dependencies.
		$this->paths = $paths ? : $this->loadPaths();
	}

	/**
	 * Magic toString method that is a proxy for the render method.
	 *
	 * @return  string
	 *
	 * @since   12.1
	 */
	public function __toString()
	{
		return $this->render();
	}

	/**
	 * Method to escape output.
	 *
	 * @param   string  $output  The output to escape.
	 *
	 * @return  string  The escaped output.
	 *
	 * @see     JView::escape()
	 * @since   12.1
	 */
	public function escape($output)
	{
		// Escape the output.
		return htmlspecialchars($output, ENT_COMPAT, 'UTF-8');
	}

	/**
	 * Load a template file -- first look in the templates folder for an override
	 *
	 * @param   string  $tpl  The name of the template source file; automatically searches the template paths and compiles as needed.
	 *
	 * @return  string  The output of the the template script.
	 *
	 * @since   3.2
	 * @throws  \Exception
	 */
	public function loadTemplate($tpl = null)
	{
		$container      = $this->container;
		$layout         = $this->getLayout();
		$layoutTemplate = $this->getLayoutTemplate();
		$template       = $container->get('app')->getTemplate();

		// Create the template file name based on the layout
		$file = $this->layout = isset($tpl) ? $layout . '_' . $tpl : $layout;

		// Clean the file name
		$file = preg_replace('/[^A-Z0-9_\.-]/i', '', $file);
		$tpl  = isset($tpl) ? preg_replace('/[^A-Z0-9_\.-]/i', '', $tpl) : $tpl;

		// Load the template script
		$templateFile = $this->getPath($file);

		// Change the template folder if alternative layout is in different template
		if (isset($layoutTemplate) && $layoutTemplate != '_' && $layoutTemplate != $template)
		{
			$alternateTmplFile = str_replace($template, $layoutTemplate, $template);

			if (is_file($alternateTmplFile))
			{
				$templateFile = $alternateTmplFile;
				$template     = $layoutTemplate;
			}
		}

		if (strpos($templateFile, \JPath::clean(JPATH_THEMES)) !== false)
		{
			$this->prepareTemplate($template);
		}

		if (!$templateFile)
		{
			throw new \Exception(\JText::sprintf('JLIB_APPLICATION_ERROR_LAYOUTFILE_NOT_FOUND', $file), 500);
		}

		// Unset so as not to introduce into template scope
		unset($tpl);
		unset($file);

		// Never allow a 'this' property
		if (isset($this->this))
		{
			unset($this->this);
		}

		// Start capturing output into a buffer
		ob_start();

		// Include the requested template filename in the local scope
		// (this will execute the view logic).
		include $templateFile;

		// Done with the requested template; get the buffer and
		// clear it.
		$output = ob_get_contents();
		ob_end_clean();

		// Fall back to last layout
		$this->layout = $layout;

		return $output;
	}

	/**
	 * prepareTemplate
	 *
	 * @param $template
	 *
	 * @return  void
	 */
	protected function prepareTemplate($template)
	{
		if ($this->templatePrepared)
		{
			return;
		}

		// Load the language file for the template
		$lang = $this->container->get('language');
		$lang->load('tpl_' . $template, JPATH_BASE, null, false, false)
		|| $lang->load('tpl_' . $template, JPATH_THEMES . "/$template", null, false, false)
		|| $lang->load('tpl_' . $template, JPATH_BASE, $lang->getDefault(), false, false)
		|| $lang->load('tpl_' . $template, JPATH_THEMES . "/$template", $lang->getDefault(), false, false);

		$this->templatePrepared = true;
	}

	/**
	 * flash
	 *
	 * @param string $msgs
	 * @param string $type
	 *
	 * @return $this
	 */
	public function flash($msgs, $type = 'message')
	{
		$app  = $this->getContainer()->get('app');
		$msgs = (array) $msgs;

		foreach ($msgs as $msg)
		{
			$app->enqueueMessage($msg, $type);
		}

		return $this;
	}

	/**
	 * getLayoutTemplate
	 *
	 * @return  string
	 */
	public function getLayoutTemplate()
	{
		return $this->layoutTemplate;
	}

	/**
	 * Method to get the view layout.
	 *
	 * @return  string  The layout name.
	 *
	 * @since   12.1
	 */
	public function getLayout()
	{
		return $this->layout;
	}

	/**
	 * Method to get the layout path.
	 *
	 * @param   string  $layout  The layout name.
	 *
	 * @return  mixed  The layout file name if found, false otherwise.
	 *
	 * @since   12.1
	 */
	public function getPath($layout)
	{
		// Get the layout file name.
		$file = \JPath::clean($layout . '.' . $this->layoutExt);

		// Find the layout file path.
		$path = \JPath::find(clone($this->paths), $file);

		return $path;
	}

	/**
	 * Method to get the view paths.
	 *
	 * @return  \SplPriorityQueue  The paths queue.
	 *
	 * @since   12.1
	 */
	public function getPaths()
	{
		return $this->paths;
	}

	/**
	 * doRedner
	 *
	 * @return  string
	 *
	 * @throws \RuntimeException
	 */
	protected function doRedner()
	{
		return $this->loadTemplate();
	}

	/**
	 * Method to set the view layout.
	 *
	 * @param   string  $layout  The layout name.
	 *
	 * @return  HtmlView  Method supports chaining.
	 *
	 * @since   12.1
	 */
	public function setLayout($layout)
	{
		if (strpos($layout, ':') === false)
		{
			$this->layout = $layout;
		}
		else
		{
			// Convert parameter to array based on :
			$temp = explode(':', $layout);
			$this->layout = $temp[1];

			// Set layout template
			$this->layoutTemplate = $temp[0];
		}

		return $this;
	}

	/**
	 * Method to set the view paths.
	 *
	 * @param   \SplPriorityQueue  $paths  The paths queue.
	 *
	 * @return  HtmlView  Method supports chaining.
	 *
	 * @since   12.1
	 */
	public function setPaths(\SplPriorityQueue $paths)
	{
		$this->paths = $paths;

		return $this;
	}

	/**
	 * Method to load the paths queue.
	 *
	 * @return  \SplPriorityQueue  The paths queue.
	 *
	 * @since   12.1
	 */
	protected function loadPaths()
	{
		return new \SplPriorityQueue;
	}
}
