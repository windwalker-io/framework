<?php
/**
 * Part of joomla321 project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Helper;

use Windwalker\DI\Container;
use Joomla\DI\Container as JoomlaContainer;
use Joomla\DI\ContainerAwareInterface;

/**
 * Class AssetHelper
 *
 * @since 1.0
 */
class AssetHelper implements ContainerAwareInterface
{
	/**
	 * Property paths.
	 *
	 * @var string
	 */
	protected $paths;

	/**
	 * Property name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Property container.
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * Property doc.
	 *
	 * @var \JDocument
	 */
	protected $doc;

	/**
	 * Property sumName.
	 *
	 * @var
	 */
	protected $sumName = 'md5sum';

	/**
	 * Property mootools.
	 *
	 * @var bool
	 */
	protected $mootools = false;

	/**
	 * Property jquery.
	 *
	 * @var bool
	 */
	protected $jquery = false;

	/**
	 * @param $paths
	 */
	public function __construct($name = 'windwalker', $paths = null)
	{
		$this->name = $name;

		// Setup dependencies.
		$this->paths = $paths ? : new \SplPriorityQueue((array) $paths);

		$this->registerPaths();
	}

	/**
	 * addCss
	 *
	 * @param string $file
	 * @param string $name
	 * @param array  $attribs
	 *
	 * @return $this
	 */
	public function addCSS($file, $name = null, $attribs = array())
	{
		$doc = $this->getDoc();

		if ($doc->getType() != 'html')
		{
			return $this;
		}

		$filePath = $this->findFile($file, 'css', $name);

		if (!$filePath)
		{
			$this->alert(sprintf('CSS file: %s not found.', $file));

			return $this;
		}

		$type  = \JArrayHelper::getValue($attribs, 'type');
		$media = \JArrayHelper::getValue($attribs, 'media');

		unset($attribs['type']);
		unset($attribs['media']);

		$doc->addStyleSheetVersion(\JUri::root(true) . '/' . $filePath['file'], $filePath['sum'], $type, $media, $attribs);

		return $this;
	}

	/**
	 * addCss
	 *
	 * @param string $file
	 * @param string $name
	 * @param string $version
	 * @param array  $attribs
	 *
	 * @return $this
	 */
	public function addJS($file, $name = null, $version = null, $attribs = array())
	{
		$doc = $this->getDoc();

		if ($doc->getType() != 'html')
		{
			return $this;
		}

		$filePath = $this->findFile($file, 'js', $name);

		if (!$filePath)
		{
			$this->alert(sprintf('JS file: %s not found.', $file));

			return $this;
		}

		$type  = \JArrayHelper::getValue($attribs, 'type');
		$defer = \JArrayHelper::getValue($attribs, 'defer');
		$async = \JArrayHelper::getValue($attribs, 'async');

		unset($attribs['type']);
		unset($attribs['media']);

		if ($this->mootools)
		{
			\JHtml::_('behavior.framework');
		}

		if ($this->jquery)
		{
			\JHtml::_('jquery.framework', $this->mootools);
		}

		$doc->addScriptVersion(\JUri::root(true) . '/' . $filePath['file'], $filePath['sum'], $type, $defer, $async);

		return $this;
	}

	/**
	 * addCssDeclaration
	 *
	 * @param string $content
	 * @param string $type
	 *
	 * @return $this
	 */
	public function internalCSS($content, $type = 'text/css')
	{
		$this->getDoc()->addStyleDeclaration("\n" . $content . "\n", $type);

		return $this;
	}

	/**
	 * addScriptDeclaration
	 *
	 * @param string $content
	 * @param string $type
	 *
	 * @return $this
	 */
	public function internalJS($content, $type = 'text/javascript')
	{
		$this->getDoc()->addScriptDeclaration("\n" . $content . "\n", $type);

		return $this;
	}

	/**
	 * windwalker
	 *
	 * @return void
	 */
	public function windwalker()
	{
		$this->addCSS('windwalker.css');
		$this->addJS('windwalker.js');
	}

	/**
	 * jquery
	 *
	 * @param boolean $debug
	 * @param boolean $migrate
	 *
	 * @return $this
	 */
	public function jquery($debug = null, $migrate = true)
	{
		\JHtml::_('jquery.framework', true, $debug, $migrate);

		return $this;
	}

	/**
	 * jqueryUI
	 *
	 * @param boolean $debug
	 *
	 * @return $this
	 */
	public function jqueryUI($debug = null)
	{
		\JHtml::_('jquery.ui', array('core'), $debug);

		return $this;
	}

	/**
	 * mootools
	 *
	 * @param boolean $debug
	 *
	 * @return $this
	 */
	public function mootools($debug = null)
	{
		\JHtml::_('behavior.framework', true, $debug);

		return $this;
	}

	/**
	 * bootstrap
	 *
	 * @param bool    $css
	 * @param boolean $debug
	 *
	 * @return $this
	 */
	public function bootstrap($css = false, $debug = null)
	{
		\JHtml::_('bootstrap.framework', $debug);

		if ($css)
		{
			\JHtml::_('bootstrap.loadCss');
		}

		return $this;
	}

	/**
	 * isis
	 *
	 * @param bool $debug
	 *
	 * @return $this
	 */
	public function isis($debug = false)
	{
		static $loaded;

		if ($loaded)
		{
			return $this;
		}

		$doc    = $this->getDoc();
		$app    = $this->getContainer()->get('app');
		$prefix = $app->isSite() ? 'administrator/' : '';

		$this->jquery();

		$min = $debug ? '.min' : '';

		$doc->addStylesheet($prefix . 'templates/isis/css/template' . $min . '.css');
		$doc->addScript($prefix . 'templates/isis/js/template' . $min . '.js');

		$loaded = true;

		return $this;
	}

