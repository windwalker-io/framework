<?php

use Windwalker\Component\Component;

/**
 * Class FlowerComponent
 *
 * @since 1.0
 */
final class FlowerComponent extends Component
{
	/**
	 * Property name.
	 *
	 * @var string
	 */
	protected $name = 'Flower';

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
	protected function prepareAll()
	{
	}

	/**
	 * initAdmin
	 *
	 * @return void
	 */
	protected function prepareAdmin()
	{
	}

	/**
	 * initSite
	 *
	 * @return void
	 */
	protected function prepareSite()
	{
	}
}
