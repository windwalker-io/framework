<?php

namespace Windwalker\Controller\State;

class PublishController extends AbstractUpdateStateController
{
	/**
	 * Property stateData.
	 *
	 * @var string
	 */
	protected $stateData = array(
		'published' => 1
	);

	/**
	 * Property actionText.
	 *
	 * @var string
	 */
	protected $actionText = 'PUBLISHED';
}
