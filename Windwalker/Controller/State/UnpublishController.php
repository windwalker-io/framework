<?php

namespace Windwalker\Controller\State;

class UnpublishController extends AbstractUpdateStateController
{
	/**
	 * Property stateName.
	 *
	 * @var string
	 */
	protected $stateName = 'published';

	/**
	 * Property stateValue.
	 *
	 * @var mixed
	 */
	protected $stateValue = 0;
}
