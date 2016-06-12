<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Renderer;

use Windwalker\Edge\Compiler\EdgeCompiler;
use Windwalker\Edge\Edge;

/**
 * The EdgeRenderer class.
 *
 * @since  {DEPLOY_VERSION}
 */
class EdgeRenderer extends AbstractEngineRenderer
{
	/**
	 * All of the finished, captured sections.
	 *
	 * @var array
	 */
	protected $sections = array();

	/**
	 * The stack of in-progress sections.
	 *
	 * @var array
	 */
	protected $sectionStack = [];

	/**
	 * All of the finished, captured push sections.
	 *
	 * @var array
	 */
	protected $pushes = [];

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
	 * Property engine.
	 *
	 * @var  Edge
	 */
	protected $engine;

	/**
	 * render
	 *
	 * @param string $file
	 * @param array  $data
	 *
	 * @return  string
	 */
	public function render($file, $__data = array())
	{
		$this->prepareData($__data);

		$__filePath = $this->findFile($file);

		if (!$__filePath)
		{
			$__paths = $this->dumpPaths();

			$__paths = "\n " . implode(" |\n ", $__paths);

			throw new \UnexpectedValueException(sprintf('File: %s not found. Paths in queue: %s', $file, $__paths));
		}

		return $this->getEngine()->render($__filePath, $__data);
	}

	/**
	 * prepareData
	 *
	 * @param   array &$data
	 *
	 * @return  void
	 */
	protected function prepareData(&$data)
	{
	}

	/**
	 * Method to get property Engine
	 *
	 * @param   boolean $new
	 *
	 * @return  Edge
	 */
	public function getEngine($new = false)
	{
		if (!$this->engine || $new)
		{
			$this->engine = new Edge(new EdgeCompiler);
		}

		return $this->engine;
	}

	/**
	 * Method to set property engine
	 *
	 * @param   Edge $engine
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setEngine($engine)
	{
		$this->engine;
	}

	/**
	 * finFile
	 *
	 * @param string $file
	 * @param string $ext
	 *
	 * @return  string
	 */
	public function findFile($file, $ext = 'blade.php')
	{
		return parent::findFile($file, $ext);
	}
}
