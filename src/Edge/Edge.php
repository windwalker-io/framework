<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Edge;

use Windwalker\Edge\Cache\EdgeArrayCache;
use Windwalker\Edge\Cache\EdgeCacheInterface;
use Windwalker\Edge\Cache\EdgeFileCache;
use Windwalker\Edge\Compiler\EdgeCompiler;
use Windwalker\Edge\Compiler\EdgeCompilerInterface;
use Windwalker\Edge\Concern\ManageComponentTrait;
use Windwalker\Edge\Exception\EdgeException;
use Windwalker\Edge\Extension\EdgeExtensionInterface;
use Windwalker\Edge\Loader\EdgeLoaderInterface;
use Windwalker\Edge\Loader\EdgeStringLoader;

/**
 * The Edge template engine.
 *
 * This is a modified version of Laravel Blade engine.
 *
 * @see    https://github.com/illuminate/view/blob/master/Factory.php
 *
 * @since  3.0
 */
class Edge
{
    use ManageComponentTrait;

    /**
     * Property globals.
     *
     * @var  array
     */
    protected $globals = [];

    /**
     * Property extensions.
     *
     * @var  EdgeExtensionInterface[]
     */
    protected $extensions = [];

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
    protected $sectionStack = [];

    /**
     * @var array
     */
    protected $hasParents = [];

    /**
     * The stack of in-progress push sections.
     *
     * @var array
     */
    protected $pushStack = [];

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
    protected $pushes = [];

    /**
     * Property loader.
     *
     * @var  EdgeLoaderInterface
     */
    protected $loader;

    /**
     * Property compiler.
     *
     * @var  EdgeCompilerInterface
     */
    protected $compiler;

    /**
     * Property cacheHandler.
     *
     * @var  EdgeCacheInterface
     */
    protected $cache;

    /**
     * EdgeEnvironment constructor.
     *
     * @param EdgeLoaderInterface   $loader
     * @param EdgeCompilerInterface $compiler
     * @param EdgeCacheInterface    $cache
     */
    public function __construct(
        EdgeLoaderInterface $loader = null,
        EdgeCompilerInterface $compiler = null,
        EdgeCacheInterface $cache = null
    ) {
        // Simple fix for Blade escape
        include_once __DIR__ . '/compat.php';

        $this->loader = $loader ?: new EdgeStringLoader();
        $this->compiler = $compiler ?: new EdgeCompiler();
        $this->cache = $cache ?: new EdgeArrayCache();
    }

    /**
     * render
     *
     * @param string $__layout
     * @param array  $__data
     * @param array  $__more
     *
     * @return string
     * @throws EdgeException
     */
    public function render($__layout, $__data = [], $__more = [])
    {
        // TODO: Aliases

        $this->incrementRender();

        $__path = $this->loader->find($__layout);

        if ($this->cache->isExpired($__path)) {
            $compiler = $this->prepareExtensions(clone $this->compiler);

            $compiled = $compiler->compile($this->loader->load($__path));

            if ($this->cache instanceof EdgeFileCache) {
                $compiled = "<?php /* File: {$__path} */ ?>" . $compiled;
            }

            $this->cache->store($__path, $compiled);

            unset($compiler, $compiled);
        }

        $__data = array_merge($this->getGlobals(true), $__more, $__data);

        foreach ($__data as $__key => $__value) {
            if ($__key === '__path' || $__key === '__data') {
                continue;
            }

            $$__key = $__value;
        }

        unset($__data, $__value, $__key);

        ob_start();

        try {
            if ($this->cache instanceof EdgeFileCache) {
                include $this->cache->getCacheFile($this->cache->getCacheKey($__path));
            } else {
                eval(' ?>' . $this->cache->load($__path) . '<?php ');
            }
        } catch (\Throwable $e) {
            ob_clean();
            $this->wrapException($e, $__path, $__layout);

            return null;
        }

        $result = ltrim(ob_get_clean());

        $this->decrementRender();

        $this->flushSectionsIfDoneRendering();

        return $result;
    }

