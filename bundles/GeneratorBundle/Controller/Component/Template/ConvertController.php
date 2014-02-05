<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Controller\Component\Template;

use GeneratorBundle\Action;
use GeneratorBundle\Controller\Component\ComponentController;

/**
 * Class ConvertController
 *
 * @since 1.0
 */
class ConvertController extends ComponentController
{
	/**
	 * Do Execute.
	 *
	 * @return  boolean
	 */
	protected function doExecute()
	{
		// Flip src and dest because we want to convert template.
		$dest = $this->config->get('dir.dest');
		$src  = $this->config->get('dir.src');

		$this->config->set('dir.dest', $src);
		$this->config->set('dir.src',  $dest);

		$this->doAction(new Action\Component\ConvertTemplateAction);

		return true;
	}
}
