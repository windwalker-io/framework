<?php

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
}
