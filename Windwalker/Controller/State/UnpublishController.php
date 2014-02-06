<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Controller\State;

/**
 * Class UnpublishController
 *
 * @since 1.0
 */
class UnpublishController extends AbstractUpdateStateController
{
	/**
	 * Property stateData.
	 *
	 * @var string
	 */
	protected $stateData = array(
		'published' => '0'
	);

	/**
	 * Property actionText.
	 *
	 * @var string
	 */
	protected $actionText = 'UNPUBLISHED';

	/**
	 * Property allowReturn.
	 *
	 * @var  boolean
	 */
	protected $allowReturn = true;
}