    /**
     * wrapException
     *
     * @param \Exception|\Throwable $e
     * @param string                $path
     * @param                       $layout
     *
     * @throws EdgeException
     */
    protected function wrapException($e, $path, $layout)
    {
        $msg = $e->getMessage();

        $msg .= sprintf("\n\n| View layout: %s (%s)", $path, $layout);

        throw new EdgeException($msg, $e->getCode(), $path, $e->getLine(), $e);
    }

    /**
     * Normalize a view name.
     *
     * @param  string $name
     *
     * @return string
     */
    protected function normalizeName($name)
    {
        // TODO: Handle namespace

        return str_replace('/', '.', $name);
    }

    /**
     * escape
     *
     * @param  string $string
     *
     * @return  string
     */
    public function escape($string)
    {
        return htmlspecialchars((string) $string, ENT_COMPAT, 'UTF-8');
    }

    /**
     * Start injecting content into a section.
     *
     * @param  string $section
     * @param  string $content
     *
     * @return void
     */
    public function startSection($section, $content = '')
    {
        if ($content === '') {
            if (ob_start()) {
                $this->sectionStack[] = $section;
            }
        } else {
            $this->hasParents[$section] = strpos($content, '@parent') !== false;

            $this->extendSection($section, $content);
        }
    }

    /**
     * Inject inline content into a section.
     *
     * @param  string $section
     * @param  string $content
     *
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
     * @param  bool $overwrite
     *
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
            $content = ob_get_clean();

            $this->hasParents[$last] = strpos($content, '@parent') !== false;

            $this->extendSection($last, $content);
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
     * @param  string $section
     * @param  string $content
     *
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
     * hasParent
     *
     * @param  string  $section
     *
     * @return  bool
     *
     * @since  3.5.21
     */
    public function hasParent(string $section): bool
    {
        return !empty($this->hasParents[$section]) || !isset($this->sections[$section]);
    }

    /**
     * Get the string contents of a section.
     *
     * @param  string $section
     * @param  string $default
     *
     * @return string
     */
    public function yieldContent($section, $default = '')
    {
        $sectionContent = $default;

        if (isset($this->sections[$section])) {
            $sectionContent = $this->sections[$section];
            $sectionContent = str_replace('@@parent', '--parent--holder--', $sectionContent);

            return str_replace(
                '--parent--holder--',
                '@parent',
                str_replace('@parent', $default, $sectionContent)
            );
        }

        return $sectionContent;
    }

    /**
     * Start injecting content into a push section.
     *
     * @param  string $section
     * @param  string $content
     *
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
     * @param  string $section
     * @param  string $content
     *
     * @return void
     */
    protected function extendPush($section, $content)
    {
        if (!isset($this->pushes[$section])) {
            $this->pushes[$section] = [];
        }

        if (!isset($this->pushes[$section][$this->renderCount])) {
            $this->pushes[$section][$this->renderCount] = $content;
        } else {
            $this->pushes[$section][$this->renderCount] .= $content;
        }
    }

    /**
     * Get the string contents of a push section.
     *
     * @param  string $section
     * @param  string $default
     *
     * @return string
     */
    public function yieldPushContent($section, $default = '')
    {
        if (!isset($this->pushes[$section])) {
            return $default;
        }

        return implode(array_reverse($this->pushes[$section]));
    }

