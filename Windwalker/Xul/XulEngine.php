<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Xul;

use Windwalker\Data\Data;
use Windwalker\Xul\Html\HtmlRenderer;

/**
 * Class XulEngine
 *
 * @since 1.0
 */
class XulEngine
{
	/**
	 * @var  string  Property path.
	 */
	protected $path = '';

	/**
	 * render
	 *
	 * @param string $template
	 * @param mixed  $data
	 *
	 * @return  string
	 */
	public function render($template = 'default', $data = array())
	{
		$file = $this->path . '/' . $template . '.xml';

		if (!is_file($file))
		{
			throw new \InvalidArgumentException(sprintf('Template "%s" not exists.', $template));
		}

		$xml = simplexml_load_file($file);

		if (!($data instanceof Data))
		{
			$data = new Data($data);
		}

		$data->xulControl = new Data;

		return HtmlRenderer::render('div', $xml, $data);
	}

	/**
	 * @return  string
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * @param   string $path
	 *
	 * @return  XulRenderer  Return self to support chaining.
	 */
	public function setPath($path)
	{
		$this->path = $path;

		return $this;
	}
}