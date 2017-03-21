<?php
/**
 * Part of windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Test\TestCase;

use Windwalker\Test\Traits\BaseAssertionTrait;

/**
 * The AbstractBaseTestCase class.
 * 
 * @since  2.0
 *
 * @deprecated  Directly use BaseAssertionTrait.
 */
abstract class AbstractBaseTestCase extends \PHPUnit\Framework\TestCase
{
	use BaseAssertionTrait;
}
