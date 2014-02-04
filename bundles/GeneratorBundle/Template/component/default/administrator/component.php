<?php

use Windwalker\Component\Component;

/**
 * Class {{extension.name.cap}}Component
 *
 * @since 1.0
 */
final class {{extension.name.cap}}Component extends Component
{
	/**
	 * Property name.
	 *
	 * @var string
	 */
	protected $name = '{{extension.name.cap}}';

	/**
	 * Property defaultController.
	 *
	 * @var string
	 */
	protected $defaultController = '{{controller.list.name.lower}}.display';

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
