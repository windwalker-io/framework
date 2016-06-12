<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Edge;

use Windwalker\Cache\Cache;
use Windwalker\Cache\CacheInterface;
use Windwalker\Cache\DataHandler\RawDataHandler;
use Windwalker\Cache\Storage\CacheStorageInterface;
use Windwalker\Cache\Storage\FileStorage;
use Windwalker\Cache\Storage\RuntimeStorage;
use Windwalker\Edge\Compiler\EdgeCompiler;
use Windwalker\Edge\Loader\EdgeFileLoader;

/**
 * The EdgeEnvironment class.
 *
 * @since  {DEPLOY_VERSION}
 */
class EdgeEnvironment
{
	/**
	 * Property compiler.
	 *
	 * @var  EdgeCompiler
	 */
	protected $compiler;

	/**
	 * Property cache.
	 *
	 * @var  CacheInterface
	 */
	protected $cache;

	/**
	 * Property storage.
	 *
	 * @var  CacheStorageInterface
	 */
	protected $storage;

	/**
	 * Property globals.
	 *
	 * @var  array
	 */
	protected $globals = array();

	/**
	 * Property sections.
	 *
	 * @var  array
	 */
	protected $sections;

	/**
	 * The stack of in-progress sections.
	 *
	 * @var array
	 */
	protected $sectionStack = array();

	/**
	 * The stack of in-progress push sections.
	 *
	 * @var array
	 */
	protected $pushStack = array();

	/**
	 * The number of active rendering operations.
	 *
	 * @var int
	 */
	protected $renderCount = 0;

	/**
	 * Property pushes.
	 *
	 * @var array
	 */
	protected $pushes = array();

	/**
	 * Property loader.
	 *
	 * @var  EdgeFileLoader
	 */
	protected $loader;

	/**
	 * EdgeEnvironment constructor.
	 *
	 * @param                       $loader
	 * @param EdgeCompiler          $compiler
	 * @param CacheStorageInterface $storage
	 */
	public function __construct($loader, EdgeCompiler $compiler, CacheStorageInterface $storage = null)
	{
		$this->compiler = $compiler;

		$this->globals['__env'] = $this;
		$this->loader = $loader;

		$this->setCacheStorage($storage);
	}

	/**
	 * addGlobal
	 *
	 * @param   string $name
	 * @param   string $value
	 *
	 * @return  static
	 */
	public function addGlobal($name, $value)
	{
		$this->globals[$name] = $value;

		return $this;
	}

	public function render($__path, $__data = array())
	{
		// TODO: Aliases

		$__file = $this->loader->load($__path);

		$__hash = md5($__file);

		if (!$this->cache->exists($__hash))
		{
			$this->cache->set($__hash, $this->compiler->compile(file_get_contents($__file)));
		}

		extract($__data);

		$__env = $this;

		ob_start();

		if ($this->getCacheStorage() instanceof FileStorage)
		{
			include $this->getCacheStorage()->fetchStreamUri($__hash);
		}
		else
		{
			eval(' ?>' . $this->cache->get($__hash) . '<?php ');
		}

		return ob_get_clean();
	}

	/**
	 * Method to set property storage
	 *
	 * @param   CacheStorageInterface $storage
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setCacheStorage($storage)
	{
		$this->storage = $storage ? : new RuntimeStorage;
		$this->cache = new Cache($this->storage, new RawDataHandler);

		return $this;
	}

	/**
	 * Method to get property Storage
	 *
	 * @return  CacheStorageInterface|FileStorage
	 */
	public function getCacheStorage()
	{
		return $this->storage;
	}

	/**
	 * Normalize a view name.
	 *
	 * @param  string $name
	 * @return string
	 */
	protected function normalizeName($name)
	{
		// TODO: Handle namespace

		return str_replace('/', '.', $name);
	}

	/**
	 * Start injecting content into a section.
	 *
	 * @param  string  $section
	 * @param  string  $content
	 * @return void
	 */
	public function startSection($section, $content = '')
	{
		if ($content === '') {
			if (ob_start()) {
				$this->sectionStack[] = $section;
			}
		} else {
			$this->extendSection($section, $content);
		}
	}

	/**
	 * Inject inline content into a section.
	 *
	 * @param  string  $section
	 * @param  string  $content
	 * @return void
	 */
	public function inject($section, $content)
	{
		$this->startSection($section, $content);
	}

	/**
	 * Stop injecting content into a section and return its contents.
	 *
	 * @return string
	 */
	public function yieldSection()
	{
		if (empty($this->sectionStack)) {
			return '';
		}

		return $this->yieldContent($this->stopSection());
	}

