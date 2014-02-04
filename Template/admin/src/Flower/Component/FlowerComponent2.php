<?php

namespace Flower\Component;

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
	protected function init()
	{
		parent::init();

		// Init for both

		// Init for administrator
		if ($this->application->isAdmin())
		{
			$this->initAdmin();
		}
		// Init for frontend
		else
		{
			$this->initSite();
		}
	}

	/**
	 * initAdmin
	 *
	 * @return void
	 */
	private function initAdmin()
	{
	}

	/**
	 * initSite
	 *
	 * @return void
	 */
	private function initSite()
	{
	}
}
