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
	protected $layoutExt = 'xml';

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

		return HtmlRenderer::render('div', $xml, $data);
	}
}
