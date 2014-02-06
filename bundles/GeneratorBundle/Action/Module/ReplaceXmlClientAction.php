<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Action\Module;

use GeneratorBundle\Action\AbstractAction;
use Joomla\Filesystem\File;

/**
 * Class ReplaceXmlClientAction
 *
 * @since 1.0
 */
class ReplaceXmlClientAction extends AbstractAction
{
	/**
	 * doExecute
	 *
	 * @return  mixed
	 */
	protected function doExecute()
	{
		$xml = $this->config['dest'] . '/{{extension.name.lower}}.xml';

		$content = file_get_contents($xml);

		$content = str_replace('client="site"', '{{module.client}}', $content);

		File::write($content, $xml);
	}
}
