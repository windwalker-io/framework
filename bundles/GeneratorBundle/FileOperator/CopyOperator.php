<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\FileOperator;

use CodeGenerator\FileOperator\CopyOperator as CodeGeneratorCopyOperator;

/**
 * Class CopyOperator
 *
 * @since 1.0
 */
class CopyOperator extends CodeGeneratorCopyOperator
{
	protected $replaceHandler = array('Windwalker\\String\\String', 'parseVariable');
}