	/**
	 * Stop injecting content into a section.
	 *
	 * @param  bool  $overwrite
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function stopSection($overwrite = false)
	{
		if (empty($this->sectionStack)) {
			throw new \InvalidArgumentException('Cannot end a section without first starting one.');
		}

		$last = array_pop($this->sectionStack);

		if ($overwrite) {
			$this->sections[$last] = ob_get_clean();
		} else {
			$this->extendSection($last, ob_get_clean());
		}

		return $last;
	}

	/**
	 * Stop injecting content into a section and append it.
	 *
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function appendSection()
	{
		if (empty($this->sectionStack)) {
			throw new \InvalidArgumentException('Cannot end a section without first starting one.');
		}

		$last = array_pop($this->sectionStack);

		if (isset($this->sections[$last])) {
			$this->sections[$last] .= ob_get_clean();
		} else {
			$this->sections[$last] = ob_get_clean();
		}

		return $last;
	}

	/**
	 * Append content to a given section.
	 *
	 * @param  string  $section
	 * @param  string  $content
	 * @return void
	 */
	protected function extendSection($section, $content)
	{
		if (isset($this->sections[$section])) {
			$content = str_replace('@parent', $content, $this->sections[$section]);
		}

		$this->sections[$section] = $content;
	}

	/**
	 * Get the string contents of a section.
	 *
	 * @param  string  $section
	 * @param  string  $default
	 * @return string
	 */
	public function yieldContent($section, $default = '')
	{
		$sectionContent = $default;

		if (isset($this->sections[$section])) {
			$sectionContent = $this->sections[$section];
		}

		$sectionContent = str_replace('@@parent', '--parent--holder--', $sectionContent);

		return str_replace(
			'--parent--holder--', '@parent', str_replace('@parent', '', $sectionContent)
		);
	}

	/**
	 * Start injecting content into a push section.
	 *
	 * @param  string  $section
	 * @param  string  $content
	 * @return void
	 */
	public function startPush($section, $content = '')
	{
		if ($content === '') {
			if (ob_start()) {
				$this->pushStack[] = $section;
			}
		} else {
			$this->extendPush($section, $content);
		}
	}

	/**
	 * Stop injecting content into a push section.
	 *
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function stopPush()
	{
		if (empty($this->pushStack)) {
			throw new \InvalidArgumentException('Cannot end a section without first starting one.');
		}

		$last = array_pop($this->pushStack);

		$this->extendPush($last, ob_get_clean());

		return $last;
	}

	/**
	 * Append content to a given push section.
	 *
	 * @param  string  $section
	 * @param  string  $content
	 * @return void
	 */
	protected function extendPush($section, $content)
	{
		if (! isset($this->pushes[$section])) {
			$this->pushes[$section] = [];
		}
		if (! isset($this->pushes[$section][$this->renderCount])) {
			$this->pushes[$section][$this->renderCount] = $content;
		} else {
			$this->pushes[$section][$this->renderCount] .= $content;
		}
	}

	/**
	 * Get the string contents of a push section.
	 *
	 * @param  string  $section
	 * @param  string  $default
	 * @return string
	 */
	public function yieldPushContent($section, $default = '')
	{
		if (! isset($this->pushes[$section])) {
			return $default;
		}

		return implode(array_reverse($this->pushes[$section]));
	}

	/**
	 * Flush all of the section contents.
	 *
	 * @return void
	 */
	public function flushSections()
	{
		$this->renderCount = 0;

		$this->sections = [];
		$this->sectionStack = [];

		$this->pushes = [];
		$this->pushStack = [];
	}

	/**
	 * Flush all of the section contents if done rendering.
	 *
	 * @return void
	 */
	public function flushSectionsIfDoneRendering()
	{
		if ($this->doneRendering()) {
			$this->flushSections();
		}
	}

	/**
	 * Increment the rendering counter.
	 *
	 * @return void
	 */
	public function incrementRender()
	{
		$this->renderCount++;
	}

	/**
	 * Decrement the rendering counter.
	 *
	 * @return void
	 */
	public function decrementRender()
	{
		$this->renderCount--;
	}

	/**
	 * Check if there are no active render operations.
	 *
	 * @return bool
	 */
	public function doneRendering()
	{
		return $this->renderCount == 0;
	}

	/**
	 * arrayExcept
	 *
	 * @param array $array
	 * @param array $fields
	 *
	 * @return  array
	 */
	public function arrayExcept(array $array, array $fields)
	{
		foreach ($fields as $field)
		{
			if (array_key_exists($field, $array))
			{
				unset($array[$field]);
			}
		}

		return $array;
	}
}
