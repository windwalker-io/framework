<?php

namespace Windwalker\Controller\State;

/**
 * Class TrashController
 *
 * @since 1.0
 */
class TrashController extends AbstractUpdateStateController
{
	/**
	 * Property stateData.
	 *
	 * @var string
	 */
	protected $stateData = array(
		'published' => '-2'
	);

	/**
	 * Property actionText.
	 *
	 * @var string
	 */
	protected $actionText = 'TRASHED';
}
