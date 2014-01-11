<?php

use Flower\Component\FlowerComponent as FlowerComponentBase;

/**
 * Class FlowerComponent
 *
 * @since 1.0
 */
final class FlowerComponent extends FlowerComponentBase
{
	/**
	 * Property defaultController.
	 *
	 * @var string
	 */
	protected $defaultController = 'sakuras.display';

	/**
	 * init
	 *
	 * @return void
	 */
	protected function prepare()
	{
		parent::prepare();
	}
}