	/**
	 * findFile
	 *
	 * @param string $file
	 * @param string $type
	 * @param null   $name
	 *
	 * @return array|bool
	 */
	protected function findFile($file, $type, $name = null)
	{
		$name      = $name ? : $this->name;
		$foundpath = '';
		$sum       = '';

		foreach ($this->paths as $path)
		{
			$path = str_replace(array('{name}', '{type}'), array($name, $type), $path);

			$path = trim($path, '/');

			// Get compressed file
			if (!JDEBUG && is_file(JPATH_ROOT . '/' . $path . '/' . ($minname = $this->getMinName($file))))
			{
				$foundpath = $path;
				$file      = trim($minname, '/');

				break;
			}

			$filepath = $path . '/' . $file;

			if (is_file(JPATH_ROOT . '/' . $filepath))
			{
				$foundpath = $path;

				break;
			}
		}

		if (!$foundpath)
		{
			return false;
		}

		// Get SUM
		if (!JDEBUG)
		{
			$sumfile = JPATH_ROOT . '/' . $foundpath . '/' . $file . '.sum';

			if (!is_file($sumfile))
			{
				$sumfile = JPATH_ROOT . '/' . $foundpath . '/' . $this->sumName;
			}

			if (is_file($sumfile))
			{
				$sum = file_get_contents($sumfile);
			}

			if ($sum)
			{
				$sum = str_replace(array("\n", "\r"), '', $sum);

				$sum = addslashes(htmlentities($sum));
			}
		}
		else
		{
			$sum = md5(uniqid());
		}

		// Build path
		$file = $foundpath . '/' . $file;

		return array(
			'file' => $file,
			'sum'  => $sum
		);
	}

	/**
	 * getMinName
	 *
	 * @param $file
	 *
	 * @return string
	 */
	public function getMinName($file)
	{
		$file = new \SplFileInfo($file);
		$ext  = $file->getExtension();
		$name = $file->getBasename('.' . $ext);

		$name = $name . '.min.' . $ext;

		return $file->getPath() . '/' . $name;
	}

	/**
	 * registerPaths
	 *
	 * @return void
	 */
	protected function registerPaths()
	{
		$app = $this->getContainer()->get('app');

		$prefix = $app->isAdmin() ? 'administrator/' : '';

		// (1) Find: templates/[tmpl]/[type]/[name]/[file_name].[type]
		$this->paths->insert($prefix . 'templates/' . $app->getTemplate() . '/{type}/{name}', 800);

		// (2) Find: templates/[tmpl]/[file_name].[type]
		$this->paths->insert($prefix . 'templates/' . $app->getTemplate(), 700);

		// (3) Find: components/[name]/asset/[type]/[file_name].[type]
		$this->paths->insert($prefix . 'components/{name}/asset/{type}', 600);

		// (4) Find: components/[name]/asset/[file_name].[type]
		$this->paths->insert($prefix . 'components/{name}', 500);

		// (5) Find: media/[name]/[type]/[file_name].[type]
		$this->paths->insert('media/{name}/{type}', 400);

		// (6) Find: media/[name]/[file_name].[type]
		$this->paths->insert('media/{name}', 300);

		// (7) Find: media/windwalker/[type]/[file_name].[type]
		$this->paths->insert('media/windwalker/{type}', 200);

		// (8) Find: media/windwalker/[file_name].[type]
		$this->paths->insert('media/windwalker', 100);

		// (9) Find: libraries/windwalker/assets/[file_name].[type] (For legacy)
		$this->paths->insert('libraries/windwalker/assets', 50);
	}

	/**
	 * Get the DI container.
	 *
	 * @param   string $name
	 *
	 * @return  Container
	 *
	 * @since   1.0
	 */
	public function getContainer($name = null)
	{
		if (!($this->container instanceof JoomlaContainer))
		{
			$name = ($name == 'windwalker') ? null : $name;

			$this->container = Container::getInstance($name);
		}

		return $this->container;
	}

	/**
	 * Set the DI container.
	 *
	 * @param  JoomlaContainer $container The DI container.
	 *
	 * @return $this
	 *
	 * @since   1.0
	 */
	public function setContainer(JoomlaContainer $container)
	{
		$this->container = $container;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPaths()
	{
		return $this->paths;
	}

	/**
	 * @param string $paths
	 */
	public function setPaths($paths)
	{
		$this->paths = $paths;

		return $this;
	}

	/**
	 * @return \JDocument
	 */
	public function getDoc()
	{
		if (!($this->doc instanceof \JDocument))
		{
			$this->doc = $this->getContainer()->get('document');
		}

		return $this->doc;
	}

	/**
	 * @param \JDocument $doc
	 */
	public function setDoc(\JDocument $doc)
	{
		$this->doc = $doc;

		return $this;
	}

	/**
	 * alert
	 *
	 * @param string $msg
	 * @param string $type
	 *
	 * @return $this
	 */
	protected function alert($msg, $type = 'warning')
	{
		if (JDEBUG)
		{
			$this->getContainer()->get('app')->enqueueMessage($msg, $type);
		}

		return $this;
	}

	/**
	 * @param mixed $sumName
	 */
	public function setSumName($sumName)
	{
		$this->sumName = $sumName;

		return $this;
	}
}
