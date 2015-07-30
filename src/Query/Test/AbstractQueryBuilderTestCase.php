<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2015 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Query\Test;

use Windwalker\Test\Helper\TestStringHelper;
use Windwalker\Test\TestCase\AbstractBaseTestCase;

/**
 * The AbstractQueryBuilderTestCase class.
 *
 * @since  {DEPLOY_VERSION}
 */
class AbstractQueryBuilderTestCase extends AbstractBaseTestCase
{
	/**
	 * Property qn.
	 *
	 * @var  string
	 */
	protected $qn = array('`', '`');

	/**
	 * quote
	 *
	 * @param string $text
	 *
	 * @return  string
	 */
	protected function qn($text)
	{
		return TestStringHelper::quote($text, $this->qn);
	}
}
