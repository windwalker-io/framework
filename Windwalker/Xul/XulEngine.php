<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Xul;

use Windwalker\Data\Data;
use Windwalker\View\Engine\AbstractEngine;
use Windwalker\Xul\Html\HtmlRenderer;

/**
 * Class XulEngine
 *
 * @since 1.0
 */
class XulEngine extends AbstractEngine
{
	/**
	 * @var  string  Property layoutExt.
	 */
	protected $layoutExt = 'xul';

	/**
	 * @var  array  Property handler.
	 */
	protected $renderers = array();

	/**
	 * execute
	 *
	 * @param string $templateFile
	 * @param null   $data
	 *
	 * @throws \InvalidArgumentException
	 * @return  mixed
	 */
	protected function execute($templateFile, $data = null)
	{
		if (!is_file($templateFile))
		{
			throw new \InvalidArgumentException(sprintf('Template "%s" not exists.', $templateFile));
		}

		$xml = simplexml_load_file($templateFile);

		if (!($data instanceof Data))
		{
			$data = new Data($data);
		}

		$data->xulControl = new Data;

		return HtmlRenderer::render('div', $this, $xml, $data);
	}

	/**
	 * registerRenderer
	 *
	 * @param string $name
	 * @param string $renderer
	 * @param string $namespace
	 * @param string $prefix
	 *
	 * @throws \InvalidArgumentException
	 * @return  $this
	 */
	public function registerRenderer($name, $renderer, $namespace = 'control', $prefix = null)
	{
		$alias = $this->regularizeAlias($name, $namespace, $prefix);

		if (!is_string($renderer))
		{
			throw new \InvalidArgumentException('Renderer should be a static class name string.');
		}

		$this->renderers[$alias] = $renderer;

		return $this;
	}

	/**
	 * findRenderer
	 *
	 * @param string $name
	 * @param string $namespace
	 * @param string $prefix
	 *
	 * @throws \DomainException
	 * @return  string
	 */
	public function findRenderer($name, $namespace = 'control', $prefix = null)
	{
		// Find from renderer alias
		$renderer = $this->resolveAlias($name, $namespace, $prefix);

		$prefix = $prefix ? $prefix . '\\' : '';

		// Find from Joomla component
		if (!class_exists($renderer))
		{
			$component = '';

			if (!empty($this->data->view->prefix))
			{
				$component = $this->data->view->prefix;
			}

			$renderer = '\\' . ucfirst($component) . '\\Xul\\' . $namespace . '\\' . $prefix . ucfirst($name) . 'Renderer';
		}

		// Find from windwalker
		if (!class_exists($renderer))
		{
			$renderer = '\\Windwalker\\Xul\\' . $namespace . '\\' . $prefix . ucfirst($name) . 'Renderer';
		}

		if (!class_exists($renderer))
		{
			$renderer = '\\Windwalker\\Xul\\' . $namespace . '\\' . ucfirst($name) . 'Renderer';
		}

		if (!class_exists($renderer))
		{
			throw new \DomainException(sprintf('Xul tag: "%s" do not support.', $name));
		}

		return $renderer;
	}

	/**
	 * resolveAlias
	 *
	 * @param string $name
	 * @param string $namespace
	 * @param string $prefix
	 *
	 * @return  string
	 */
	protected function resolveAlias($name, $namespace = 'control', $prefix = null)
	{
		$alias = $this->regularizeAlias($name, $namespace, $prefix);

		if (!empty($this->renderers[$alias]))
		{
			return $this->renderers[$alias];
		}

		return null;
	}

	/**
	 * regularizeAlias
	 *
	 * @param string $name
	 * @param string $namespace
	 * @param string $prefix
	 *
	 * @return  string
	 */
	protected function regularizeAlias($name, $namespace = 'control', $prefix = null)
	{
		$alias = array();

		$alias[] = strtolower($namespace);

		if ($prefix)
		{
			$alias[] = strtolower(trim($prefix, '\\'));
		}

		$alias[] = strtolower($name);

		return implode(':', $alias);
	}
}
