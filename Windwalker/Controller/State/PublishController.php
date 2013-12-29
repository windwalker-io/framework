<?php

namespace Windwalker\Controller\State;

class PublishController extends AbstractUpdateStateController
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
	protected $stateValue = 1;
}
