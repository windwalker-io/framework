<?php
/**
 * Part of formosa project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Renderer;

use Windwalker\Data\Data;

/**
 * Class PhpRenderer
 *
 * @since 1.0
 */
class PhpRenderer extends AbstractRenderer
{
	/**
	 * Property block.
	 *
	 * @var  array
	 */
	protected $block = array();

	/**
	 * Property blockQueue.
	 *
	 * @var  \SplQueue
	 */
	protected $blockQueue = null;

	/**
	 * Property currentBlock.
	 *
	 * @var  string
	 */
	protected $currentBlock = null;

	/**
	 * Property extends.
	 *
	 * @var  string
	 */
	protected $extend = null;

	/**
	 * Property parent.
	 *
	 * @var  PhpRenderer
	 */
	protected $parent = null;

	/**
	 * Property data.
	 *
	 * @var Data
	 */
	protected $data;

	/**
	 * render
	 *
	 * @param string        $file
	 * @param array|object  $data
	 *
	 * @throws  \UnexpectedValueException
	 * @return  string
	 */
	public function render($file, $data = array())
	{
		$this->data = $data = ($data instanceof Data) ? $data : new Data($data);

		$filePath = $this->findFile($file);

		if (!$filePath)
		{
			throw new \UnexpectedValueException(sprintf('File: %s not found', $filePath));
		}

		// Start an output buffer.
		ob_start();

		// Load the layout.
		include $filePath;

		// Get the layout contents.
		$output = ob_get_clean();

		// Handler extend
		if (!$this->extend)
		{
			return $output;
		}

		/** @var $parent phpRenderer */
		$parent = new static($this->paths);

		foreach ($this->block as $name => $block)
		{
			$parent->setBlock($name, $block);
		}

		// if ($file == 'html') show($this);die;

		return $parent->render($this->extend, $data);
	}

	/**
	 * finFile
	 *
	 * @param string $file
	 * @param string $ext
	 *
	 * @return  string
	 */
	public function findFile($file, $ext = 'php')
	{
		return parent::findFile($file, $ext);
	}

	/**
	 * getParent
	 *
	 * @return  mixed|null
	 */
	protected function parent()
	{
		if (!$this->extend)
		{
			return null;
		}

		if (!$this->parent)
		{
			$this->parent = new static($this->paths);

			$this->parent->render($this->extend, $this->data);
		}

		return $this->parent->getBlock($this->currentBlock);
	}

	/**
	 * extend
	 *
	 * @param string $name
	 *
	 * @return  void
	 *
	 * @throws \LogicException
	 */
	public function extend($name)
	{
		if ($this->extend)
		{
			throw new \LogicException('Please just extend one file.');
		}

		$this->extend = $name;
	}

	/**
	 * getBlock
	 *
	 * @param string $name
	 *
	 * @return  mixed
	 */
	public function getBlock($name)
	{
		return !empty($this->block[$name]) ? $this->block[$name] : null;
	}

	/**
	 * setBlock
	 *
	 * @param string $name
	 * @param string $content
	 *
	 * @return  PhpRenderer  Return self to support chaining.
	 */
	public function setBlock($name, $content = '')
	{
		$this->block[$name] = $content;

		return $this;
	}

	/**
	 * setBlock
	 *
	 * @param  string $name
	 */
	public function block($name)
	{
		$this->currentBlock = $name;

		$this->getBlockQueue()->push($name);

		// Start an output buffer.
		ob_start();
	}

	/**
	 * endblock
	 *
	 * @return  void
	 */
	public function endblock()
	{
		$name = $this->getBlockQueue()->pop();

		if (!empty($this->block[$name]))
		{
			ob_get_clean();

			echo $this->block[$name];

			return;
		}

		// Get the layout contents.
		echo $this->block[$name] = ob_get_clean();
	}

	/**
	 * getBlockQueue
	 *
	 * @return  \SplQueue
	 */
	public function getBlockQueue()
	{
		if (!$this->blockQueue)
		{
			$this->blockQueue = new \SplStack;
		}

		return $this->blockQueue;
	}
}