    /**
     * Get the rendered contents of a partial from a loop.
     *
     * @param  string $view
     * @param  array  $data
     * @param  string $iterator
     * @param  string $empty
     *
     * @return string
     * @throws EdgeException
     */
    public function renderEach($view, $data, $iterator, $empty = 'raw|')
    {
        $result = '';

        // If is actually data in the array, we will loop through the data and append
        // an instance of the partial view to the final result HTML passing in the
        // iterated value of this data array, allowing the views to access them.
        if (count($data) > 0) {
            foreach ($data as $key => $value) {
                $data = ['key' => $key, $iterator => $value];

                $result .= $this->render($view, $data);
            }
        } else {
            // If there is no data in the array, we will render the contents of the empty
            // view. Alternatively, the "empty view" could be a raw string that begins
            // with "raw|" for convenience and to let this know that it is a string.
            if (strpos($empty, 'raw|') === 0) {
                $result = substr($empty, 4);
            } else {
                $result = $this->render($empty);
            }
        }

        return $result;
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
        $this->hasParents = [];

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
     * prepareDirectives
     *
     * @param EdgeCompilerInterface $compiler
     *
     * @return EdgeCompilerInterface
     */
    public function prepareExtensions(EdgeCompilerInterface $compiler)
    {
        foreach ($this->getExtensions() as $extension) {
            foreach ((array) $extension->getDirectives() as $name => $directive) {
                $compiler->directive($name, $directive);
            }

            foreach ((array) $extension->getParsers() as $parser) {
                $compiler->parser($parser);
            }
        }

        return $compiler;
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
        foreach ($fields as $field) {
            if (array_key_exists($field, $array)) {
                unset($array[$field]);
            }
        }

        return $array;
    }

    /**
     * Method to get property Globals
     *
     * @param bool $withExtensions
     *
     * @return array
     */
    public function getGlobals($withExtensions = false)
    {
        $globals = $this->globals;

        if ($withExtensions) {
            $temp = [];

            foreach ((array) $this->getExtensions() as $extension) {
                $temp = array_merge($temp, (array) $extension->getGlobals());
            }

            $globals = array_merge($temp, $globals);
        }

        return $globals;
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

    public function removeGlobal($name)
    {
        unset($this->globals[$name]);

        return $this;
    }

    public function getGlobal($name, $default = null)
    {
        if (array_key_exists($name, $this->globals)) {
            return $this->globals[$name];
        }

        return $default;
    }

    /**
     * Method to set property globals
     *
     * @param   array $globals
     *
     * @return  static  Return self to support chaining.
     */
    public function setGlobals($globals)
    {
        $this->globals = $globals;

        return $this;
    }

    /**
     * Method to get property Compiler
     *
     * @return  EdgeCompilerInterface
     */
    public function getCompiler()
    {
        return $this->compiler;
    }

    /**
     * Method to set property compiler
     *
     * @param   EdgeCompilerInterface $compiler
     *
     * @return  static  Return self to support chaining.
     */
    public function setCompiler(EdgeCompilerInterface $compiler)
    {
        $this->compiler = $compiler;

        return $this;
    }

    /**
     * Method to get property Loader
     *
     * @return  EdgeLoaderInterface
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * Method to set property loader
     *
     * @param   EdgeLoaderInterface $loader
     *
     * @return  static  Return self to support chaining.
     */
    public function setLoader(EdgeLoaderInterface $loader)
    {
        $this->loader = $loader;

        return $this;
    }

    /**
     * addExtension
     *
     * @param EdgeExtensionInterface $extension
     * @param string                 $name
     *
     * @return static
     */
    public function addExtension(EdgeExtensionInterface $extension, $name = null)
    {
        if (!$name) {
            $name = $extension->getName();
        }

        $this->extensions[$name] = $extension;

        return $this;
    }

    /**
     * removeExtension
     *
     * @param   string $name
     *
     * @return  static
     */
    public function removeExtension($name)
    {
        if (array_key_exists($name, $this->extensions)) {
            unset($this->extensions[$name]);
        }

        return $this;
    }

    /**
     * hasExtension
     *
     * @param   string $name
     *
     * @return  boolean
     */
    public function hasExtension($name)
    {
        return array_key_exists($name, $this->extensions) && $this->extensions[$name] instanceof EdgeExtensionInterface;
    }

    /**
     * getExtension
     *
     * @param   string $name
     *
     * @return  EdgeExtensionInterface
     */
    public function getExtension($name)
    {
        if ($this->hasExtension($name)) {
            return $this->extensions[$name];
        }

        return null;
    }

    /**
     * Method to get property Extensions
     *
     * @return  Extension\EdgeExtensionInterface[]
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Method to set property extensions
     *
     * @param   Extension\EdgeExtensionInterface[] $extensions
     *
     * @return  static  Return self to support chaining.
     */
    public function setExtensions($extensions)
    {
        $this->extensions = $extensions;

        return $this;
    }

    /**
     * Method to get property Cache
     *
     * @return  EdgeCacheInterface
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Method to set property cache
     *
     * @param   EdgeCacheInterface $cache
     *
     * @return  static  Return self to support chaining.
     */
    public function setCache(EdgeCacheInterface $cache)
    {
        $this->cache = $cache;

        return $this;
    }
}
